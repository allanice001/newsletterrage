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
    htmlentities('<form method="POST" action="'.urlPath().'">') . '<br />'.
    htmlentities('<input type="text" name="address" size="20">'). '<br />'.
    htmlentities('<input type="submit" value="Submit" name="submit"><br />'). '<br />'.
    htmlentities('<font face="Tahoma" size="1">All subscribe/unsubscribe requests must be confirmed via email.</font>'). '<br />'.
    htmlentities('</form>').'<br />'.
    htmlentities('</p>');
?>
</pre>
</p>