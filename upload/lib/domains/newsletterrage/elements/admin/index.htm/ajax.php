<?php

	global $System, $DB, $Session;
    $data    = get('data');
    $format  = get('format');
    $subject = get('subject');
    $message = get('message');
    
    if ($format) {
        $Session->set('texthtml', $format);
    }
    
    if ($data == 'membercount') {
        echo $DB->count('SELECT address FROM mailinglist_subscribers WHERE confirmed');
    }    
    
    if ($data == 'data') {
        $data = array(
            'subject' => $Session->get('subject'),
            'message' => $Session->get('message'),
            'texthtml' => $Session->get('texthtml'),
            'lastsaved' => 0
        ); 
        print_pre($data);   
    }
    
    if ($subject) {
        $Session->set('subject', $subject);
    }
    
    if ($message) {
        $Session->set('message', $message);
    }