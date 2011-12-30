<?php

	global $System;

	echo
		'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' .
		'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">' .
			'<head>' .
				'<title>' . htmlentities($System['page']['title'] . ($System['domain']['title'] ? ' - ' . $System['domain']['title'] : '')) . '</title>' .
				($System['page']['description'] ? '<meta name="description" content="' . htmlentities($System['page']['description']) . '" />' : '') .
				($System['page']['keywords']    ? '<meta name="keywords" content="'    . htmlentities(implode(', ', $System['page']['keywords'])) . '" />' : '') .
				'<meta name="robots" content="all, index, follow" />' .
				'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' .
				'<meta name="generator" content="Trevor Lewis - Thraddash Software v' . $System['app']['version'] . '" />' .
				'<link rel="shortcut icon" href="' . imagePath('common') . 'icon.ico" />' .
				($System['page']['style']  ? '<style type="text/css">' . $System['page']['style'] . '</style>' : '') .
				($System['page']['script'] ? SCRIPT_START . $System['page']['script'] . SCRIPT_END : '') .
			'</head>' .
			'<body>';

