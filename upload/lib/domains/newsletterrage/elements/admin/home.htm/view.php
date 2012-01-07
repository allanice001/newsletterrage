<script type="text/javascript" src="/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
    
    function format(value) {
        document.getElementById('debug').innerHTML = value;    
    }
    
</script>

<?php
    global $DB, $Session;
    
    //print_pre($Session);
    $today = getdate();
    $draftid = get('draftid', 0);
    $templateid = get('templateid', 0);
    
    $data = array(
        'subject' => '',
        'message' => '',
        'texthtml' => 0,
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
    
    print_pre($data);
    $current_member_count = $DB->count('SELECT address FROM mailinglist_subscribers WHERE confirmed');
    //$current_member_count = mysql_num_rows($DB->lookup('SELECT address FROM mailinglist_subscribers WHERE confirmed'));
    
?>
<script type="text/javascript">
    var i= <?=$data['texthtml']?>;
</script>

<form method="POST" action="<?echo urlPath();?>" name="form1" id="form1"> <!-- onsubmit="return besure('<? //Date("T")?>');"> -->
	<p class="message">If sent now, message will be delivered to <span id="mc"><?=$current_member_count?></span> subscribers. Send as 
	<select size="1" name="format" id="format" onchange="i = this.value;">
    <!-- <select size="1" name="format" onchange="format_text(this.value)"> -->
		<option value="0"<?=($data['texthtml'] == 0 ? 'selected = "selected"' : '')?>>Plain text</option>
		<option value="1"<?=($data['texthtml'] == 1 ? 'selected = "selected"' : '')?>>HTML (plain text version will be created)</option>
	</select></p>
    <p id="debug">
    <script type="text/javascript">
    document.getElementById('debug').innerHTML = i;
    </script>

	<p></p>
	
    <p class="message" id="format_text" style="display:<?=$config_default_format?>">Type or paste your <strong>HTML</strong> in the message area below:</p>
    <p>
        <label for="editor1">Editor 1:</label>
        <textarea cols="80" id="editor1" name="editor1" rows="10">&lt;p&gt;This is some &lt;strong&gt;sample text&lt;/strong&gt;. You are using &lt;a href="http://ckeditor.com/"&gt;CKEditor&lt;/a&gt;.&lt;/p&gt;</textarea>
        <script type="text/javascript">
        CKEDITOR.replace( 'editor1' );
        </script>
    </p>
</form>		