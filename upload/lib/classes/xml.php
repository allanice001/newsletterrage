<?php

class XML_Document
{
	var $nodes = array();
	var $index = 0;

	function XML_Document($data = '')
	{
		$this->clear();
		if ($data) {
			$this->loadXML($data);
		}
	}

	function clear()
	{
		$this->nodes = array(
			0 => array(
				'id'         => 0,
				'parentNode' => 0,
				'nodeName'   => '#document',
				'attributes' => array(),
				'childNodes' => array(),
				'text'       => ''
			)
		);
		$this->setAttribute(0, 'version', '1.0');
		$this->index = 0;
	}

	function createNode($parent_id, $node_name, $text = '', $attributes = array())
	{
		$node_id = ++$this->index;
		$this->nodes[$node_id] = array(
			'id'         => $node_id,
			'parentNode' => $parent_id,
			'nodeName'   => $node_name,
			'attributes' => is_array($attributes) ? $attributes : array(),
			'childNodes' => array(),
			'text'       => $text
		);
		$this->nodes[$parent_id]['childNodes'][$node_id] = $node_id;
		return $node_id;
	}

	function destroyNode($node_id)
	{
		foreach ($this->nodes[$node_id]['childNodes'] as $child_node) {
			$this->destroyNode($child_node);
		}
		unset($this->nodes[$this->nodes[$node_id]['parentNode']]['childNodes'][$node_id]);
		unset($this->nodes[$node_id]);
	}

	function getAttribute($node_id, $name, $default = '')
	{
		return isset($this->nodes[$node_id]['attributes'][$name]) ? $this->nodes[$node_id]['attributes'][$name] : $default;
	}

	function getText($node_id)
	{
		return $this->nodes[$node_id]['text'];
	}

	function getXML($node_id = 0, $reserved = 0)
	{
		$result = '';
		if (!$reserved) {
			$result  = '<?xml';
			foreach ($this->nodes[0]['attributes'] as $key => $value) {$result .= ' ' . $key . '="' . $this->_encodeEntities($value) . '"';}
			$result .= '?>';
		}
		foreach ($this->nodes[$node_id]['childNodes'] as $child_node) {
			$attributes = '';
			foreach ($this->nodes[$child_node]['attributes'] as $key => $value) {$attributes .= ' ' . $key . '="' . $this->_encodeEntities($value) . '"';}
			$node_content = $this->_encodeEntities($this->nodes[$child_node]['text']) . $this->getXML($child_node, 1);
			$result .= '<' . $this->nodes[$child_node]['nodeName'] . $attributes . ($node_content ? '>' . $node_content . '</' . $this->nodes[$child_node]['nodeName'] . '>' : ' />');
		}
		return $result;
	}

	function loadXML($xml_data = '')
	{
		$this->clear();
		$this->_extractNodes($xml_data, 0, $last_position = 0);
	}

	function selectNodes($query_string, $node_id = 0, $single_node = false)
	{
		$result = array();

		foreach (explode('|', $query_string) as $query_string) {

			$query_node_id = $node_id;
			$query_nodes   = array();

			$query_string = trim($query_string) . '/';
			if (substr($query_string, 0, 1) === '/') {
				$query_node_id = 0;
			}

			while (true) {

				$query_node = array(
					'nodeName'   => '',
					'predicates' => '',
					'global'     => false
				);

				if (substr($query_string, 0, 2) === '//') {
					$query_node['global'] = true;
					$query_string = substr($query_string, 2);
				} elseif (substr($query_string, 0, 1) === '/') {
					$query_string = substr($query_string, 1);
				}

				$position = strpos($query_string, '/');
				if ($position !== false) {

					$node_name    = trim(substr($query_string, 0, $position));
					$query_string = substr($query_string, $position);

					if (substr($node_name, -1) === ']') {
						$position = strrpos($node_name, '[');
						if ($position !== false) {
							$query_node['predicates'] = preg_replace('/([^=<>!]{1})=([^=]{1})/', '${1}==${2}', trim(substr($node_name, $position + 1, -1)));
							if (preg_match('/^[0-9\.\+\-\*\/\(\) ]+$/', $query_node['predicates'])) {
								$query_node['predicates'] = @eval('return ' . $query_node['predicates'] . ';');
							}
							$node_name = trim(substr($node_name, 0, $position));
						}
					}
					$query_node['nodeName'] = $node_name;

				} else {
					break;
				}

				$query_nodes[] = $query_node;
			}

			$result += $this->_searchNodes($query_node_id, $query_nodes, 0, $single_node);
		}

		return $result;
	}

	function selectSingleNode($query_string, $node_id = 0)
	{
		return $this->selectNodes($query_string, $node_id, true);
	}

	function setAttribute($node_id, $name, $value)
	{
		$this->nodes[$node_id]['attributes'][$name] = $value;
	}

	function setText($node_id, $text)
	{
		return $this->nodes[$node_id]['text'] = $text;
	}

	/////////////////////////////////////////////////
	// PRIVATE
	/////////////////////////////////////////////////

	function _decodeEntities($string)
	{
		return str_replace(array('&lt;', '&gt;', '&apos;', '&quot;', '&amp;'), array('<', '>', '\'', '"', '&'), $string);
	}

	function _encodeEntities($string)
	{
		return str_replace(array('&', '<', '>', '\'', '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), $string);
	}

	function _extractNodes($xml_data, $parent_id, &$last_position)
	{
		while (true) {

			$tag_start = strpos($xml_data, '<', $last_position);
			$tag_end   = ($tag_start !== false ? strpos($xml_data, '>', $tag_start + 1) : false);
			if ($tag_end !== false) {

				$this->setText($parent_id, $this->_decodeEntities(substr($xml_data, $last_position, $tag_start - $last_position)));
				$tag_data = trim(substr($xml_data, $tag_start + 1, $tag_end - $tag_start - 1));
				$last_position = $tag_end + 1;

				if (substr($tag_data, 0, 1) === '/') {
					break;
				} elseif ((substr($tag_data, 0, 1) . '|' . substr($tag_data, -1)) === '?|?') {
					//XML DIRECTIVES
				} elseif ((substr($tag_data, 0, 3) . '|' . substr($tag_data, -2)) === '!--|--') {
					//COMMENT
				} else {

					$attributes = array(0 => '');
					for ($i = 0, $quote = 0; $i < strlen($tag_data); $i++) {
						    if ($tag_data[$i] === ' ' && !$quote) {$attributes[] = '';}
						elseif ($tag_data[$i] === '/' && !$quote) {}
						elseif ($tag_data[$i] === '"' && (!$quote || ($quote & 1))) {$quote ^= 1;}
						elseif ($tag_data[$i] === "'" && (!$quote || ($quote & 2))) {$quote ^= 2;}
						  else {$attributes[count($attributes) - 1] .= $tag_data[$i];}
					}

					$node_id = $this->createNode($parent_id, array_shift($attributes));
					foreach ($attributes as $attribute) {
						$index = strpos($attribute, '=');
						if ($index !== false) {
							$this->setAttribute($node_id, substr($attribute, 0, $index), $this->_decodeEntities(substr($attribute, $index + 1)));
						}
					}

					if (substr($tag_data, -1) !== '/') {
						$this->_extractNodes($xml_data, $node_id, $last_position);
					}
				}

			} else {
				break;
			}
		}
	}

	function _searchNodes($node_id, &$query_nodes, $query_node_id, $single_node)
	{
		$result = array();
		$return_nodes = ((count($query_nodes) - 1) == $query_node_id);
		$node_index   = 1;

		foreach ($this->nodes[$node_id]['childNodes'] as $child_node) {
			if (($this->nodes[$child_node]['nodeName'] === $query_nodes[$query_node_id]['nodeName']) || ($query_nodes[$query_node_id]['nodeName'] === '*')) {

				$predicates = $query_nodes[$query_node_id]['predicates'];
				if ($predicates) {
					if (is_numeric($predicates)) {
						$predicates = $predicates . '===' . $node_index;
					} elseif (preg_match_all('/@([\w:]+)/', $predicates, $matches)) {
						foreach ($matches[1] as $match) {
							$value = $this->getAttribute($child_node, $match);
							$predicates = preg_replace('/@' . $match . '/', (is_numeric($value) ? $value : '\'' . str_replace('\'', '\\\'', $value) . '\''), $predicates, 1);
						}
					}
				}

				if (($predicates === '') || @eval('return ' . $predicates . ';')) {
					if ($return_nodes) {
						if ($single_node) {
							$result = &$this->nodes[$child_node];
							break;
						} else {
							$result[$child_node] = &$this->nodes[$child_node];
						}
					} else {
						$result += $this->_searchNodes($child_node, $query_nodes, $query_node_id + 1, $single_node);
					}
				}

				$node_index++;
			}
			if ($query_nodes[$query_node_id]['global']) {
				$result += $this->_searchNodes($child_node, $query_nodes, $query_node_id, $single_node);
			}
		}
		return $result;
	}

}

