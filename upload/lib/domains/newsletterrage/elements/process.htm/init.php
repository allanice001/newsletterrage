<?php
    global $DB;
    
    function isValidEmail($email){  
     return filter_var(filter_var($email, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);  
    }
    
    $post_email = get('address', '', false, true);
    $get_email  = get('address', 'allan@linaccess.com', true, false);
    $status = '';
    if ($post_email) {
        $blacklisted = false;
        $rs_blacklist = $DB->query('SELECT * FROM mailinglist_blacklist');
        while ($blacklist = $rs_blacklist->next()) {
            $pos = strpos($email, $blacklist_row[rule]);
            if ($pos !== false) {
                $blacklisted = true;
            }
        }
    }

    if ($post_email) {    
        if (isValidEmail($post_email) AND !$blacklisted) {
            // Email is potentially valid
    		// See if in db, if so, send unsub email
    		// if not in db, insert record and send sub email
            $key = md5(time());
            $auth_link = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . "?address=$email&key=$key";
    		$req_time = time();
            
            $addr_exists = $DB->lookup('SELECT address FROM mailinglist_subscribers WHERE address = "'. $post_email .'"');
            if ($addr_exists) {// Record exists in db
                $data = $DB->getArray('SELECT * FROM mailinglist_subscribers WHERE address = "'. $post_email .'"');
                
                if ($data[$email]['confirmed'] == 1) {// Send unsubscribe email
                    $subject   = 'Please confirm your unsubscribe request from '. $System['config']['list_name'];
                    $action    = 'unsubscribe from';
    				$auth_link = url($System['page']['name'], array('address' => $post_email, 'key' => $data[$post_email]['userkey'], 'c' => '0'), true);
                } else {// Email was not confirmed, generate key, and send confirmation request
    				$subject    = 'Please confirm your subscribe request to '. $System['config']['list_name'];
    				$action     = 'subscribe to';
    				$auth_link  = url($System['page']['name'], array('address' => $post_email, 'key' => $data[$post_email]['userkey'], 'c' => '1'), true);
                    $DB->query('UPDATE mailinglist_subscribers SET userkey="'. $key .'", last_sub_req_date= "'. $req_time .'" WHERE address = "'. $post_email .'"');
                }
            } else {// Record not in db
                $DB->query('INSERT INTO mailinglist_subscribers (address, userkey, last_sub_req_date)VALUES ("'. $post_email .'", "'. $key .'", "'. $req_time .'")');
    			$subject = 'Please confirm your subscribe request to '. $System['config']['list_name'];
    			$action = 'subscribe to';
    			$auth_link  = url($System['page']['name'], array('address' => $post_email, 'key' => $data[$post_email]['userkey'], 'c' => '1'), true);    
            }
            
            $message = 'To confirm the request to '. $action .' the list: '. $System['config']['list_name']. 
            ', we ask that you follow this link:' . "\n\n". $auth_link . "\n\n" . 'If you are unable to click '.
            'the link, please copy and paste it into your web browser.'."\n\n".
    		$System['config']['owner_email']."\n";
           
            
            /* Send Mail HERE!!
            $confirm = new SMLmailer;
    		$confirm->subject = $subject;
    		$confirm->mail_to = $email;
    		$confirm->message = $message;
    		$confirm->unsub_message = "";
    		$confirm->use_SMTP = ($config[use_SMTP] == 1) ? true:false;
    		$confirm->send();
            */
            
            $status = 'mail_sent';
            
            
        } else {
            $status = 'invalid_email';
            //redirect(url($System['page']['name'], array('status' => $status)));
        } 
            
    } elseif($get_email) {
        $key = get('key');
        $confirm = get('c');
        
        $query = $DB->lookup('SELECT address FROM mailinglist_subscribers WHERE address = "'. $get_email .'" AND userkey = "'. $key .'"');
        if ($query) {
            // The address and key match a record in the db. Proceed to verify request.
  		
            $data = $DB->getArray('SELECT * FROM mailinglist_subscribers WHERE address = "'. $get_email .'" AND userkey = "'. $key .'"');
            // if db has confirmed = 0 and user has confirmed = 0, that's an attempt to unsubscribe an unconfirmed address - denied
	       	// if db has confirmed = 0 and user has confirmed = 1, that's an attempt to confirm an unconfirmed address - allowed
            // if db has confirmed = 1 and user has confirmed = 0, that's an attempt to unsubscribe a confirmed address - allowed
            // if db has confirmed = 1 and user has confirmed = 1, that's an attempt to subscribe a confirmed address - denied
        
            print_pre($data);
            
            if($data[$get_email]['confirmed'] == 0 And $confirm == 1) {
                // user is in db, email and key are correct, they have not confirmed so this is a confirmation,
                // update confirm and present message
                //$DB->query('UPDATE mailinglist_subscribers SET confirmed = "1" WHERE address = "'. $get_email .'" AND userkey = "'. $key .'"');
                $status = 'confirmed_subscribe';
                
                
                if($System['config']['notify_on_confirm']) {
                    $count= $DB->lookup('SELECT COUNT(*) FROM mailinglist_subscribers WHERE confirmed = "1"');
                    $admin_note = $get_email .' has joined '. $System['config']['list_name'].'. There are now '.$count .' members subscribing to this list.';
                    
                    /* TODO -> send notification mail
                    $notify_confirm = new SMLmailer;
				    $notify_confirm->subject = "$config[list_name] Subscription Confirmation";
				    $notify_confirm->mail_to = $config['owner_email'];
				    $notify_confirm->message = $admin_note;
				    $notify_confirm->unsub_message = "";
				    $notify_confirm->use_SMTP = ($config[use_SMTP] == 1) ? true:false;
				    $notify_confirm->send();
                    */
                }
                
                if($System['config']['notify_on_confirm']) {
    				$user_note = 'Thank you for joining the '. $System['config']['list_name'] .' list.';
                    
                    /* TODO -> send notification mail
    				$notify_user_confirm = new SMLmailer;
    				$notify_user_confirm->subject = "$config[list_name] Subscription Confirmation";
    				$notify_user_confirm->mail_to = $email;
    				$notify_user_confirm->message = $user_note;
    				$notify_user_confirm->unsub_message = "";
    				$notify_user_confirm->use_SMTP = ($config[use_SMTP] == 1) ? true:false;
    				$notify_user_confirm->send();
                    */
                }
            } elseif($data[$get_email]['confirmed'] == 1 And $confirm == 0) {
 			    // user is in db, email and key are correct, they were already confirmed so this is an unsubscribe req
                // remove user from db and present message
                
                $DB->query('DELETE FROM mailinglist_subscribers WHERE address = "'. $get_email .'" AND userkey = "'. $key .'"');
                $status = 'confirm_unsubscribe';
                
                if($System['config']['notify_on_unsub']) {
				    // Count subscribers for admin email
				    $count = $DB->lookup('SELECT  COUNT(*) FROM mailinglist_subscribers WHERE confirmed = "1"');
		
				    $admin_note = $email .' has unsubscribed from '. $System['config']['list_name'].'. There are now '. $count .' members subscribing to this list.';

                    /* TODO - Send Notification Mail
    				$notify_unsub = new SMLmailer;
    				$notify_unsub->subject = $System['config']['list_name'] .' Unsubscription';
    				$notify_unsub->mail_to = $config['owner_email'];
    				$notify_unsub->message = $admin_note;
    				$notify_unsub->unsub_message = "";
    				$notify_unsub->use_SMTP = ($config[use_SMTP] == 1) ? true:false;
    				$notify_unsub->send();
                    */
                }
                
                if($config['notify_user_on_unsub']) {
    				$user_note = 'You have been successfully unsubsribed from the '. $System['config']['list_name'] .'list.';
    
                    /* TODO - Send Notification Mail
    				$notify_user_unsub = new SMLmailer;
    				$notify_user_unsub->subject = "$config[list_name] Unsubscription Confirmation";
    				$notify_user_unsub->mail_to = $email;
    				$notify_user_unsub->message = $user_note;
    				$notify_user_unsub->unsub_message = "";
    				$notify_user_unsub->use_SMTP = ($config[use_SMTP] == 1) ? true:false;
    				$notify_user_unsub->send();
                    */
                }
            } else {
                // one of the two denied conditions above occurred.
                $status = 'error';
            }
        } 
    }
    
    if ($status) {
        $email = ($post_email ? $post_email : $get_email);
        redirect(url($System['page']['name'], array('status' => $status, 'address' => $email)));
    }