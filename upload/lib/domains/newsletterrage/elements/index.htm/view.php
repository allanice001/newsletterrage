<?php
    $status = get('status', '');
    $email = get('address');
    if ($status) {
        if ($status == 'mail_sent') {
            $status_message = 'A confirmation email has been sent to '. $email;
        }
        if ($status == 'invalid_email') {
            $status_message = 'We\'re sorry, this email address seems to be invalid or it\'s not allowed to sign up for this list.<br />'. 
					'Please check the address and try again or email '. $System['config']['owner_email'] .' for assistance.';
        }
        if ($status == 'confirmed_subscribe') {
            $status_message = 'Thank you, your subscription to '. $System['config']['list_name'] .' has been confirmed. To unsubscribe at any time just enter your email address below.\n';
        }
        if ($status == 'confirm_unsubscribe') {
            $status_message = 'Thank you, you have been unsubscribed from '. $System['config']['list_name'];
        }
        if ($status == 'error') {
            $status_message = 'Error processing request. Please contact '. $System['config']['owner_email'] .' for assistance.';
        }
        
    }
?>
<form method="POST" action="<?echo urlPath(); ?>">
	<input type="text" name="address" size="20">
	<input type="submit" value="Submit" name="submit"><br>
	<font face="Tahoma" size="1">All subscribe/unsubscribe requests must be confirmed via email.</font>
</form>
<p>
To embedd this form on your website, copy the code below.
<pre>
<?php
echo 
    htmlentities('<p>') . '<br />' .
    htmlentities('<form method="POST" action="'. urlPath().'">') . '<br />'.
    htmlentities('<input type="text" name="address" size="20">'). '<br />'.
    htmlentities('<input type="submit" value="Submit" name="submit"><br />'). '<br />'.
    htmlentities('<font face="Tahoma" size="1">All subscribe/unsubscribe requests must be confirmed via email.</font>'). '<br />'.
    htmlentities('</form>').'<br />'.
    htmlentities('</p>');
?>
</pre>
</p>
<p>To see this page without the code snippet, use <?php echo '<a href="'. url('process', array(), true). '">'. url('process') .'</a>'; ?>.</p>
<p>To view the documentation, click here: <?php echo '<a href="'. url('doc_index', array(), true). '">'. url('doc_index', array(), true) .'</a>'; ?>.</p>
<p>The administration panel is located <a href="<?echo url('admin_index');?>"><?echo url('admin_index');?></a></p>

