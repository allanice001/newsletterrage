<?php

	//PLACE CODE TO PROCESS AJAX REQUESTS FROM THE CLIENT HERE EG. /request/example.htm

	global $System, $DB;
    $data = get('data');
    $format = get('format');
    
    if ($format) {$Session->set('format', $format);}
    
    if ($data == 'membercount') {
        echo $DB->count('SELECT address FROM mailinglist_subscribers WHERE confirmed');
    }
    
    if ($data == 'data') {
        print_pre($data);
    }
    
    if ($data == 'format' ){
        echo $format;
    }