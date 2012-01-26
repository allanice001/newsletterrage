<?php

	global $System, $DB, $Session;
    $data    = get('data');
    $format  = get('format');
    $subject = get('subject');
    
    if ($format) {
        $Session->set('texthtml', $format);
        echo '<b>Format = '. $format . '</b>';
    }
    
    if ($data == 'membercount') {
        echo $DB->count('SELECT address FROM mailinglist_subscribers WHERE confirmed');
    }    
    
    if ($data == 'format') {
        $format = $DB->lookup('SELECT value FROM gui_configuration WHERE name="texthtml" AND group_id = 5');
    }
    
    if ($data == 'subject') {
        echo $Session->get('subject');
    }
    
    if ($subject) {
        $Session->set('subject', $subject);
    }
    
    