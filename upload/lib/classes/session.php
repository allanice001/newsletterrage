<?php

class Session
{

	var $session_current  = '';
	var $session_previous = '';
	var $session_expire   = 2592000; // 30 DAYS

	function Session()
	{
		session_set_save_handler(array(&$this, 'open'), array(&$this, 'close'), array(&$this, 'read'), array(&$this, 'write'), array(&$this, 'destroy'), array(&$this, 'gc'));
		register_shutdown_function('session_write_close');
		session_start();
		setcookie('ssn_id', $this->session_current, (time() + $this->session_expire), '/', false, false);
	}

	function get($name, $default = '')
	{
		return isset($_SESSION[$name]) ? $_SESSION[$name] : (isset($_SESSION[$this->session_previous][$name]) ? $_SESSION[$this->session_previous][$name] : $default);
	}

	function set($name, $value = '', $persistent = true)
	{
		if ($persistent) {
			if (isset($_SESSION[$this->session_previous][$name])) {unset($_SESSION[$this->session_previous][$name]);}
			$values = &$_SESSION;
		} else {
			$session_previous = isset($_SESSION['prev_ssn_id']) ? $_SESSION['prev_ssn_id'] : '';
			if ($session_previous != $this->session_previous) {
				if (isset($_SESSION[$session_previous])) {unset($_SESSION[$session_previous]);}
				$_SESSION['prev_ssn_id']           = $this->session_previous;
				$_SESSION[$this->session_previous] = array();
			}
			if (isset($_SESSION[$name])) {unset($_SESSION[$name]);}
			$values = &$_SESSION[$this->session_previous];
		}
		if (is_array($value) ? !empty($value) : !preg_match('/^$|false/i', $value)) {
			$values[$name] = $value;
		} else if (isset($values[$name])) {
			unset($values[$name]);
		}
	}

	function open($save_path, $session_name)
	{
		return true;
	}

	function close()
	{
		return true;
	}

	function read($id)
	{
		global $DB;
		$this->session_current  = isset($_COOKIE['ssn_id']) ? $_COOKIE['ssn_id'] : $id;
		$this->session_previous = $id;
		return $DB->lookup('SELECT SQL_NO_CACHE s.data FROM sessions s WHERE s.id = "' . $DB->escape($this->session_current) . '" LIMIT 1', '');
	}

	function write($id, $data)
	{
		global $DB;
		$DB->query('INSERT INTO sessions (id, timestamp, data, active) VALUES ("' . $DB->escape($this->session_current) . '", UNIX_TIMESTAMP(), "' . $DB->escape($data) . '", 1) ON DUPLICATE KEY UPDATE timestamp = UNIX_TIMESTAMP(), data = "' . $DB->escape($data) . '", active = 1');
		return true;
	}

	function destroy($id)
	{
		global $DB;
		$DB->query('DELETE FROM sessions WHERE id = "' . $DB->escape($this->session_current) . '"');
		$_SESSION = array();
		return true;
	}

	function gc($maxlifetime)
	{
		global $DB;
		$DB->query('DELETE FROM sessions WHERE (timestamp < (UNIX_TIMESTAMP() - ' . $DB->escape($this->session_expire) . ')) OR (timestamp < (UNIX_TIMESTAMP() - ' . $DB->escape($maxlifetime) . ') AND data = "")');
		$DB->query('UPDATE sessions s SET s.active = 0 WHERE s.active = 1 AND s.timestamp < (UNIX_TIMESTAMP() - ' . $DB->escape($maxlifetime) . ')');
		return true;
	}

}

	$GLOBALS['Session'] = new Session();

