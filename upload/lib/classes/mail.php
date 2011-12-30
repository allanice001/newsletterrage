<?php

class Mail
{

	var $smtp_server = '127.0.0.1'; //Username:Password@Servername
	var $smtp_port = 25;
	var $smtp_connection = 0;
	var $smtp_persistent = true;

	var $mail_method = 'smtp';
	var $hostname = 'localhost';

	var $from = 'root@localhost';
	var $from_name = 'root';
	var $priority = 3; //1: High, 3: Normal, 5: Low
	var $subject = '';
	var $body = '';
	var $alt_body = '';
	var $html = true;
	var $embed_inline_content = true;
	var $read_receipt = false;

	var $recipients = array();
	var $attachments = array();

	var $debug = false;

	function Mail()
	{
		$this->clear();
	}

	function addRecipient($address, $name = '', $type = 'to')
	{
		$valid_address = $this->isValidAddress($address);
		if ($valid_address) {
			$type = strtolower($type);
			if (isset($this->recipients[$type])) {
				$this->recipients[$type][$address] = array($address, $name);
			}
		}
		return $valid_address;
	}

	function addAttachment($filename, $inline = false, $content_type = '')
	{
		$disposition = $inline ? 'inline' : 'attachment';
		if (!isset($this->attachments[$disposition])) {
			$this->attachments[$disposition] = array();
		}
		$attachments = &$this->attachments[$disposition];
		if ($inline) {
			$cid = $inline ? md5(uniqid(time())) : '';
			if (isset($attachments[$filename])) {
				$cid = $attachments[$filename]['cid'];
			} else {
				$attachments[$filename] = array(
					'file' => $filename,
					'name' => basename($filename),
					'mime' => ($content_type ? $content_type : $this->getMimeType($filename)),
					'cid' => $cid
				);
			}
			return $cid;
		} else {
			$attachments[] = array(
				'file' => $filename,
				'name' => basename($filename),
				'mime' => ($content_type ? $content_type : $this->getMimeType($filename)),
				'cid' => ''
			);
		}
	}

	function send()
	{
		$time_zone = (date('Z') / 3600) * 100 + (date('Z') % 3600) / 60;
		$message_id = md5(uniqid(time()));
		$message_boundary1 = 'b1_' . $message_id;
		$message_boundary2 = 'b2_' . $message_id;

		if ($this->embed_inline_content) {
			$this->embedInlineContent();
		}

		$mail_head = '';
		$mail_body = '';

		$mail_head .=
			'Date: ' . sprintf('%s %s%04d', date('D, j M Y H:i:s'), ($time_zone < 0 ? '-' : '+'), abs($time_zone)) . "\n" .
			'Return-Path: ' . (false ? $this->from : $this->from) . "\n" .
			($this->mail_method != 'mail' ?
				(count($this->recipients['to']) ? $this->addressAppend('To', $this->recipients['to']) : (!count($this->recipients['cc']) ? "To: undisclosed-recipients:;\n" : '')) .
				(count($this->recipients['cc']) ? $this->addressAppend('Cc', $this->recipients['cc']) : '') : '') .
			$this->addressAppend('From', array(array($this->from, $this->from_name))) .
			($this->mail_method != 'mail' ? "Subject: $this->subject\n" : '') .
			"Message-ID: <$message_id@$this->hostname>\n" .
			"X-Priority: $this->priority\n" .
			"X-Mailer: Thraddash Software Mailer [1.0.1]\n" .
			($this->read_receipt ? "Disposition-Notification-To:<$this->from>\n" : '') .
			"MIME-Version: 1.0\n";

		if (!count($this->attachments)) {

			if (!$this->alt_body) {
				$mail_head .=
					"Content-Transfer-Encoding: 8bit\n" .
					'Content-Type: ' . ($this->html ? 'text/html' : 'text/plain') . '; charset="iso-8859-1"' . "\n";
				$mail_body .=
					$this->encodeString($this->body, '8bit');
			} else {
                $mail_head .=
					"Content-Type: multipart/alternative;\n" .
					"\t" . 'boundary="' . $message_boundary1 . '"' . "\n";

				$mail_body .=
					"\n--$message_boundary1\n" .
					'Content-Type: text/plain; charset="iso-8859-1"' . "\n" .
					"Content-Transfer-Encoding: 8bit\n\n" .
					$this->encodeString($this->alt_body, '8bit') .
					"\n--$message_boundary1\n" .
					'Content-Type: text/html; charset="iso-8859-1"' . "\n" .
					"Content-Transfer-Encoding: 8bit\n\n" .
					$this->encodeString($this->body, '8bit') .
					"\n--$message_boundary1--\n";
			}

		} else {

			if (!isset($this->attachments['inline'])) {
				$mail_head .=
					"Content-Type: multipart/mixed;\n" .
					"\t" . 'boundary="' . $message_boundary1 . '"' . "\n";
			} else {
				$mail_head .=
					"Content-Type: multipart/related;\n" .
					"\t" . 'type="text/html";' . "\n" .
					"\t" . 'boundary="' . $message_boundary1 . '"' . "\n";
			}

			if (!$this->alt_body) {
				$mail_body .=
					"\n--$message_boundary1\n" .
					'Content-Type: ' . ($this->html ? 'text/html' : 'text/plain') . '; charset="iso-8859-1"' . "\n" .
					"Content-Transfer-Encoding: 8bit\n\n" .
					$this->encodeString($this->body, '8bit');
			} else {
				$mail_body .=
					"\n--$message_boundary1\n" .
					"Content-Type: multipart/alternative;\n" .
					"\t" . 'boundary="' . $message_boundary2 . '"' . "\n" .
					"\n--$message_boundary2\n" .
					'Content-Type: text/plain; charset="iso-8859-1"' . "\n" .
					"Content-Transfer-Encoding: 8bit\n\n" .
					$this->encodeString($this->alt_body, '8bit') .
					"\n--$message_boundary2\n" .
					'Content-Type: text/html; charset="iso-8859-1"' . "\n" .
					"Content-Transfer-Encoding: 8bit\n\n" .
					$this->encodeString($this->body, '8bit') .
					"\n--$message_boundary2--\n";
			}

			foreach ($this->attachments as $type => $attachments) {
				foreach ($attachments as $attachment) {
					$file = fopen($attachment['file'], "rb");
					$mail_body .=
						"\n--$message_boundary1\n" .
						'Content-Type: ' . $attachment['mime'] . '; name="' . $attachment['name'] . '"' . "\n" .
						"Content-Transfer-Encoding: base64\n" .
						($attachment['cid'] ? 'Content-ID: <' . $attachment['cid'] . '>' . "\n" : '') .
						'Content-Disposition: ' . $type . '; filename="' . $attachment['name'] . '"' . "\n\n" .
						$this->encodeString(fread($file, filesize($attachment['file'])), 'base64') . "\n";
					fclose($file);
				}
			}
			$mail_body .= "\n--$message_boundary1--\n";
		}

		if ($this->mail_method != 'mail') {$mail_head .= "\n";}

		if ($this->debug) {
			echo $mail_head . "\n";
			echo $mail_body . "\n";
		}

		switch ($this->mail_method) {
			case 'smtp':

				$smtp_server = $this->smtp_server;
				$smtp_username = '';
				$smtp_password = '';

				$pos = strrpos($smtp_server, '@');
				if ($pos) {
					$smtp_username = substr($smtp_server, 0, $pos);
					$smtp_server = substr($smtp_server, $pos + 1);
					$pos = strpos($smtp_username, ':');
					if ($pos) {
						$smtp_password = substr($smtp_username, $pos + 1);
						$smtp_username = substr($smtp_username, 0, $pos);
					}
				}

				if (!is_resource($this->smtp_connection)) {$this->smtp_connection = fsockopen($smtp_server, $this->smtp_port, $error_num, $error_msg, 10);}
				if (is_resource($this->smtp_connection)) {
					$this->getSmtpData();
	
					$this->sendSmtpData('RSET');
					if ($this->sendSmtpData('EHLO ' . $this->hostname) != 250) {$this->sendSmtpData('HELO ' . $this->hostname);}
	
					if ($smtp_username || $smtp_password) {
						$this->sendSmtpData('AUTH LOGIN');
						$this->sendSmtpData(base64_encode($smtp_username));
						$this->sendSmtpData(base64_encode($smtp_password));
					}
	
					$this->sendSmtpData('MAIL FROM:<' . $this->from . '>');
					foreach ($this->recipients as $recipients) {
						foreach ($recipients as $recipient) {
							$this->sendSmtpData('RCPT TO:<' . $recipient[0] . '>') . "\n";
						}
					}
					$this->sendSmtpData('DATA', false);
					$data = explode("\n", $this->encodeString($mail_head . $mail_body, '8bit'));
					foreach ($data as $value) {
						$this->sendSmtpData($value, false);
					}
					$this->sendSmtpData('', false);
					$this->sendSmtpData('.');
					if (!$this->smtp_persistent) {
						$this->close();
					}
					
					return true;

				} else {

					return false;

				}
				break;
		}

	}

	function close()
	{
		if ($this->smtp_connection) {
			fclose($this->smtp_connection);
			$this->smtp_connection = 0;
		}
	}

	function clear()
	{
		$this->body = '';
		$this->alt_body = '';
		$this->subject = '';
		$this->priority = 3;
		$this->clearAttachments();
		$this->clearRecipients();
	}

	function clearAttachments()
	{
		$this->attachments = array();
	}

	function clearRecipients()
	{
		$this->recipients = array(
			'to' => array(),
			'cc' => array(),
			'bcc' => array()
		);
	}

	function isValidAddress($address)
	{
		return preg_match('/\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i', $address);
	}

	/////////////////////////////////////////////////
	// TEXT FORMATING FUNCTIONS
	/////////////////////////////////////////////////

	/**
	 * @access private
	 * @return string
	 */
	function getMimeType($filename)
	{
		$ext = strtolower(trim($filename));
		$pos = strrpos($ext, '.');
		if ($pos) {$ext = substr($ext, $pos + 1);}
		
		switch ($ext) {
			case 'gif':
				return 'image/gif';
				break;
			case 'jpeg':
			case 'jpg':
			case 'jpe':
				return 'image/jpeg';
				break;
			case 'png':
				return 'image/x-png';
				break;
			default:
				return 'application/octet-stream';
				break;
		}
	}

	/**
	 * @access private
	 * @return string
	 */
	function sendSmtpData($data, $get_response = true)
	{
		if ($this->debug) {echo $data . "\n";}
		fputs($this->smtp_connection, $data . "\r\n");
		return $get_response ? substr($this->getSmtpData(), 0, 3) : '';
	}

	/**
	 * @access private
	 * @return string
	 */
	function getSmtpData()
	{
		$data = '';
		while($str = fgets($this->smtp_connection, 515)) {
			if ($this->debug) {echo '<span style="color: #f00;">' . $str . '</span>' . "\n";}
			$data .= $str;
			if(substr($str, 3, 1) == ' ') {
				break;
			}
		}
		return $data;
	}

	/**
	 * @access private
	 * @return string
	 */
	function encodeString($str, $encoding)
	{
		$result = '';
		switch ($encoding) {
			case 'base64':
				$result = chunk_split(base64_encode($str), 76, "\n");
				break;
			case '8bit':
		        $result = str_replace("\r", "\n", str_replace("\r\n", "\n", $str));
				if (substr($result, -(strlen("\n"))) != "\n") {
					$result .= "\n";
				}
				break;
		}
		return $result;
	}

	/**
	 * @access private
	 * @return string
	 */
	function addressAppend($type, $addresses)
	{
		if ($addresses) {
			$result = array();
			foreach ($addresses as $address) {
				$result[] = $address[1] ? $address[1] . ' <' . $address[0] . '>' : $address[0];
			}
			return $type . ': ' . implode(', ', $result) . "\n";
		}
	}

	/**
	 * @access private
	 */
	function embedInlineContent()
	{

		$html = $this->body;

		$tags = array(
			array('<img', '>', 'src="', '"')
		);

		foreach ($tags as $tag_info) {
			$result = '';
			while (strlen($html)) {
	
				$pos1 = strpos(strtolower($html), $tag_info[0]);
				$pos2 = ($pos1 === false) ? 0 : strpos($html, $tag_info[1], $pos1 + strlen($tag_info[0]));
				
				if ($pos2) {
					$pos2++;
					$result .= substr($html, 0, $pos1);
					$tag = substr($html, $pos1, $pos2 - $pos1);
					$html = substr($html, $pos2);
	
					$pos1 = strpos(strtolower($tag), $tag_info[2]);
					$pos2 = ($pos1 === false) ? 0 : strpos($tag, $tag_info[3], $pos1 + strlen($tag_info[2]));
	
					if ($pos2) {
						$pos1 += strlen($tag_info[2]);
						$tag_part = array(
							substr($tag, 0, $pos1),
							trim(substr($tag, $pos1, $pos2 - $pos1)),
							substr($tag, $pos2)
						);
						$tag_part[1] = is_file($tag_part[1]) ? 'cid:' . $this->addAttachment($tag_part[1], true) : $tag_part[1];
						$tag = implode('', $tag_part);
					}
					$result .= $tag;
				} else {
					$result .= $html;
					break;
				}
			}
			$html = $result;
		}
		$this->body = $html;
	}

}

	$GLOBALS['Mail'] = new Mail();

