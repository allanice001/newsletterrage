<?php

	/////////////////////////////////////////////////
	// CONSTANTS
	/////////////////////////////////////////////////

	define('SCRIPT_START', '<script type="text/javascript">/*<![CDATA[*/');
	define('SCRIPT_END',   '/*]]>*/</script>');

	/////////////////////////////////////////////////
	// STRING FUNCTIONS
	/////////////////////////////////////////////////

	/**
	 * Returns an array of strings divided by the delimiter. The delimiter is ignored within quoted strings
	 * @param string $delimiter The boundary string
	 * @param string $string The input string
	 * @return mixed
	 */
	function explodeQuote($delimiter, $string)
	{
		$results = array(0 => '');
		for ($i = 0, $quote = 0, $delim = ($delimiter ? $delimiter[0] : ' '); $i < strlen($string); $i++) {
			    if (($string[$i] === $delim) && !$quote) {$results[] = '';}
			elseif (($string[$i] === '"')    && (!$quote || ($quote & 1))) {$quote ^= 1;}
			  else {$results[count($results) - 1] .= $string[$i];}
		}
		return $results;
	}

	/**
	 * Highlights the specified words in the given text
	 * @param string $text The text that you wish to highlight
	 * @param mixed $words Can be passed a string or array of strings specifying words to highlight
	 * @param bool $case_sensitive
	 * @param mixed $prefix If passed an array the words will be highlighted in rotation, eg. '<b>' or array('<b>', '<u>')
	 * @param mixed $suffix If passed an array it must contain the same amount of entries as $prefix, eg. '</b>' or array('</b>', '</u>')
	 * @return string
     */
	function highlight($text, $words = array(), $case_sensitive = false, $prefix = '<u>', $suffix = '</u>') {
		$prefix = is_array($prefix) ? $prefix : array($prefix);
		$prefix_index = 0;
		foreach ((is_array($words) ? $words : array($words)) as $word) {
			if (trim($word)) {
				$index = ($prefix_index++ % count($prefix)) * 2;
				$text = preg_replace('/(' . preg_replace('/([\\\\\^\$\*\+\?\.\(\)\{\}\[\]\|\&\/]){1}/', '\\\\${1}', $word) . ')/' . ($case_sensitive ? '' : 'i'), (chr($index) . '${1}' . chr($index + 1)), $text);
			}
		}
		for ($prefix_index = 0; $prefix_index < count($prefix); $prefix_index++) {
			$index = $prefix_index * 2;
			$text = str_replace(array(chr($index), chr($index + 1)), array($prefix[$prefix_index], (is_array($suffix) ? $suffix[$prefix_index] : $suffix)), $text);
		}
		return $text;
	}

	/**
	 * Print a variable or array inside <PRE></PRE> tags
	 */
	function print_pre($expression)
	{
		echo '<pre>';
		print_r($expression);
		echo '</pre>';
	}

	/////////////////////////////////////////////////
	// SYSTEM FUNCTIONS
	/////////////////////////////////////////////////

	/**
	 * Searches the $_POST and $_GET arrays for an item, or returns a default value
	 * @param string $name Used as a key for reading an array item value
	 * @param mixed $default The default value returned if the item does not exist
	 * @param bool $get Should the $_GET array be searched
	 * @param bool $post Should the $_POST array be searched
	 * @return mixed
	 */
	function get($name, $default = '', $get = true, $post = true)
	{
		return $post && isset($_POST[$name]) ? $_POST[$name] : ($get && isset($_GET[$name]) ? $_GET[$name] : $default);
	}

	/////////////////////////////////////////////////
	// IMAGE FUNCTIONS
	/////////////////////////////////////////////////

	/**
	 * Locates an image by id/key in the database then appends its relative directory location
	 * @param mixed $index
	 * @param mixed $attribute Specifies the desired attribute of the image, NULL returns an associated Array
	 * @return mixed
	 */
	function image($index, $attribute = 'image')
	{
		global $System, $DB;
		static $image_cache = array(0 => array('image' => '', 'tooltip' => '', 'width' => 0, 'height' => 0));

		if (!array_key_exists($index, $image_cache)) {
			$image_cache[$index] = $image_cache[0];
			$rs_image = $DB->query('SELECT i.id, i.alias, i.image, ig.name AS image_group, i.tooltip, i.width, i.height FROM images i LEFT OUTER JOIN image_groups ig ON ig.id = i.group_id WHERE ' . (is_numeric($index) ? 'i.id = ' . $index : 'i.alias = "' . $DB->escape($index) . '"') . ' LIMIT 1');
			if ($record = $rs_image->next()) {
				$image_cache[$record['id']] = array('image' => @eval('return "' . (imagePath($record['image_group']) . $record['image']) . '";'), 'tooltip' => $record['tooltip'], 'width' => $record['width'], 'height' => $record['height']);
				if ($record['alias']) {
					$image_cache[$record['alias']] = &$image_cache[$record['id']];
				}
			}
			$rs_image->free();
		}
		return $attribute ? $image_cache[$index][$attribute] : $image_cache[$index];
	}

	/**
	 * Creates a web folder path to an image location
	 * @param string $path This path is appended to the final result
	 * @param bool $full_path Adds the HTML prefixes to the path
	 * @param bool $local_path Return the image path relative to the server
	 * @return string
	 */
	function imagePath($path = '', $full_path = false, $local_path = false)
	{
		global $System;

		return
			($local_path ? ROOT : ($full_path ? ($System['page']['secure'] ? $System['domain']['https'] : $System['domain']['http']) : '')) .
			$System['paths']['assets'] . 'images/' .
			($path ? $path . (substr($path, -1) != '/' ? '/' : '') : '');
	}

	/**
	 * Returns an HTML image created from the detail of $index
	 * @param mixed $index
	 * @param mixed $tooltip Overides the default tooltip
	 * @return string
	 */
	function imageTag($index, $tooltip = NULL)
	{
		$image = image($index, NULL);
		if ($image['image']) {
			if (!is_null($tooltip)) {$image['tooltip'] = $tooltip;}
			return '<img src="' . $image['image'] . '"' . ($image['tooltip'] ? ' title="' . $image['tooltip'] . '"' : '') . ' alt="' . $image['tooltip'] . '"' . ($image['width'] ? ' width="' . $image['width'] . '"' : '') . ($image['height'] ? ' height="' . $image['height'] . '"' : '') . ' />';
		}
	}

	/////////////////////////////////////////////////
	// URL FUNCTIONS
	/////////////////////////////////////////////////

	/**
	 * Redirects the current page to another location
	 * @param string $url
	 * @param bool $resend_data
	 */
	function redirect($url, $resend_data = false)
	{
		header('Location: ' . $url);
		if ($resend_data) {header('HTTP/1.1 307 Temporary Redirect');}
		header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		exit(0);
	}

	/**
	 * Builds a new URL
	 * @param string $page The page alias or filename
	 * @param array $query_string The values for the querystring, eg. array('x' => 'y') + $_GET
	 * @param string $options Any combonation of the following parameters: 'fsr'
	 * 	f (Adds http domain prefix)
	 * 	s (Adds https domain prefix)
	 * 	r (Returns url unencoded, used with redirects)
	 * @return string
	 */
    function url($page = '', $query_string = array(), $options = '')
	{
		global $System;

		$page  = isset($System['pages'][$page]) ? $System['pages'][$page] : $page;
		$flags = (strpos($options, 'f') !== false ? 1 : 0) + (strpos($options, 's') !== false ? 2 : 0) + (strpos($options, 'r') !== false ? 4 : 0);
		$query = '';
		$link  = '';

		foreach ($query_string as $key => $value) {
			if ($key === '#') {$link = $value;} else {
			if ($value) {$query .= ($query ? '&' : '') . ($key . '=' . urlencode($value));}}
		}

		$url = ($flags & 3  ? ($flags & 2 ? $System['domain']['https'] : $System['domain']['http']) : '') . ($System['domain']['root'] . $page) . ($query ? ((strpos($page, '?') === false ? '?' : '&') . $query) : '') . ($link ? ('#' . $link) : '');
		return ($flags & 4) ? $url : htmlentities($url, ENT_QUOTES);
	}

	/**
	 * Reconstructs the full URL path of the current page
	 * @param bool $html_encode Escape the URL allowing strings to be passed
	 * @return string
	 */
	function urlPath($html_encode = true)
	{
		$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		return $html_encode ? htmlentities($url, ENT_QUOTES) : $url;
	}

	/**
	 * Returns a URL path for element ajax requests
	 * @param string $element_alias The GUI Element alias, defaults to the current element.
	 * @return string
	 */
	function urlRequest($element_alias = '')
	{
		global $System, $DB;

		return '/request/' . ($element_alias ? $DB->lookup('SELECT e.name FROM gui_elements e WHERE e.alias = "' . $DB->escape($element_alias) . '" LIMIT 1', $element_alias) : (isset($System['element']['name']) ? $System['element']['name'] : 'ajax'));
	}

	/////////////////////////////////////////////////
	// WEBSITE GUI FUNCTIONS
	/////////////////////////////////////////////////

	/**
	 * Outputs the Current Pages Layout and Content
	 */
	function GUIOutputPage()
	{
		global $System, $DB;

		$_gui_reference = '';
		$_gui_elements  = array(0 => array('init' => array(), 'view' => array()));
		$_gui_includes  = array(
			'elements'        => array(),
			'includes'        => array('global', 'common'),
			'include_styles'  => array(),
			'include_scripts' => array(),
			'element_styles'  => array(),
			'element_scripts' => array()
		);

		if (isset($_GET['guipage'])) {
			$_gui_reference = $_GET['guipage'];
			unset($_GET['guipage']);
		} else {
			$_gui_reference = substr($_SERVER['PHP_SELF'], 1);
		}
		$_rs_gui_page = $DB->query(
			'SELECT gp.id, gp.alias, gp.name, gp.regexp, gp.title, gp.description, gp.layout_id, gl.layout_id AS layout_regions_id, gl.framework, gl.layout' .
			' FROM gui_pages gp' .
			' JOIN gui_layouts gl ON gl.id = gp.layout_id' .
			' WHERE gp.domain_id IN (0,' . $System['domain']['id'] . ') AND (gp.name = "' . $DB->escape($_gui_reference) . '" OR gp.def OR "' . $DB->escape($_gui_reference) . '" REGEXP gp.regexp) AND gp.mobile = ' . $System['browser']['mobile'] . ' AND !gp.disabled' .
			' ORDER BY gp.domain_id DESC, gp.def' .
			' LIMIT 1'
		);
		if ($_gui_page = $_rs_gui_page->next()) {

			$System['page'] = array(
				'id'          => $_gui_page['id'],
				'reference'   => $_gui_reference,
				'alias'       => $_gui_page['alias'],
				'name'        => $_gui_page['name'],
				'title'       => $_gui_page['title'],
				'description' => $_gui_page['description'],
				'secure'      => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'),
				'style'       => '',
				'script'      => '',
				'param'       => array(),
				'keywords'    => array()
			);
			if ($_gui_page['regexp']) {
				if (preg_match('/' . str_replace('/', '\/', $_gui_page['regexp']) . '/i', $_gui_reference, $matches)) {
					array_shift($matches);
					$System['page']['param'] = $matches;
				}
			}
			$System['pages']    = array();
			$System['elements'] = array();

			$_rs_gui_pages = $DB->query(
				'SELECT p.alias, p.name' .
				' FROM gui_pages p' .
				' WHERE p.domain_id IN (0,' . $System['domain']['id'] . ') AND p.mobile = ' . $System['browser']['mobile'] . ' AND !p.disabled AND p.alias != ""' .
				' ORDER BY p.domain_id'
			);
			while ($record = $_rs_gui_pages->next()) {
				$System['pages'][$record['alias']] = $record['name'];
			}
			$_rs_gui_pages->free();

			$_rs_gui_layout_elements = $DB->query(
				'SELECT ge.id AS element_id, ge.name AS element, ge.uses AS element_uses, g.region_id' .
				' FROM gui g' .
				' JOIN gui_elements ge ON ge.id = g.element_id' .
				' WHERE (g.domain_id IN (0,' . $System['domain']['id'] . ') AND g.layout_id = ' . $_gui_page['layout_id'] . ') OR g.page_id = ' . $System['page']['id'] .
				' ORDER BY g.region_id, g.rank'
			);
			while ($record = $_rs_gui_layout_elements->next()) {
				$element_global_path = $System['paths']['domains'] . 'global'                  . $System['paths']['elements'] . $record['element'] . '/';
				$element_local_path  = $System['paths']['domains'] . $System['domain']['name'] . $System['paths']['elements'] . $record['element'] . '/';
				$System['elements'][$record['element_id']] = array('id' => $record['element_id'], 'used' => 0, 'name' => $record['element']);
				if (file_exists($element_file = $element_local_path . 'init.php')  || file_exists($element_file = $element_global_path . 'init.php'))  {$System['elements'][$record['element_id']]['init']   = $element_file; $_gui_elements[0]['init'][$record['element_id']] = $record['element_id'];}
				if (file_exists($element_file = $element_local_path . 'style.css') || file_exists($element_file = $element_global_path . 'style.css')) {$System['elements'][$record['element_id']]['style']  = $element_file; $_gui_includes['element_styles'][$record['element_id']]  = $record['element_id'];}
				if (file_exists($element_file = $element_local_path . 'script.js') || file_exists($element_file = $element_global_path . 'script.js')) {$System['elements'][$record['element_id']]['script'] = $element_file; $_gui_includes['element_scripts'][$record['element_id']] = $record['element_id'];}
				if (file_exists($element_file = $element_local_path . 'view.php')  || file_exists($element_file = $element_global_path . 'view.php'))  {$System['elements'][$record['element_id']]['view']   = $element_file; if ($record['region_id']) {$_gui_elements[$record['region_id']][] = $record['element_id'];} else {$_gui_elements[0]['view'][$record['element_id']] = $record['element_id'];}}
				$System['elements'][$record['element_id']]['data'] = array();
				if ($record['element_uses']) {$_gui_includes['elements'][$record['element_id']] = $record['element_uses'];}
			}
			$_rs_gui_layout_elements->free();

			$_gui_element_includes_got = array();
			while ($_gui_element_includes_get = array_diff(array_unique(preg_split('/[\s,;]+/', $DB->escape(trim(implode(',', $_gui_includes['elements']))), -1, PREG_SPLIT_NO_EMPTY)), $_gui_element_includes_got)) {
				$_gui_includes['elements'] = array();
				$_gui_element_includes_got = array_merge($_gui_element_includes_got, $_gui_element_includes_get);
				$_rs_gui_layout_includes   = $DB->query('SELECT gi.name, gi.uses FROM gui_includes gi WHERE (gi.alias != "") AND (gi.alias LIKE "' . str_replace('*', '%', implode('" OR gi.alias LIKE "', $_gui_element_includes_get)) . '")');
				while ($record = $_rs_gui_layout_includes->next()) {$_gui_includes['includes'][$record['name']] = $record['name']; if ($record['uses']) {$_gui_includes['elements'][] = $record['uses'];}}
				$_rs_gui_layout_includes->free();
			}

			if ($_gui_includes['includes']) {
				$_rs_gui_layout_includes = $DB->query(
					'SELECT gi.name' .
					' FROM gui_includes gi' .
					' WHERE gi.name IN ("' . implode('","', $_gui_includes['includes']) . '")' .
					' ORDER BY gi.rank'
				);
				while ($record = $_rs_gui_layout_includes->next()) {
					$include_global_path = $System['paths']['domains'] . 'global'                  . $System['paths']['includes'] . $record['name'] . '/';
					$include_local_path  = $System['paths']['domains'] . $System['domain']['name'] . $System['paths']['includes'] . $record['name'] . '/';
					if (file_exists($include_file = $System['paths']['classes'] . $record['name'] . '.php')) {require($include_file);}
					if (file_exists($include_file = $include_local_path . 'style.css') || file_exists($include_file = $include_global_path . 'style.css')) {$_gui_includes['include_styles'][]  = $include_file;}
					if (file_exists($include_file = $include_local_path . 'script.js') || file_exists($include_file = $include_global_path . 'script.js')) {$_gui_includes['include_scripts'][] = $include_file;}
				}
				$_rs_gui_layout_includes->free();
			}

			foreach ($_gui_elements[0]['init'] as $element_id) {$System['element'] = &$System['elements'][$element_id]; $GLOBALS['Private'] = &$System['elements'][$element_id]['data']; require($System['element']['init']);}
			ob_start();
				if ($_gui_page['layout']) {require($System['paths']['layouts'] . $_gui_page['layout'] . '.php');}
				foreach ($_gui_includes['include_styles'] as $include)    {require($include);}
				foreach ($_gui_includes['element_styles'] as $element_id) {$System['element'] = &$System['elements'][$element_id]; $GLOBALS['Private'] = &$System['elements'][$element_id]['data']; require($System['element']['style']);}
				$System['page']['style'] = preg_replace(array('/\s+/', '/\s*({|}|\(|:|;|,){1}\s*/', '/\/\*.*?\*\//'), array(' ', '$1', ''), ($System['page']['style'] . ob_get_contents()));
			ob_end_clean();
			ob_start();
				foreach ($_gui_includes['include_scripts'] as $include)    {require($include);}
				foreach ($_gui_includes['element_scripts'] as $element_id) {$System['element'] = &$System['elements'][$element_id]; $GLOBALS['Private'] = &$System['elements'][$element_id]['data']; require($System['element']['script']);}
				$System['page']['script'] .= ob_get_contents();
			ob_end_clean();
			if ($_gui_page['framework']) {require($System['paths']['frameworks'] . $_gui_page['framework'] . '/' . 'startup.php');}
			foreach ($_gui_elements[0]['view'] as $element_id) {$System['element'] = &$System['elements'][$element_id]; $GLOBALS['Private'] = &$System['elements'][$element_id]['data']; $System['element']['used']++; require($System['element']['view']);}

			if ($_gui_page['layout_regions_id']) {
				$_gui_layout = array(
					0 => array(
						'children' => array()
					)
				);
				$_rs_gui_layout = $DB->query(
					'SELECT glr.id, glr.parent_id, glr.region_id, glr.class' .
					' FROM gui_layout_regions glr' .
					' WHERE glr.layout_id = ' . $DB->escape($_gui_page['layout_regions_id']) .
					' ORDER BY glr.rank'
				);
				while ($record = $_rs_gui_layout->next()) {
					$_gui_layout[$record['id']] = array(
						'parent'   => $record['parent_id'],
						'region'   => $record['region_id'],
						'class'    => $record['class'],
						'children' => array()
					);
				}
				$_rs_gui_layout->free();
				foreach ($_gui_layout as $_gui_layout_section_id => $_gui_layout_section) {
					if ($_gui_layout_section_id) {$_gui_layout[$_gui_layout_section['parent']]['children'][] = $_gui_layout_section_id;}
				}
				$_gui_layout_section_id = 0;
				$_gui_layout[0]['index'] = 0;
				while (true) {
					if ($_gui_layout[$_gui_layout_section_id]['index'] < count($_gui_layout[$_gui_layout_section_id]['children'])) {
						$_gui_layout_section_id = $_gui_layout[$_gui_layout_section_id]['children'][$_gui_layout[$_gui_layout_section_id]['index']];
						$_gui_layout[$_gui_layout_section_id]['index'] = 0;
						echo '<div class="' . $_gui_layout[$_gui_layout_section_id]['class'] . '">';
						if ($_gui_layout[$_gui_layout_section_id]['region']) {
							if (isset($_gui_elements[$_gui_layout[$_gui_layout_section_id]['region']])) {
								foreach ($_gui_elements[$_gui_layout[$_gui_layout_section_id]['region']] as $element_id) {$System['element'] = &$System['elements'][$element_id]; $GLOBALS['Private'] = &$System['elements'][$element_id]['data']; $System['element']['used']++; require($System['element']['view']);}
							}
						}
					} elseif ($_gui_layout_section_id) {
						echo (count($_gui_layout[$_gui_layout_section_id]['children']) > 1 ? '<div style="clear: both;"></div>' : '') . '</div>';
						$_gui_layout_section_id = $_gui_layout[$_gui_layout_section_id]['parent'];
						$_gui_layout[$_gui_layout_section_id]['index']++;
					} else {
						break;
					}
				}
			} else {
				foreach ($_gui_elements as $_gui_layout_region_id => $_gui_layout_region) {
					if ($_gui_layout_region_id) {
						foreach ($_gui_layout_region as $element_id) {$System['element'] = &$System['elements'][$element_id]; $GLOBALS['Private'] = &$System['elements'][$element_id]['data']; $System['element']['used']++; require($System['element']['view']);}

					}
				}
			}

			if ($_gui_page['framework']) {require($System['paths']['frameworks'] . $_gui_page['framework'] . '/' . 'shutdown.php');}
		}
		$_rs_gui_page->free();

	}

	/**
	 * Outputs an Elements AJAX request
	 */
	function GUIOutputElement()
	{
		global $System, $DB;

		$_gui_reference = '';
		$_gui_includes  = array(
			'elements' => array(),
			'includes' => array()
		);

		if (isset($_GET['guielement'])) {
			$_gui_reference = $_GET['guielement'];
			unset($_GET['guielement']);
		}
		$_rs_gui_element = $DB->query(
			'SELECT ge.id, ge.name, ge.uses' .
			' FROM gui_elements ge' .
			' WHERE ge.name = "' . $DB->escape($_gui_reference) . '"' .
			' LIMIT 1'
		);
		if ($_gui_element = $_rs_gui_element->next()) {

			$System['element'] = array(
				'id'   => $_gui_element['id'],
				'name' => $_gui_element['name']
			);
			if ($_gui_element['uses']) {$_gui_includes['elements'][] = $_gui_element['uses'];}

			$_gui_element_includes_got = array();
			while ($_gui_element_includes_get = array_diff(array_unique(preg_split('/[\s,;]+/', $DB->escape(trim(implode(',', $_gui_includes['elements']))), -1, PREG_SPLIT_NO_EMPTY)), $_gui_element_includes_got)) {
				$_gui_includes['elements'] = array();
				$_gui_element_includes_got = array_merge($_gui_element_includes_got, $_gui_element_includes_get);
				$_rs_gui_layout_includes   = $DB->query('SELECT gi.name, gi.uses FROM gui_includes gi WHERE (gi.alias != "") AND (gi.alias LIKE "' . str_replace('*', '%', implode('" OR gi.alias LIKE "', $_gui_element_includes_get)) . '")');
				while ($record = $_rs_gui_layout_includes->next()) {$_gui_includes['includes'][$record['name']] = $record['name']; if ($record['uses']) {$_gui_includes['elements'][] = $record['uses'];}}
				$_rs_gui_layout_includes->free();
			}

			if ($_gui_includes['includes']) {
				$_rs_gui_element_includes = $DB->query(
					'SELECT gi.name' .
					' FROM gui_includes gi' .
					' WHERE gi.name IN ("' . implode('","', $_gui_includes['includes']) . '")' .
					' ORDER BY gi.rank'
				);
				while ($record = $_rs_gui_element_includes->next()) {
					if (file_exists($include_file = $System['paths']['classes'] . $record['name'] . '.php')) {require($include_file);}
				}
				$_rs_gui_element_includes->free();
			}

			$element_global_path = $System['paths']['domains'] . 'global'                  . $System['paths']['elements'] . $_gui_element['name'] . '/';
			$element_local_path  = $System['paths']['domains'] . $System['domain']['name'] . $System['paths']['elements'] . $_gui_element['name'] . '/';
			if (file_exists($element_file = $element_local_path . 'ajax.php') || file_exists($element_file = $element_global_path . 'ajax.php')) {require($element_file);}

		}
		$_rs_gui_element->free();

	}

