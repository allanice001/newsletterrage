<?php

class Structure
{

	function pack($data)
	{
		$data = serialize($data);
		for ($i = 0; $i < strlen($data); $i++) {
			$data[$i] = chr(ord($data[$i]) ^ ($i % 5));
		}
		return '<!--<DATA-START>' . rawurlencode($data) . '<DATA-END>-->';
	}

	function unpack($data)
	{
		$data_start = strpos($data, '<DATA-START>') + 12;
		$data_end   = strpos($data, '<DATA-END>');
		if ($data_start < $data_end) {
			$data = rawurldecode(substr($data, $data_start, ($data_end - $data_start)));
			for ($i = 0; $i < strlen($data); $i++) {
				$data[$i] = chr(ord($data[$i]) ^ ($i % 5));
			}
			return unserialize($data);
		}
		return '';
	}

	function convertToCode($data, $indent = 0)
	{
		if (is_bool($data)) {
			return ($data ? 'true' : 'false');
		} elseif (is_numeric($data)) {
			return $data;
		} elseif (is_string($data)) {
			return '\'' . htmlentities(str_replace('\'', '\\\'', $data)) . '\'';
		} elseif (is_array($data)) {
			$result = '';
			$element_count = 0;
			foreach ($data as $key => $value) {
				$result .= ($element_count ? ",\n" : '') . str_repeat("\t", $indent + 1) . ($key !== $element_count++ ? $this->convertToCode($key) . ' => ' : '') . $this->convertToCode($value, $indent + 1);
			}
			return 'array(' . ($element_count ? "\n" : '') . $result . ($element_count ? "\n" . str_repeat("\t", $indent) : '') . ')';
		}
	}

}

	$GLOBALS['Structure'] = new Structure();

