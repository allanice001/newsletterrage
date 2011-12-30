<?php

class DB_Connection
{

	var $database_id = 0;
	var $query_count = 0;

	function DB_Connection($server, $username, $password, $database, $persistent = true)
	{
		$this->database_id = ($persistent ? @mysql_pconnect($server, $username, $password) : @mysql_connect($server, $username, $password))
			or die('Could not connect to database!');
		@mysql_select_db($database, $this->database_id)
			or die('Could not select database!');
	}

	function query($command, $page_size = 0, $page_num = 1)
	{
		$record_count = 0;

		if ($page_size) {
			$page_size = max($page_size, 1);
			$page_num  = max($page_num, 1);
			$position  = (strpos(strtoupper($command), 'SELECT') + 6);
			$query_id  = mysql_query(substr($command, 0, $position) . ' SQL_CALC_FOUND_ROWS' . substr($command, $position) . ' LIMIT ' . ($page_size * ($page_num - 1)) . ',' . $page_size, $this->database_id);
			$record_count = $this->lookup('SELECT FOUND_ROWS()');
		} else {
			$query_id = mysql_query($command, $this->database_id);
		}

		$this->query_count++;
		if (is_resource($query_id)) {
			return new DB_Recordset($query_id, $page_size, $page_num, $record_count);
		} elseif ($query_id) {
			$insert_id = mysql_insert_id($this->database_id);
			return $insert_id ? $insert_id : mysql_affected_rows($this->database_id);
		}
		return false;
	}

	function lookup($command, $default = '')
	{
		$query  = $this->query($command);
		$result = (mysql_num_fields($query->query_id) > 1) ?
			(($record = $query->next()) ? $record : $default) :
			(($record = $query->next(MYSQL_NUM)) ? $record[0] : $default);
		$query->free();
		return $result;
	}

	function getArray($command, $indexes = null, $values = null)
	{
		$result = array();
		$query  = $this->query($command);
		$count  = mysql_num_fields($query->query_id);

		if (!$indexes) {$indexes = array(mysql_field_name($query->query_id, 0));}
			elseif (!is_array($indexes)) {$indexes = array($indexes);}
		if (!$values)  {$values = ($count < 3) ? array(mysql_field_name($query->query_id, min(1, $count - 1))) : array();}
			elseif (!is_array($values))  {$values = array($values);}

		while ($record = $query->next()) {
			$entry = &$result;
			foreach ($indexes as $index) {
				$record_index = ($index === '#') ? count($entry) : $record[$index];
				if (!isset($entry[$record_index])) {$entry[$record_index] = array();}
				$entry = &$entry[$record_index];
			}
			if (!$values) {
				$entry = $record;
			} elseif (count($values) === 1) {
				$entry = $record[$values[0]];
			} else {
				foreach ($values as $value) {
					$entry[$value] = $record[$value];
				}
			}
		}
		$query->free();

		return $result;
	}

	function escape($string)
	{
		return mysql_real_escape_string($string, $this->database_id);
	}

	function unescape($string)
	{
		return stripslashes($string);
	}

	function whereIn($search_words = array(), $word_combinations = 1, $limit = 0)
	{
		$words = array();
		foreach (array_unique($search_words) as $search_word) {$words[] = $search_word;}
		$words_count = count($words);
		$results = $words;

		for ($word_group_count = 2; $word_group_count <= min($word_combinations, $words_count); $word_group_count++) {

			$index = array_fill(0, $word_group_count, 0);
			$word_group_total = pow($words_count, $word_group_count);

			for ($counter = 0; $counter < $word_group_total; $counter++) {
				if ($word_group_count == count(array_unique($index))) {
					$word = array();
					for ($id = 0; $id < $word_group_count; $id++) {
						$word[] = $words[$index[$id]];
					}
					$results[] = implode(' ', $word);
					if ($limit && count($results) >= $limit) {break 2;}
				}
				for ($id = 0; $id < $word_group_count; $id++) {
					$index[$id]++;
					if ($index[$id] >= $words_count) {
						$index[$id] = 0;
					} else {
						break;
					}
				}
			}
		}

		return '"' . implode('","', $results) . '"';
	}

	function whereLike($search, $fields, $any_keyword = false)
	{
		if ($search && $fields) {
			$search = is_array($search) ? $search : explode(' ', trim(preg_replace('/\s\s+/', ' ', $this->escape($search))));
			$fields = is_array($fields) ? $fields : explode(' ', trim(preg_replace('/\s\s+/', ' ', $this->escape($fields))));
			$search_query = array();
			foreach ($search as $keyword) {
				$search_query[] = implode(' LIKE "%' . $keyword . '%" OR ', $fields) . ' LIKE "%' . $keyword . '%"';
			}
			return '((' . implode(') ' . ($any_keyword ? 'OR' : 'AND') . ' (', $search_query) . '))';
		} else {
			return 1;
		}
	}

}

class DB_Recordset
{

	var $query_id     = 0;
	var $record_index = 0;
	var $record_count = 0;
	var $record_start = 0;
	var $record_end   = 0;
	var $record       = array();

	function DB_Recordset($query_id = 0, $page_size = 0, $page_num = 1, $record_count = 0)
	{
		if ($query_id) {
			$this->query_id = $query_id;
			if ($page_size) {
				$this->record_count = $record_count;
				$this->record_start = min(1 + ($page_size * ($page_num - 1)), $record_count);
				$this->record_end   = min($page_size * $page_num, $record_count);
				$this->page_size    = $page_size;
				$this->page_num     = $page_num;
				$this->page_count   = (($record_count - ($record_count % $page_size)) / $page_size) + (($record_count % $page_size) > 0 ? 1 : 0);
			} else {
				$this->record_count = mysql_num_rows($this->query_id);
				$this->record_start = min(1, $this->record_count);
				$this->record_end   = $this->record_count;
			}
		}
	}

	function free()
	{
		mysql_free_result($this->query_id);
		$this->query_id = 0;
	}

	function next($result_type = MYSQL_ASSOC)
	{
		$this->record_index++;
		$this->record = mysql_fetch_array($this->query_id, $result_type);
		return $this->record;
	}

	function fields()
	{
		$fields = array();
		for ($i = 0, $j = mysql_num_fields($this->query_id); $i < $j; $i++) {
			$fields[] = mysql_fetch_field($this->query_id, $i);
		}
		return $fields;
	}

}

