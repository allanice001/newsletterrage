<?php

	//PREVENT MAGIC QUOTES
	if (get_magic_quotes_gpc()) {
		$_GET  = array_map('stripslashes', $_GET);
		$_POST = array_map('stripslashes', $_POST);
	}

	$System = array();
	$System['paths'] = array(
		'classes'    => ROOT . '/lib/classes/',
		'domains'    => ROOT . '/lib/domains/',
		'layouts'    => ROOT . '/lib/layouts/',
		'frameworks' => ROOT . '/lib/frameworks/',
		'elements'   => '/elements/',
		'includes'   => '/includes/'
	);

	//CONNECT TO THE DATABASE
	require($System['paths']['classes'] . 'db.php');
	require(ROOT . '/config.php');
    //$GLOBALS['DB'] = new DB_Connection('localhost', 'root', 'abc123', 'framework');

	/////////////////////////////////////////////////
	// CAPTURE USER AGENT
	/////////////////////////////////////////////////

	$http_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
	$http_accept     = isset($_SERVER['HTTP_ACCEPT'])     ? $_SERVER['HTTP_ACCEPT']     : '';

	if ($record = $DB->lookup('SELECT a.bot, a.downloader, a.mobile FROM gui_user_agents a WHERE a.name = "' . $DB->escape($http_user_agent) . '" LIMIT 1', false)) {
		$System['browser'] = $record;
	} else {
		$System['browser'] = array(
			'bot'        => (preg_match('/bot|slurp|yandex|spider|crawl|httrack|^ia_|teoma|find|^java|urllib|libwww|jakarta|curl|wget|validator|^axel|rabobi|fetch|^word/i', $http_user_agent)) ? 1 : 0,
			'downloader' => (preg_match('/^getright|^flashget|^leechget|^dap|^.{0,9}download/i', $http_user_agent)) ? 1 : 0,
			'mobile'     => (preg_match('/MIDP|Symbian|Windows CE|PDA;|Palm[OS]|UP\.Br|\/CLDC|Ericsson|Nokia|BlackBerry/i', $http_user_agent) || preg_match('/vnd\.wap\.wml/i', $http_accept)) ? 1 : 0
		);
		$DB->query('INSERT INTO gui_user_agents (name, bot, downloader, mobile) VALUES ("' . $DB->escape($http_user_agent) . '",' . $DB->escape($System['browser']['bot']) . ',' . $DB->escape($System['browser']['downloader']) . ',' . $DB->escape($System['browser']['mobile']) . ')');
	}

	/////////////////////////////////////////////////
	// SYSTEM CONFIGURATION
	/////////////////////////////////////////////////

	//DETECT CURRENT DOMAIN AND COLLECT INFORMATION
	$rs_domain = $DB->query('SELECT d.id, d.name, d.title, d.http, d.https, d.root FROM gui_domains d WHERE "' . $DB->escape($_SERVER['HTTP_HOST']) . '" REGEXP d.regexp OR d.def = 1 ORDER BY d.def LIMIT 1');
	if ($rs_domain->next()) {
		$System['domain'] = $rs_domain->record;
	}
	$rs_domain->free();

	//GET CONFIGURATION SETTINGS AND DEFAULTS
	$rs_config = $DB->query('SELECT cg.name AS group_name, c.name, c.value, c.evaluate FROM gui_configuration c JOIN gui_configuration_groups cg ON cg.id = c.group_id WHERE c.domain_id IN (0,' . $System['domain']['id'] . ') ORDER BY c.domain_id, cg.rank, c.rank');
	while ($rs_config->next()) {
		if ($rs_config->record['name']) {
			$System[$rs_config->record['group_name']][$rs_config->record['name']] = $rs_config->record['evaluate'] ? @eval('return "' . $rs_config->record['value'] . '";') : $rs_config->record['value'];
		} else {
			@eval($rs_config->record['value']);
		}
	}
	$rs_config->free();

	/////////////////////////////////////////////////
	// DEPENDENCIES
	/////////////////////////////////////////////////

	//INCLUDE COMMON CLASSES
	require($System['paths']['classes'] . 'base.php');
	require($System['paths']['classes'] . 'session.php');

