<?php
/*						Contact.inc.php
 *						 Contact page
 */
class contact{
    private $blacklist_ip = array();
    private $output_buffer;
    
    private function return_stream(){
        $GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);   
    }
    
    public function contact(){
        if(!in_array($_SESSION['remote_addr'],$this->blacklist_ip)){
            if(true){
                if(!isset($_POST['Submit'])){
                    $this->mail_form();   
                }
                else{
                    $this->send_mail();   
                }
            }
            else{
                $this->output_buffer .= '<div align="center">contact page disabled during testing.</div>';
            }
            $this->return_stream();
        }
    }
    
    private function mail_form(){
        if(isset($_SESSION['uid'])){
            $user = $GLOBALS['userdata'];
        }
        $this->output_buffer .= '<div align="center">
                <form name="form1" method="post" action="">
                  <table width="90%" border="0" cellspacing="0" cellpadding="0" class="table">
                    <tr>
                      <td colspan="2" align="center" class="subHeader" style="border-top:none;" >Contact</td>
                    </tr>
                    <tr>
                      <td colspan="2" align="center" style="color:darkred;">Using this form you can contact support with questions and / or problems<br />
                        Replies will be recieved through e-mail.</td>
                    </tr>
                    <tr>
                      <td colspan="2" align="center" class="subHeader">General information</td>
                      </tr>
                    <tr>
                      <td width="50%" align="center" style="padding:3px;">Your account name:</td>
                      <td width="50%" align="center" style="padding:3px;">';
        if(isset($user) && $user != '0 rows'){
            $this->output_buffer .= '<input type="text" name="username" value="'.$user[0]['username'].'" style="width:90%;">';
        }
        else{
            $this->output_buffer .= '<input type="text" name="username" style="width:90%;">';   
        }
        $this->output_buffer .= '</td>
                    </tr>
                    <tr>
                      <td align="center" style="padding:3px;">Your e-mail address:</td>
                      <td align="center" style="padding:3px;">';
        if(isset($user) && $user != '0 rows'){
            $this->output_buffer .= '<input type="text" name="email" value="'.$user[0]['mail'].'" style="width:90%;">';   
        }
        else{
            $this->output_buffer .= '<input type="text" name="email" style="width:90%;">';
        }
        $this->output_buffer .= '</td></tr>
                    <tr>
                      <td align="center" style="padding:3px;">Nature of your problem / inquiry:</td>
                      <td align="center" style="padding:3px;"><select name="type" style="width:90%;" id="select">
                        <option selected>Bug / Exploit</option>
                        <option>Suggestion</option>
                                            </select></td>
                    </tr>
                    <tr>
                      <td colspan="2" align="center" class="subHeader">Description of your problem / Inquiry:</td>
                      </tr>
                    <tr>
                      <td colspan="2" align="center" style="padding:3px;"><textarea name="inquiry" rows="10" style="height:100%;width:100%;" id="textfield"></textarea></td>
                      </tr>
                    <tr>
                      <td align="center" valign="middle" style="padding:3px;"><input type="checkbox" name="checkbox" id="checkbox">
                        This e-mail has been written in readable english</td>
                      <td align="center" valign="middle" style="padding:3px;"><input type="checkbox" name="checkbox2" id="checkbox2"> 
                        No stupidity is evident in this inquiry.</td>
                    </tr>
                    <tr>
                      <td colspan="2" align="center" style="padding:3px;"><input type="submit" name="Submit" value="Submit"></td>
                      </tr>
                    <tr>
                      <td colspan="2" align="center">&nbsp;</td>
                    </tr>

                  </table>              
                  </form>
                  <br />
                </div>';   
    }

    private function send_mail(){
        if(!isset($_SESSION['last_mail']) || $_SESSION['last_mail'] > time() - 60){
            if(isset($_POST['username']) && $_POST['username'] != ''){
                if(isset($_SESSION['uid'])){
                    $user = $GLOBALS['userdata'];   
                    if($user != '0 rows'){
                       $_POST['username'] = $user[0]['username'];
                       $_POST['email'] = $user[0]['mail'];
                    }
                }
                if(isset($_POST['email']) && $_POST['email'] != ''){
                    session_register('last_mail');
                    $_SESSION['last_mail'] = time();
                    if(isset($_POST['inquiry']) && strlen($_POST['inquiry']) > 5){
                        if($_POST['type'] != ''){                        
                            require_once('./libs/mail.inc.php');
                            $mail = new Mail();
                            $mail->Subject = 'TNR: '.$_POST['type'].' '.time().$_SESSION['uid'];
                            $mail->ClearReplyTos();
                            $mail->addReplyTo($_POST['email'],$_POST['username']);
                            $body = "<b>Username:</b> ".$_POST['username']."<br /> <b>Sent from IP:</b> ".$_SERVER['REMOTE_ADDR']."  <br /><b>Inquiry:</b> <br />".nl2br($_POST['inquiry']);
                            $mail->SupportMail($body);
                            $mail->AddAddress('');
                            if($mail->Send()){
                                $this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">Your inquiry has been sent and will be processed as soon as possible.<br />Please refrain from nagging if you feel we dont respond quickly enough<br /><a href="?id=1">Return</a></div>';
                            }
                            else{
                                print_r($mail->errorInfo);
                            }
                        }
                    }
                    else{
                        $this->output_buffer .= '<div align="center">No message was specified.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';    
                    }
                }
                else{
                    $this->output_buffer .= '<div align="center">No return address was set.<br /><a href="?id='.$_GET['id'].'">Return</a></div>'; 
                }
            }
            else{
                $this->output_buffer .= '<div align="center">No username was set.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';   
            }
        }
        else{
            $this->output_buffer .= '<div align="center">You have already sent an inquiry in the past 60 seconds.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';   
        }
    }
}

$contact = new contact();
?>