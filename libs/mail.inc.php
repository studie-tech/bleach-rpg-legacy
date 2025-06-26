<?php
require_once('./libs/phpmailer/class.phpmailer.php');
class Mail extends PHPMailer{
	
	public function Mail(){
        //$this->isSMTP();
        $this->Host = '';
        $this->SMTPAuth = true;
        $this->Username = '';
        $this->Password = '';
        $this->From = '';
        $this->AddReplyTo('','Bleach-RPG');
		$this->FromName = 'Bleach-RPG';
		$this->Priority = 3;
	}
	
	public function HTMLMail($body){
		$this->isHTML(true);
		$this->AltBody = $body;
		$this->Body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>Theninja-RPG E-mail</title>
		</head>
		<body>
		<div align="center">
		<table width="80%" border="0" cellspacing="0" cellpadding="0" style="border:2px solid #000000;background-color:#F1E0BA;">
  		<tr>
    		<td style="background-color:#333333;text-align:center;font-weight:bold;color:#FFFFFF;border-bottom:2px solid #000000">
            Bleach RPG</td>
  		</tr>
  		<tr>
    		<td style="text-align:center;" bgcolor="#CCCCCC">'.$body.'</td>
  		</tr>
  		<tr>
    		<td style="padding:2px;" bgcolor="#CCCCCC">
            <a href="http://www.theninja-server2.com">
            <span style="color: #000000; font-weight: 700; font-size: 11px">
            Bleach-RPG</span></a></td>
  		</tr>
		</table>
		</div>
		<div style="font-size:10px;" align="center">Copyright Bleach-RPG.com &copy; by Studie-Tech ApS 2005/2007 - CVR: 30 61 71 34</div>
		</div>
		</body>
		</html>';
	}

    public function SupportMail($body){
        $this->FromName = 'Theninja-RPG Contact Form';
        $this->isHTML(true);
        $this->AltBody = $body;
        $this->Body = $body;
    }
}
?>