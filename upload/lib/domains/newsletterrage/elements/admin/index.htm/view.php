<script type="text/javascript" src="/ckeditor/ckeditor.js"></script>
<?php
    global $DB, $Session;
    $today = getdate();
    $draftid = get('draftid', 0);
    $templateid = get('templateid', 0);
    $format = $Session->get('texthtml', $DB->lookup('SELECT value FROM gui_configuration WHERE name="texthtml" AND group_id = 5'));
    $subject = $Session->get('subject');
    $message = $Session->get('message');        
    $data = array(
        'subject' => '',
        'message' => '',
        'texthtml' => $format,
        'lastsaved' => 0
    );    
    
    if($draftid) {
    	$draft = $DB->getArray('SELECT * FROM mailinglist_drafts WHERE id = '. $draftid);
    	$data = $draft[$draftid];
    }
    
    if ($templateid) {
        $template = $DB->getArray('SELECT * FROM mailinglist_templates WHERE id = '. $templateid);
        $data = $template[$templateid];
    }    
?>
<script type="text/javascript">
    function formatupdate(value) {
        $('<?php echo urlRequest();?>').request({
            post : {format : value}, 
            onSuccess : function($response)
                    {
                            setTimeout(function()
                            {
                                window.location= '/admin/index.php';
                            }, 100);
                        }
                    });
    }

    function subjectupdate(value) {
        $('<?php echo urlRequest();?>').request({
            post : {subject : value},
            onSuccess : function($response)
                {
                    setTimeout(function()
                    {
                        window.location= '/admin/index.php';
                    }, 10000);
                    document.getElementById('subject').focus();
                }
        });
    }

    function messageupdate(value) {
        $('<?php echo urlRequest();?>').request({
            post : {message : value},
            onSuccess : function($response)
                {
                    setTimeout(function()
                    {
                        window.location= '/admin/index.php';
                    }, 100);
                }
        });
    }

</script>
<form method="POST" action="<?echo urlPath();?>" name="form1" id="form1"> <!-- onsubmit="return besure('<? //Date("T")?>');"> -->
    <p class="message">If sent now, message will be delivered to <span id="mc">0</span> subscribers. Send as 
	<select size="1" name="format" id="format" onchange="formatupdate(this.value);">
    <!-- <select size="1" name="format" onchange="format_text(this.value)"> -->
		<option value="1"<?=($data['texthtml'] == 1 ? 'selected = "selected"' : '')?>>Plain text</option>
		<option value="2"<?=($data['texthtml'] == 2 ? 'selected = "selected"' : '')?>>HTML (plain text version will be created)</option>
	</select></p>
    
    <p><label for="subject">Subject:</label><input type="text" name="subject" id="subject" onkeydown="subjectupdate(this.value);" onkeyup="subjectupdate(this.value);" onchange="subjectupdate(this.value);" value="<?=$subject?>" style="width: 800px;"/></p>
    <p id="debug" style="border: red; background-color: red;"></p>
	
	
    <p class="message" id="format_text" style="display:<?=$config_default_format?>">Type or paste your <strong>HTML</strong> in the message area below:</p>
    <p>
        <label for="editor1">Message:</label>
        <textarea cols="80" id="editor1" name="editor1" rows="10" onchange="messageupdate(this.value);" onkeydown="messageupdate(this.value);" onkeyup="messageupdate(this.value);"><?=$message;?></textarea>
        <? if ($data['texthtml'] == 2) {
            echo
                '<script type="text/javascript">'.
                'CKEDITOR.replace(\'editor1\');'.
                '</script>';
            }
        ?>
    </p>
    <p><span style="float: left;"><input type="submit" value="Review" /></span><span style="float: right;"><input type="button" value="RESET" onclick="<?$Session->set('texthtml', '');$Session->set('subject', '');$Session->set('message', '');?>window.location= '/admin/index.php';"/></span></p>
	
</form>

<script type="text/javascript">
                $('<?php echo urlRequest();?>').request({
                    post : {data : 'membercount'},
                    onSuccess : function($response)
                    {
                            document.getElementById('mc').innerHTML = $response;
                            setTimeout(function()
                            {
                                window.location= '/admin/index.php';
                            }, 100000);
                        }
                    });
                    
                    $('<?php echo urlRequest();?>').request({
                    post : {data : 'subject'},
                    onSuccess : function($response)
                    {
                            document.getElementById('debug').innerHTML = $response;
                            setTimeout(function()
                            {
                                window.location= '/admin/index.php';
                            }, 100000);
                        }
                    });
</script>