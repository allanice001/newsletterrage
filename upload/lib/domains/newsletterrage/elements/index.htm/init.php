<?php
    global $DB;
    
    function isValidEmail($email){  
     return filter_var(filter_var($email, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);  
    }
    
    $email = get('address', '');
    
    if ($address) {
        $blacklisted = false;
        $rs_blacklist = $DB->query('SELECT * FROM mailinglist_blacklist');
        while ($blacklist = $rs_blacklist->next()) {
            $pos = strpos($email, $blacklist_row[rule]);
            if ($pos !== false) {
                $blacklisted = true;
            }
        }
    }
    
    if (isValidEmail($email) AND !$blacklisted) {
        // Email is potentially valid
		// See if in db, if so, send unsub email
		// if not in db, insert record and send sub email
        $key = md5(time());
        $auth_link = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . "?address=$email&key=$key";
		$req_time = time();
        
        $addr_exists = $DB->lookup('SELECT FROM mailinglist_subscribers WHERE address = '.$email);
    }