<?
    global $DB;
    $action = get('action');
    if($action) {
        print_pre($_POST);
        $message = get('message');
        echo htmlentities($message);
        $draftid = get('draftid', 0);
        
        if ($draftid) {
            //Update Selected Draft 
        $DB->query('UPDATE mailinglist_drafts SET');
        } else {
            //Create New Draft
            $DB->query('INSERT INTO mailinglist_drafts () VALUES ()');
        }        
    }
