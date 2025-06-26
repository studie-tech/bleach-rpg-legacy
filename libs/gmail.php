<?php
require_once('./libs/phpmailer/class.phpgmailer.php');

class Mail extends PHPGmailer{

    public function __construct(){
        $mail->Username = '';
        $mail->Password = '';
        $mail->From = '';
        $mail->FromName = '';
        $this->AddReplyTo('','');
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
            <td style="background-color:#724B3F;text-align:center;font-weight:bold;color:#FFFFFF;border-bottom:2px solid #000000;">Theninja-RPG</td>
          </tr>
          <tr>
            <td style="text-align:center;">'.$body.'</td>
          </tr>
          <tr>
            <td style="padding:2px;"><a href="http://www.theninja-server2.com" style="color: #000000;font-weight:bold;font-size:11px;">Theninja-RPG</a></td>
          </tr>
        </table>
        </div>
        <div style="font-size:10px;" align="center">Copyright TheNinja-RPG.com &copy; by Studie-Tech ApS 2005/2007 - CVR: 30 61 71 34</div>
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
