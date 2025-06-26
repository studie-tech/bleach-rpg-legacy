<?php
class PM{
    private $output_buffer;
    
    public function PM(){
        if(!isset($_GET['act'])){
            $this->inbox_screen();
        }
        elseif($_GET['act'] == 'newpm'){
            if(!isset($_POST['Submit'])){
                $this->send_PM_form();
            }
            else{
                $this->send_PM_act();   
            }
        }
        elseif($_GET['act'] == 'read' && isset($_GET['pmid'])){
            $this->show_message($_GET['pmid']);
        }
        elseif($_GET['act'] == 'delete' && isset($_GET['pmid'])){
            $this->delete_do();
        }
        elseif($_GET['act'] == 'clear'){
            $this->clear_do();
        }
        $this->return_stream();
    }
    
    //       Return stream to core
    private function return_stream(){
        $GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
    }
    
    //      Show the inbox
    private function inbox_screen(){
        $msgcount = $GLOBALS['database']->fetch_data("SELECT COUNT(`time`) AS `pm_count` FROM `users_pm` WHERE `reciever` = '".$_SESSION['username']."'");
        if($_SESSION['user_rank'] != 'Member'){$PMmax = 100;}else{$PMmax = 50;}
        $perc = ($msgcount[0]['pm_count'] / $PMmax) * 100;
        if($perc < 25){
            $perc = '<span style="color:darkgreen;font-weight:bold;">'.$perc.'</span>';
        }
        elseif($perc < 50){
            $perc = '<span style="color:darkblue;font-weight:bold;">'.$perc.'</span>';
        }
        elseif($perc < 75){
            $perc = '<span style="color:orange;font-weight:bold;">'.$perc.'</span>';
        }
        elseif($perc <= 100){
           $perc = '<span style="color:darkred;font-weight:bold;">'.$perc.'</span>';
        }
        $this->output_buffer .= '<div align="center">
                  <table width="95%" border="0" cellspacing="0" cellpadding="0" class="table">
                    <tr>
                      <td colspan="2" align="center" class="subHeader" style="border-top:none;" >PM system</td>
                    </tr>
                    <tr>
                      <td align="center" width="50%"><a href="?id='.$_GET['id'].'&act=newpm">New PM</a></td>
                      <td align="center" width="50%"><a href="?id='.$_GET['id'].'&act=clear">Clear inbox</a></td>
                    </tr>
                    <tr>
                        <td align="center" colspan="2">Your inbox is currently '.$perc.'% full ('.$msgcount[0]['pm_count'].' / '.$PMmax.')</td>
                    </tr>
                    <tr>
                      <td colspan="2" align="center" class="subHeader">Inbox</td>
                    </tr>
                    <tr>
                      <td colspan="2" align="center">';
        $messages = $GLOBALS['database']->fetch_data("SELECT `users_pm`.* FROM `users_pm` WHERE `reciever` = '".$_SESSION['username']."' ORDER BY `time` DESC");
        if($messages != '0 rows'){
            $this->output_buffer .= '<table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                          <td width="44%" style="padding-left:5px;border-bottom:1px solid #000000;">Subject</td>
                          <td width="15%" style="border-bottom:1px solid #000000;" align="center">Sender</td>
                          <td width="20%" style="border-bottom:1px solid #000000;">&nbsp;</td>
                          <td width="21%" style="border-bottom:1px solid #000000;">&nbsp;</td>
                        </tr>';
            $i = 0;
            while($i < count($messages)){
                $pre = null;
                $post = null;   

                if($messages[$i]['read'] == 'no'){
                    $this->output_buffer .= '<tr class="row'.(($i % 2) + 1).'">
                          <td style="padding-left:5px;color:darkred;font-weight:bold;">'.$messages[$i]['subject'].'</td>
                          <td align="center" style="font-weight:bold;">'.$pre.$messages[$i]['sender'].$post.'</td>
                          <td align="center"><a href="?id='.$_GET['id'].'&act=read&pmid='.$messages[$i]['sender'].':'.$messages[$i]['time'].'">Read</a></td>
                          <td align="center"><a href="?id='.$_GET['id'].'&act=delete&pmid='.$messages[$i]['sender'].':'.$messages[$i]['time'].'">Delete</a></td>
                         </tr>';
                }
                else{
                    $this->output_buffer .= '<tr class="row'.(($i % 2) + 1).'">
                          <td style="padding-left:5px;">'.$messages[$i]['subject'].'</td>
                          <td align="center">'.$pre.$messages[$i]['sender'].$post.'</td>
                          <td align="center"><a href="?id='.$_GET['id'].'&act=read&pmid='.$messages[$i]['sender'].':'.$messages[$i]['time'].'">Read</a></td>
                          <td align="center"><a href="?id='.$_GET['id'].'&act=delete&pmid='.$messages[$i]['sender'].':'.$messages[$i]['time'].'">Delete</a></td>
                         </tr>';
                }
                $i++;
            }
            $this->output_buffer .= '</table>';
        }
        else{
            $this->output_buffer .= '<div align="center" style="color:darkred;">You do not have any PM\'s</div>';   
        }
        $this->output_buffer .= '</td></tr></table></div>';
    }   

    //      Show the message
    private function show_message($msgID){
        $temp = explode(':',$msgID);
        $message = $GLOBALS['database']->fetch_data("SELECT `users_pm`.* FROM `users_pm` WHERE `sender` = '".$temp[0]."' AND `time` = '".$temp[1]."' AND `reciever` = '".$_SESSION['username']."' LIMIT 1");
        if($message != '0 rows'){
            $pre = null;
            $post = null;   
        
            //      Echo the message
            $this->output_buffer .= '<div align="center">
                  <table width="95%" border="0" cellspacing="0" cellpadding="0" class="table">
                    <tr>
                      <td colspan="2" align="center" class="subHeader" style="border-top:none;" >PM system</td>
                    </tr>
                    <tr>
                      <td align="center" width="50%"><a href="?id='.$_GET['id'].'&act=newpm&pmid='.$msgID.'">Reply</a></td>
                      <td align="center" width="50%"><a href="?id='.$_GET['id'].'&act=delete&pmid='.$msgID.'">Delete message</a></td>
                    </tr>
                    <tr>
                      <td colspan="2" align="center" class="subHeader">Read message</td>
                    </tr>
                    <tr>
                      <td colspan="2" align="center"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                          <td width="15%" style="padding-left:5px;">Sender:</td>
                          <td width="85%">'.$pre.$message[0]['sender'].$post.'</td>
                        </tr>
                        <tr>
                          <td style="padding-left:5px;">Sent at:</td>
                          <td>'.date('d-m-Y G:i:s',$message[0]['time']).' ('.(functions::convert_PM_time(time() - $message[0]['time'])).')</td>
                        </tr>
                        <tr>
                          <td style="padding-left:5px;">Subject:</td>
                          <td>'.functions::parse_BB($message[0]['subject']).'</td>
                        </tr>
                        <tr>
                          <td colspan="2" align="center" class="subHeader">Message</td>
                        </tr>
                        <tr>
                          <td colspan="2" style="padding:10px;">';
                          
						  if($message[0]['sender'] == "Pwner System"||$message[0]['sender'] == "N00b System"){
						      $this->output_buffer .= $message[0]['message'];
						  }
						  else{
						   	  $this->output_buffer .= functions::parse_BB($message[0]['message']);
						  }
			
			$this->output_buffer .= '</td>
                        </tr>
                        <tr>
                          <td colspan="2" style="padding-left:5px;">&nbsp;</td>
                        </tr>
                        <tr>
                          <td colspan="2" align="center" style="padding-bottom:5px;"><a href="?id=15&act=pm&pmid='.$message[0]['time'].'&uid='.$message[0]['sender'].'"><img src="./images/report.gif" style="border:none;" /></a></td>
                        </tr>
                      </table></td>
                    </tr>
                  </table>
                </div>';
            //      Set the message to unread!
            if($message[0]['read'] == 'no'){
                $GLOBALS['database']->execute_query("UPDATE `users_pm` SET `read` = 'yes' WHERE `sender` = '".$temp[0]."' AND `time` = '".$temp[1]."' AND `reciever` = '".$_SESSION['username']."' LIMIT 1 ");
            }
        }
        else{
            $this->output_buffer .= '<div align="center" style="color:darkred;">The message could not be loaded for one of the following reasons:<br />
            - It does not exist.<br />
            - It does not belong to you.<br />
            <br /><a href="?id='.$_GET['id'].'">Return</a></div>';   
        }
    }

    //      Send new PM form
    private function send_PM_form(){
        if(isset($_GET['pmid'])){
            $temp = explode(':',$_GET['pmid']);
            $replyMSG = $GLOBALS['database']->fetch_data("SELECT * FROM `users_pm` WHERE `sender` = '".$temp[0]."' AND `time` = '".$temp[1]."' AND `reciever` = '".$_SESSION['username']."' LIMIT 1");
            if($replyMSG != '0 rows'){
                if(!stristr($replyMSG[0]['subject'],'RE:')){
                    $subject = 'RE: '.$replyMSG[0]['subject'];   
                }
                else{
                    $subject = $replyMSG[0]['subject'];
                }
                $recipient = $replyMSG[0]['sender'];
            }
            else{
                $quotemsg = 'Insert message here';
                $recipient = '';
                $subject ='';
            }
        }
        elseif(isset($_GET['user'])){
            $quotemsg = 'Insert message here';
            $recipient = $_GET['user'];
            $subject = '';
        }
        else{
            $quotemsg = 'Insert message here';   
            $recipient = '';
            $subject ='';
        }
        $this->output_buffer .= '<div align="center"><form name="form1" method="post" action="">
                  <table width="95%" border="0" cellspacing="0" cellpadding="0" class="table">
                    <tr>
                      <td colspan="2" align="center" class="subHeader" style="border-top:none;" >Send PM</td>
                    </tr>
                    <tr>
                      <td width="32%" align="left" style="padding:5px;">Send to:</td>
                      <td width="68%" align="left" style="padding:5px;"><input name="recipient" value="'.$recipient.'" type="text" size="20"></td>
                    </tr>
                    <tr>
                      <td align="left" style="padding:5px;">Subject:</td>
                      <td align="left" style="padding:5px;"><input name="subject" value="'.$subject.'" type="text" size="40" maxlength="150"></td>
                    </tr>
                    <tr>
                      <td colspan="2" align="center" class="subHeader">Message:</td>
                    </tr>
                    <tr>
                      <td colspan="2" align="center" style="padding:2px;"><textarea name="message" rows="10" style="width:100%">';
        //            Insert msg quote
        $this->output_buffer .= $quotemsg;
        $this->output_buffer .= '</textarea></td>
                    </tr>
                    <tr>
                      <td colspan="2" align="center" style="padding:5px;"><input type="submit" name="Submit" value="Submit"></td>
                    </tr>
                  </table>
                  </form>
                </div>';
    }
    
    //      Send PM act
    private function send_PM_act(){   
                       
        if($_POST['recipient'] != ''){
            if(functions::store_content($_POST['subject']) != ''){
                //      Do checks
                $recipient = $GLOBALS['database']->fetch_data("SELECT `pm_block`,`id`,`user_rank` FROM `users` WHERE `username` = '".$_POST['recipient']."'");
                if($recipient != '0 rows'){
                    if($recipient[0]['pm_block'] != 1 || ($_SESSION['user_rank'] == 'Admin' || $_SESSION['user_rank'] == 'Moderator' || $_SESSION['user_rank'] == 'Supermod')){
                        $pmdata = $GLOBALS['database']->fetch_data("SELECT COUNT(`time`) as `pm_count` FROM `users_pm` WHERE `reciever` = '".$_SESSION['username']."'");
                        if($_SESSION['user_rank'] != 'Member'){$max = 100;}else{$max = 50;}
                        if($pmdata[0]['pm_count'] < $max || ($_SESSION['user_rank'] == 'Admin' || $_SESSION['user_rank'] == 'Moderator' || $_SESSION['user_rank'] == 'Supermod')){
                            $pmdata = $GLOBALS['database']->fetch_data("SELECT COUNT(`time`) as `pm_count` FROM `users_pm` WHERE `reciever` = '".$_POST['recipient']."'");
                            if($recipient[0]['user_rank'] != 'Member'){$max = 100;}else{$max = 50;}
                            if($pmdata[0]['pm_count'] < $max || ($_SESSION['user_rank'] == 'Admin' || $_SESSION['user_rank'] == 'Moderator' || $_SESSION['user_rank'] == 'Supermod')){
                                if(strlen(functions::store_content($_POST['message'])) > 5 && strlen(functions::store_content($_POST['message'])) < 1000){
                                    $blacklist = $GLOBALS['database']->fetch_data("SELECT * FROM `users_pm_settings` WHERE `userid` = '".$recipient[0]['id']."' LIMIT 1");
                                    if($blacklist !== "0 rows"){          
                                        if(
                                            ($blacklist[0]['setting'] == 'block_black' && !stristr($blacklist[0]['blacklist'],';'.$_SESSION['uid'].';')) || 
                                            ($blacklist[0]['setting'] == 'white_only' && stristr($blacklist[0]['whitelist'],';'.$_SESSION['uid'].';')) || 
                                            ($blacklist[0]['setting'] == 'off') || 
                                            ($_SESSION['user_rank'] == 'Admin' || $_SESSION['user_rank'] == 'Moderator' || $_SESSION['user_rank'] == 'Supermod')
                                        ){
                                            //      SEND PM   
                                            if($GLOBALS['database']->execute_query("INSERT INTO `users_pm` (`sender` ,`reciever` ,`time` ,`message` ,`subject` ,`read`)VALUES ('".$_SESSION['username']."', '".$_POST['recipient']."', '".time()."', '".functions::store_content($_POST['message'])."', '".functions::store_content($_POST['subject'])."', 'no');")){
                                                $this->output_buffer .= '<div align="center" style="color:black;">Your PM has been sent!<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
                                            }
                                            else{
                                                $this->output_buffer .= '<div align="center" style="color:darkred;">An error occured sending your PM!<br /><a href="?id='.$_GET['id'].'">Return</a></div>';        
                                            }
                                        }
                                        else{
                                            $this->output_buffer .= '<div align="center" style="color:darkred;">The PM could not be sent due to the recipients white / blacklist preferences.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
                                        }
                                    }
                                    else{
                                        $this->output_buffer .= '<div align="center" style="color:darkred;">Your account is missing a black-list entry in a table. This issue has now been fixed automatically.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
                                        $GLOBALS['database']->execute_query("INSERT INTO `users_pm_settings` (`userid` ,`whitelist` ,`blacklist` ) VALUES ('".$recipient[0]['id']."', ';', ';');");
                                    }
                                }
                                else{
                                    $this->output_buffer .= '<div align="center" style="color:darkred;">Your message is too short, or too long.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
                                }
                            }
                            else{
                                $this->output_buffer .= '<div align="center" style="color:darkred;">The recipients PM inbox is full<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
                            }
                        }
                        else{
                             $this->output_buffer .= '<div align="center" style="color:darkred;">Your PM inbox is full.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
                        }
                    }
                    else{
                        $this->output_buffer .= '<div align="center" style="color:darkred;">The recipient has chosen not to recieve PM\'s<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
                    }
                }
                else{
                   $this->output_buffer .= '<div align="center" style="color:darkred;">The recipient does not exist<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
                }
            }
            else{
                $this->output_buffer .= '<div align="center" style="color:darkred;">You did not specify a subject<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
            }
        }
        else{
            $this->output_buffer .= '<div align="center" style="color:darkred;">You did not specify a recipient for the PM<br /><a href="?id='.$_GET['id'].'">Return</a></div>';   
        }
    }

    //      Do delete
    private function delete_do(){
        $temp = explode(':',$_GET['pmid']);
        $message = $GLOBALS['database']->fetch_data("SELECT * FROM `users_pm` WHERE `sender` = '".$temp[0]."' AND `time` = '".$temp[1]."' AND `reciever` = '".$_SESSION['username']."' LIMIT 1");
        if($message != '0 rows'){
            $GLOBALS['database']->execute_query("DELETE FROM `users_pm` WHERE `reciever` = '".$_SESSION['username']."' AND `time` = '".$temp[1]."' AND `sender` = '".$temp[0]."' LIMIT 1");
            $this->output_buffer .= '<div align="center">The PM has been deleted<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
        }
        else{
            $this->output_buffer .= '<div align="center" style="color:darkred;">The message could not be deleted for one of the following reasons:<br />
            - It does not exist.<br />
            - It does not belong to you.<br />
            <br /><a href="?id='.$_GET['id'].'">Return</a></div>';   
        }
    }

    //      Do clear
    private function clear_do(){
        $GLOBALS['database']->execute_query("DELETE FROM `users_pm` WHERE `reciever` = '".$_SESSION['username']."' AND ((`time` <= ".(time() - 5)." AND `read` = 'no') OR `read` = 'yes')");
        $this->output_buffer .= '<div align="center">Your inbox has been cleared of messages older than 5 seconds.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
    }
}

$pm = new PM();
?>
