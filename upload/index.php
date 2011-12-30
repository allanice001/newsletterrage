<?php
    define('ROOT', '.');
    
    if (!file_exists(ROOT . '/config.php')) {
        header('Location: /install.php');
    }
    
    require(ROOT . '/lib/config.php');
    if (isset($_GET['guielement'])) {
        GUIOutputElement();
    } else {
        GUIOutputPage();
    }
    

