<?php
	echo
	'<form name="login" method="post" action="'.urlPath().'">'.
	'<input type="hidden" name="action" value="'.rand().'" />'.
	'<span style="display:none;" id="errorMgs" class="loginWarning">Invalid Username / Password</span>'.
	'<table border="0" cellspacing="0" cellpadding="0" style="margin:auto;">'.
	'<tr><th>User Name</th><td><input type="text" name="username" value="" /></td></tr>'.
	'<tr><th>Password</th><td><input type="password" name="password" value="" /></td></tr>'.
	'<tr><th>Keep me signed in</th><td><input type="checkbox" name="remember" /></td></tr>'.
	'<tr><td align="left"><input type="submit" name="Submit" value="Login" /></td>'.
	//'<td align="right"><input onclick="window.open(\''.url($System['pages']['recover']).'\',\'_self\')" type="button" name="Recover" value="Recover Password" /></td>'.
	'</tr>'.
	'</table>'.
	'</form>'.
	SCRIPT_START.
	'document.login.username.focus();'.
	SCRIPT_END;