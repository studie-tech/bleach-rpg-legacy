<?php
class register{
    private $blocked_part = array('\'','admin','moderator','sex','pussy','cock','niggah','s3x','c0ck','horny','h0rny','gay','fuck','butthole','butth0le','hentai','aeterno','aoshi');
    private $output_buffer;
    
	public function register(){
		if(!isset($_GET['act'])){
			if(!isset($_POST['Submit'])){
				$this->show_page();
			}
			else{
				$this->check_register();
			}
		}
		elseif($_GET['act'] == 'activate'){
			$this->activate();
		}
		elseif($_GET['act'] == 'forgot'){
			if(isset($_GET['reqID'])){
				$this->recoverForgotPass();
			}
			elseif(!isset($_POST['Submit'])){
				$this->forgotForm();
			}
			elseif(isset($_POST['Submit'])){
				$this->sendForgotMail();
			}
			
		}
        elseif($_GET['act'] == 'resend_activation'){
            if(!isset($_POST['Submit'])){
                $this->resendActivation();
            }
            else{
                $this->doResend();
            }
        }
        elseif($_GET['act'] == 'send_unlock'){
            if(!isset($_POST['Submit'])){
                $this->unlockForm();
            }
            else{
                $this->sendUnlock();
            }   
        }
        elseif($_GET['act'] == 'do_unlock' && !isset($_POST['lgn_usr_stpd'])){
            $this->doUnlock();   
        }
		$this->return_stream();
	}
	
	private function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	private function show_page(){

		$this->output_buffer .= '<div align="center">
  <center>
  <form name="form1" method="post" action="">
  <table cellpadding="0" cellspacing="0" width="90%" class="table">
    <tr>
      <td colspan="2" class="subHeader">
      <p style="text-align: left"> - Register</td>
    </tr>
    <tr>
      <td width="60%" class="td2"><br /><b>Desired username:</b></td>
      <td width="40%"><br /><input name="username" type="text" id="username" size="20"></td>
    </tr>
    <tr>
      <td width="60%" class="td2">Desired race:</td>
      <td width="40%">'.$_GET[race].' <input type="hidden" name="race" value="'.$_GET[race].'"></td>
    </tr>
    <tr>
      <td width="60%" class="td2">Password:</td>
      <td width="40%">Sent to email specified below</td>
    </tr>
    <tr>
      <td width="60%" class="td2"><b>Valid Email:</b></td>
      <td width="40%"><input name="mail" type="text" id="mail" size="20"></td>
    </tr>
    <tr>
      <td width="60%" class="td2"><b>Confirm email:</b></td>
      <td width="40%"><input name="mail_v" type="text" id="mail_v" size="20"></td>
    </tr>
    <tr>
      <td width="60%" class="td2"><b>Gender</b></td>
      <td width="40%"><select name="gender" id="gender">
                      <option>Male</option>
                      <option>Female</option>
                      <option selected>Pick one</option>
                    </select></td>
    </tr>
    <tr>
      <td width="60%" class="td2"><br /><b>Agreements:</b></td>
      <td width="40%"></td>
    </tr>
    <tr>
      <td colspan="2" width="50%" class="td2">
      <input name="terms" type="checkbox" id="terms" value="1"> I hereby agree that I have read the <a href="?id=10">Terms of Service</a> and that I will act 
      in accordance to these terms.</td>
    </tr>
    <tr>
      <td colspan="2" width="50%" class="td2">
      <input name="rules" type="checkbox" id="rules" value="1"> I hereby agree that I have read the <a href="?id=9">Rules</a> and that I will act in 
      accordance to these</td>
    </tr>
    <tr>
      <td colspan="2" width="50%" class="td2">
      <input name="altlimit" type="checkbox" id="altlimit" value="1">This is my <b>ONLY</b> account on this game<br /><br /></td>
    </tr>
    <tr>
      <td colspan="2" class="subHeader">
      <p style="text-align: left"> - Important Notice</td>

    </tr>
    <tr>
      <td colspan="2" width="50%">
      <br /><center><i>"This game is as stated in the title a sub-project of theninja-rpg. Extensive user support is not provided by the staff of this game.  Feel free to report bugs you experience in the game."</i></center><br /></td>
    </tr>
  </table>
  <br /><input type="submit" name="Submit" value="Submit">
  </FORM>
  </center>
</div>';
	}

	private function check_register(){
		if($_POST['rules'] == 1){
			if($_POST['terms'] == 1){
			 if($_POST['altlimit'] == 1){
				if(trim($_POST['username']) != '' && strlen($_POST['username']) >= 4 && strlen($_POST['username']) <= 20){
                    if(ereg("^[A-Za-z0-9_]+_?\$",trim($_POST['username']))){
                        $_POST['username'] = trim($_POST['username']);
                        $continue = true;
                        foreach($this->blocked_part as $string){
                            if(stristr($_POST['username'],$string)){
                                $continue = false;
                                break;   
                            }
                        }
                        if($continue){
					        if(ereg('[-a-zA-Z0-9_.+]+@[a-zA-Z0-9-]+.[a-zA-Z]+', $_POST['mail']) && $_POST['mail'] != '' && $_POST['mail'] == $_POST['mail_v']){

                                $proceed = true;
                                if($proceed == true){
						            if($_POST['gender'] != 'Pick one' && ($_POST['gender'] == 'Male' || $_POST['gender'] == 'Female')){
							            					
										            $users = $GLOBALS['database']->fetch_data("SELECT `username` FROM `users` WHERE `username` LIKE '".$_POST['username']."' LIMIT 1");
										            if($users == '0 rows'){
                                                        $ip_check = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) AS `count` FROM `users` WHERE `join_ip` = '".$_SESSION['REMOTE_ADDR']."'");
                                                        if($ip_check[0]['count'] < 2){
											                $mail = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) AS `count` FROM `users` WHERE `mail` = '".$_POST['mail']."'");
                                                            if($mail[0]['count'] == 0){
                                                                $this->do_register();
                                                            }
                                                            else{
                                                                $this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">Your e-mail is already in use.</div>';
                                                                $this->show_page();
                                                            }
                                                        }
                                                        else{
                                                            $this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">Two accounts already exist on this IP.</div>';
                                                            $this->show_page();
                                                        }
										            }
										            else{
                                                        $this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">This username already exists</div>';
											            $this->show_page();
										            }


						            }
						            else{
							            $this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">You did not pick a valid gender</div>';
							            $this->show_page();
						            }
                                }
                                else{
                                    $this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">Your desired username is too similar to that of a staff member (less than 3 different characters).</div>';
                                    $this->show_page();
                                }
					        }
					        else{
						        $this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">The e-mails you specified do not match, or you did not enter a valid e-mail</div>';
						        $this->show_page();
					        }
                        }
                        else{
                            $this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">Your desired username contains blocked words</div>';
                            $this->show_page();
                        }
                    }
                    else{
                        $this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">Your desired username contains illegal characters</div>';
                            $this->show_page();
                    }
				}
				else{
					$this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">Your desired username is not between 4 and 20 characters long</div>';
					$this->show_page();
				}
				}
			else{
				$this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">You are only allowed to have one account!</div>';
				$this->show_page();
			}
			}
			else{
				$this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">You did not agree to the terms and conditions</div>';
				$this->show_page();
			}
		}
		else{
			$this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">You did not agree to the rules</div>';
			$this->show_page();
		}
	}

	private function do_register(){
		$race_data = $GLOBALS['database']->fetch_data("SELECT * FROM `races` WHERE `name` = '".$_POST['race']."' LIMIT 1");
		if($race_data != '0 rows'){
		    if($_POST['race'] == "Hollow"){
			    $userrank = "Minion";
		    }else{
			    $userrank = "Student";
		    }
		 $totalChar = 5; // number of chars in the password
		 $salt = "abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789";  // salt to select chars from
		 srand((double)microtime()*1000000); // start the random generator
		 $password=""; // set the inital variable
		 for ($i=0;$i<$totalChar;$i++)  // loop and create password
		 $password = $password . substr ($salt, rand() % strlen($salt), 1);
			$longitude = 1; $latitude = 1;
			if($GLOBALS['database']->execute_query("INSERT INTO `users` (
`id` ,
`username` ,
`password` ,
`mail` ,
`join_date` ,
`join_ip` ,
`user_rank` ,
`layout` ,
`gender` ,
`post_ban` ,
`rank` ,
`rankid` ,
`level` ,
`abillity` ,
`race` ,
`status` ,
`experience` ,
`regen_rate` ,
`bank` ,
`system_warning` ,
`healed` ,
`pm_block` ,
`squad` ,
`intelligence` ,
`speed` ,
`strength` ,
`cur_health` ,
`cur_rei` ,
`max_health` ,
`max_rei` ,
`rep_now` ,
`lock` ,
`lock_count` ,
`activation` ,
`login_id` ,
`activity` ,
`shikai` ,
`bankai` ,
`sword` ,
`location`
)
VALUES (
NULL , '".$_POST['username']."', '".md5($password)."', '".$_POST['mail']."', '".time()."', '".$_SERVER['REMOTE_ADDR']."', 'Member', 'default', '".$_POST['gender']."', '0', '".$userrank."', '".$race_data[0]['rankid']."', '1', NULL , '".$race_data[0]['name']."', 'awake', '0', '10', '100', '', '', '0', '', '10.00', '10.00', '10.00', '100.00', '100.00', '100.00', '100.00', '0', '0', '0', '0', '', '' , '', '', '', '".$longitude.":".$latitude."'
);")){
				$id = $GLOBALS['database']->get_inserted_id();
					if($GLOBALS['database']->execute_query("INSERT INTO `users_timer` (`userid`) VALUES ('".$id."')")){
                        if($GLOBALS['database']->execute_query("INSERT INTO `users_pm_settings` (`userid` ,`whitelist` ,`blacklist`)VALUES ('".$id."', ';', ';');")){
                            if($GLOBALS['database']->execute_query("INSERT INTO `users_events` (`userid`) VALUES ('".$id."')")){
                                if($GLOBALS['database']->execute_query("INSERT INTO `battle_options` (`uid`) VALUES ('".$id."')")){
                                	if($_GET['ref']){
                                	 	$this->ref = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `username` = '".$_GET['ref']."' LIMIT 1");
										if($this->ref[0]['join_ip'] !== $_SERVER['REMOTE_ADDR']){
											$GLOBALS['database']->execute_query("INSERT INTO `referals` (`uid`,`referrer`) VALUES ('".$id."','".$_GET['ref']."')");
										}
									}

                                    //	Send verification e-mail:
							        require_once('./libs/mail.inc.php');
							        $mail = new Mail();
							        $mail->Subject = 'Bleach-RPG Account';
							        $mail->HTMLMail('<b>Welcome to bleach-game.com!</b></br>
							        <br />Thank you for you registration. Click on the following link to activate your account. This confirmation of your email is made to protect your identity and to ensure your safety.<br>
                                    </br>
							        <a href="http://www.bleach-game.com/?id=2&act=activate&user='.$_POST['username'].'&code='.md5($_POST['mail'].'confirm').'">Important! Click here to activate your account.</a>
                                    <br>
							        <br />If you are unable to click the activation link above, you can enter this URL directly into the browser:</br><br />
							        http://www.bleach-game.com/?id=2&act=activate&user='.$_POST['username'].'&code='.md5($_POST['mail'].'confirm').'
                                    <br>
							        <br /><center>
							        Your user ID     : '.$_POST['username'].'<br />
							        Your password : '.$password.'
							        </center>
							        <br /><b>Terms:</b> By activating this account, you agree to follow our terms of service and rules.
							        <b>Not your account?:</b> You have recieved this email because an account was created where this email was used. If you didnt recently create this account please 
                                    ignore this email.<br />');
							        $mail->AddAddress($_POST['mail']);
							    if($mail->Send()){
							      	$this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">Your account has been created, please check your e-mail for the verification mail.<br /><br />We can\'t send mails to <b>@yahoo.com</b> and <b>@aim.com</b> emails.<br /> If you have such a mail and did not receive a verification email, <br />then please register a <a href="http://www.gmail.com">gmail</a> account and try registering here again</div>';
						  		}
						        else{
                                    echo"Error Sending mail";
								        print_r($mail->errorInfo);
				  		        }
				  		    }
                        }
                    }
				}
			}
		}
		else{
			$this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">Wow, you\'re so smart trying to trick the game by altering the GET variables. Go away! </div>';
		}
	}
	
	private function activate(){
		if(isset($_GET['code']) && isset($_GET['user'])){
			$userdata = $GLOBALS['database']->fetch_data("SELECT `mail`,`activation` FROM `users` WHERE `username` = '".$_GET['user']."' LIMIT 1");
			if($userdata != '0 rows'){
				if($userdata[0]['activation'] == 0){
					if(md5($userdata[0]['mail'].'confirm') == $_GET['code']){
						if($GLOBALS['database']->execute_query("UPDATE `users` SET `activation` = '1' WHERE `username` = '".$_GET['user']."' LIMIT 1")){
							$this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">Your account has now been activated, you can now log in.</div>';
						}
						else{
							
						}
					}
					else{
						$this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">This activation code is not valid, please follow the link specified in the mail<br /> if problems persist contact support.</div>';
					}
				}
				else{
					$this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">This account has already been activated.</div>';
				}
			}
			else{
				$this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">The user could not be found, please follow the link specified in the mail<br /> if problems persist contact support.</div>';
			}
		}
		else{
			$this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">No user or activation code were set, please follow the link specified in the e-mail.</div>';
		}
	}
	
	private function forgotForm(){
		$GLOBALS['database']->execute_query("DELETE FROM `pass_request` WHERE `time` <= '".(time() - 1800)."'");
		$this->output_buffer .= '<div align="center">
				<form name="form1" method="post" action="">
				  <table width="90%" border="0" cellspacing="0" cellpadding="0" class="table">
                    <tr>
                      <td align="center" style="font-size:16px;border-top:none;" class="subHeader">Recover password </td>
                    </tr>
                    <tr>
                      <td style="text-align:center;padding:5px;">This feature allows you to recover your password, granted that the e-mail you specified when you created the character is still in use. </td>
                    </tr>
                    <tr>
                      <td style="text-align:center;">E-mail address: 
                      <input name="email" type="text" id="username">&nbsp;
                      <input type="submit" name="Submit" value="Submit"></td>
                    </tr>
                    <tr>
                      <td style="text-align:center;">&nbsp;</td>
                    </tr>
                  </table>
				</form>
				</div>';
	}
	
	private function sendForgotMail(){
		$user = $GLOBALS['database']->fetch_data("SELECT `mail`,`activation`,`username` FROM `users` WHERE `mail` = '".$_POST['email']."' LIMIT 1");
		if($user != '0 rows'){
			$auth_code = md5($user[0]['mail'].'_'.$user[0]['username']);
			if($GLOBALS['database']->execute_query("INSERT INTO `pass_request` ( `time` , `username` , `auth_code` , `mail_addr` , `IP` )VALUES ('".time()."', '".$user[0]['username']."', '".$auth_code."', '".$user[0]['mail']."', '".$_SERVER['REMOTE_ADDR']."')")){
				//	Send Recover e-mail
				require_once('./libs/mail.inc.php');
				$mail = new Mail();
				$mail->Subject = 'Bleach-game account recovery';
				$mail->HTMLMail('<p>Confirm password reset:</p>
                      <p>Someone on the following ip: '.$_SERVER['REMOTE_ADDR'].' has requested a password reset for your account: '.$user[0]['username'].'<br>
                        To verify this request please press <a href="http://www.bleach-game.com/?id=2&act=forgot&reqID='.$auth_code.'">this link</a> or copy paste the following URL in your browser<br>
                      http://www.bleach-game.com/?id=2&act=forgot&reqID='.$auth_code.'</p>
                      <p>Your new password will be sent to this e-mail address afterwards.</p>
                      <p><strong>Did you not request a password?<br>
                      </strong>If you did not request a new password for this account please disregard this message.<br>
                      The request will automatically time out after 30 minutes without adversely affeting your account.<br>
                      Should these e-mails occur frequently, please contact support allong with the details of this e-mail. 
                      </p>');
				$mail->AddAddress($user[0]['mail']);
				if($mail->Send()){
					$this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">Your request has been processed, check your e-mail for further instructions.</div>';
				}
				else{
					print_r($mail->errorInfo);
				}
				
			}
			else{
				$this->output_buffer .= '<div align="center" style="color:darkred;">An error occured uploading your request, please try again later.<br /><a href="?id='.$_GET['id'].'&act=forgot">Return</a></div>';
			}
		}
		else{
			$this->output_buffer .= '<div align="center" style="color:darkred;">No user is registered with this e-mail address<br /><a href="?id='.$_GET['id'].'&act=forgot">Return</a></div>';
		}
	}
	
	private function generatePassword(){
		//	Letterbox
		$letters = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',0,1,2,3,4,5,6,7,8,9);
		//	letterbox array min / max for randomize
		$min = 0;
		$max = count($letters);
		//	Set preset vars
		$i = 0;
		$pass = '';
		//	Get password
		while($i < rand(8,12)){
			$tempkey = $letters[rand($min,$max)];
			if(rand(0,1) == 1 && !is_numeric($tempkey)){
				$tempkey = strtoupper($tempkey);
			}
			$pass .= $tempkey;
			$i++;
		}
		return $pass;
	}
	
	private function recoverForgotPass(){
		if(isset($_GET['reqID'])){
			$request = $GLOBALS['database']->fetch_data("SELECT * FROM `pass_request` WHERE `auth_code` = '".$_GET['reqID']."' LIMIT 1");
			if($request != '0 rows'){
				if($_SERVER['REMOTE_ADDR'] == $request[0]['IP']){
					$user = $GLOBALS['database']->fetch_data("SELECT `username`,`mail`,`password` FROM `users` WHERE `username` = '".$request[0]['username']."' LIMIT 1");
					if($user != '0 rows'){
						$auth = md5($user[0]['mail'].'_'.$request[0]['username']);
						if($auth = $_GET['reqID']){
							//			All checks clear
							//			Generate password
							$password = $this->generatePassword();
							//			Delete request
							$GLOBALS['database']->execute_query("DELETE FROM `pass_request` WHERE `auth_code` = '".$_GET['reqID']."' LIMIT 1");
							//			Upload new password
							$GLOBALS['database']->execute_query("UPDATE `users` SET `password` = '".md5($password)."' WHERE `username` = '".$user[0]['username']."' LIMIT 1");
							//			Send e-mail!
							require_once('./libs/mail.inc.php');
							$mail = new Mail();
							$mail->Subject = 'Bleach-rpg account password change!';
							$mail->HTMLMail('<p>Password recovery complete:</p>
                      		<p>A password recovery was requested and completed, and the password to your account '.$user[0]['username'].' has hereby changed.</p>
                      		<p>Your new password is:<br>
                        	<b>'.$password.'</b></p>
                      		<p>You can change this password after you log in.</p>
                      		<p><strong>Did you not request a password change?</strong><br>
                      		If you did not request this password change then it is highly likely someone has access to your e-mail account. We advise to take appropriate countermreasures.<br>
                        	For any problems regarding account recovery, please contact support </p>');
							$mail->AddAddress($user[0]['mail']);
							if($mail->Send()){
								//			Return message
								$this->output_buffer .= '<div align="center">Your new password has been sent to your e-mail address, we advise you to change this password to a more secure password as soon as possible.<br /><a href="?id=1">Return</a></div>';
							}
							else{
								$this->output_buffer .= '<div align="center" style="color:darkred;">An error occured sending the e-mail, please try again later<br /> if problems persist please contact support.<br /><a href="?id=1">Return</a></div>';
							}
						}
						else{
							$this->output_buffer .= '<div align="center" style="color:darkred;">The password request is corrupt: Username / e-mail combo dont match.<br /><a href="?id=1">Return</a></div>';
						}
					}
					else{
						$this->output_buffer .= '<div align="center" style="color:darkred;">The password request is corrupt: User could not be found.<br /><a href="?id=1">Return</a></div>';
					}
				}
				else{
					$this->output_buffer .= '<div align="center" style="color:darkred;">Your IP does not match with the one in the request, please re-issue the request in 30 minutes.<br /><a href="?id=1">Return</a></div>';
				}
			}
			else{
				$this->output_buffer .= '<div align="center" style="color:darkred;">No password recovery request was issued for this account<br /><a href="?id=1">Return</a></div>';
			}
		}
		else{
			$this->output_buffer .= '<div align="center" style="color:darkred;">No request authentication ID was found, please follow the link in your e-mail<br /><a href="?id=1">Return</a></div>';
		}
	}

    private function resendActivation(){
        $this->output_buffer .= '<div align="center">
                <form name="form1" method="post" action="">
                  <table width="90%" border="0" cellspacing="0" cellpadding="0" class="table">
                    <tr>
                      <td colspan="2" align="center" class="subHeader" style="border-top:none;" >Resend verification e-mail</td>
                    </tr>
                    <tr>
                      <td width="49%" align="center" style="padding:2px;">Username:</td>
                      <td width="51%" align="center" style="padding:2px;"><input type="text" name="username"></td>
                    </tr>
                    <tr>
                      <td colspan="2" align="center" style="padding:2px;"><input type="submit" name="Submit" value="Submit"></td>
                    </tr>
                  </table>              
                  </form>
                  <br />
                </div>';
    }
    
    private function doResend(){
        $_POST['username'] = str_replace("%","",$_POST['username']);
        $user = $GLOBALS['database']->fetch_data("SELECT `mail`,`activation`,`username` FROM `users` WHERE `username` LIKE '".$_POST['username']."'");
        if($user != '0 rows'){
            if($user[0]['activation'] == 0){
                //    Send verification e-mail:
                if(include_once('./libs/mail.inc.php')){
                    $mail = new Mail();
                    $mail->Subject = 'Bleach-rpg account';
                    $mail->HTMLMail('Confirmation on registration:<br /><br />
                    <br /><b>Do not reply to this message.</b> If you didnt register an account, kindly follow the instructions in this email.</br>
                    <br /><b>Welcome to the ninja-rpg!</b></br>
                    <br />The Ninja RPG is a browser based role playing game.</br> 
                    <br />Thank you for you registration. Click on the following link to activate your account. This confirmation of your email is made to protect your identity and to ensure your safety on bleach-rpg.net.</br>
                    <a href="http://www.bleach-game.com/?id=2&act=activate&user='.$_POST['username'].'&code='.md5($user[0]['mail'].'confirm').'">Important! Click here to activate your account.</a>
                    <br />If you are unable to click the activation link above, you can enter this URL directly into the browser:</br>
                    http://www.bleach-rpg.net/?id=2&act=activate&user='.$_POST['username'].'&code='.md5($user[0]['mail'].'confirm').'
                    <br /><center>
                    Your user ID     : '.$user[0]['username'].'<br />
                    Your password    : '.$user[0]['password'].'
                    </center><br />
                    <br />You will find the manual at:<br />
                    (replaceWithManualURI)
                    <br>We, the ninja rpg team, hope that you will have a joyfull gaming experience. Good luck and have fun!<br />
                    <br />http://www.bleach-rpg.net<br />
                    <br /><b>Terms:</b> By activating this account, you agree to follow our terms of service and rules.
                    <b>Not your account?:</b> You have recieved this email because an account was created on bleach-rpg where this email was used. If you didnt recently create this account please contact us on: bleach.support@gmail.com<br />');
                    $mail->AddAddress($user[0]['mail']);
                    if($mail->Send()){
                        $this->output_buffer .= '<div align="center">The activation mail for this character has been resent.</div>';
                    }
                    else{
                        print_r($mail->errorInfo);
                    }
                }
                else{
                     $GLOBALS['error']->handle_error('Mail library could not be included',1,false);
                }
            }
            else{
                $this->output_buffer .= '<div align="center">This account is already activated<br /><a href="?id=2&act=resend_activation">Return</a></div>';   
            }
        }
        else{
            $this->output_buffer .= '<div align="center">This account does not exist<br /><a href="?id=2&act=resend_activation">Return</a></div>';   
        }
    }
    
    private function unlockForm(){
        $this->output_buffer .= '<div align="center">
                <form name="form1" method="post" action="">
                  <table width="90%" border="0" cellspacing="0" cellpadding="0" class="table">
                    <tr>
                      <td colspan="2" align="center" class="subHeader" style="border-top:none;" >Resend verification e-mail</td>
                    </tr>
                    <tr>
                      <td width="49%" align="center" style="padding:2px;">Username:</td>
                      <td width="51%" align="center" style="padding:2px;"><input type="text" name="username"></td>
                    </tr>
                    <tr>
                      <td colspan="2" align="center" style="padding:2px;"><input type="submit" name="Submit" value="Submit"></td>
                    </tr>
                  </table>              
                  </form>
                  <br />
                </div>';
    }
    
    private function doUnlock(){
        if(isset($_GET['auth']) && isset($_GET['username'])){
            $request = $GLOBALS['database']->fetch_data("SELECT * FROM `unlock` WHERE `auth_code` = '".$_GET['auth']."' LIMIT 1");
            $userdata = $GLOBALS['database']->fetch_data("SELECT `mail`,`activation`,`username`,`lock_count` FROM `users` WHERE `username` = '".$_GET['username']."' LIMIT 1");
            if($userdata != '0 rows'){
                if($userdata[0]['lock_count'] >= 3){
                    if(time() < ($request[0]['time'] + 3600)){
                        if($request[0]['auth_code'] == $_GET['auth'] && $request[0]['username'] == $userdata[0]['username']){
                            if($GLOBALS['database']->execute_query("UPDATE `users` SET `lock_count` = '0' WHERE `username` = '".$userdata[0]['username']."' LIMIT 1")){
                                $GLOBALS['database']->execute_query("DELETE FROM `unlock` WHERE `auth_code` = '".$_GET['auth']."' LIMIT 1");
                                $this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">Your account has now been unlocked, you can now log in.</div>';
                            }
                            else{
                                $this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">An error occured unlocking the account.</div>';
                            }
                        }
                        else{
                            $this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">This unlock code is not valid, please follow the link specified in the mail<br /> if problems persist contact support.</div>';
                        }
                    }
                    else{
                        $this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">This unlock code has expired, please request a new one.</div>';
                    }
                }
                else{
                    $this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">This account has already been unlocked.</div>';
                }
            }
            else{
                $this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">The user could not be found, please follow the link specified in the mail<br /> if problems persist contact support.</div>';
            }
        }
        else{
            $this->output_buffer .= '<div align="center" style="font-weight:bold;color:darkred;">No user or unlock code were set, please follow the link specified in the e-mail.</div>';
        }
    }

    private function sendUnlock(){
        $user = $GLOBALS['database']->fetch_data("SELECT `mail`,`activation`,`username`,`lock_count` FROM `users` WHERE `username` = '".$_POST['username']."'");
        if($user != '0 rows'){
            if($user[0]['lock_count'] == 3){
                $unlockCode = md5(time() + $user[0]['username'] + 'UNLOCK');
                //    Send verification e-mail:
                if(include_once('./libs/mail.inc.php')){
                    $mail = new Mail();
                    $mail->Subject = 'Bleach-game account';
                    $mail->HTMLMail('<b>Account unlock</b><br />
                    This e-mail has been sent to you because you requested your account on bleach-rpg be unlocked. After previously being locked due to having too many invalid login attempts.<br />
                    to unlock your account <a href="http://www.bleach-game.com/?id=2&act=do_unlock&username='.$user[0]['username'].'&auth='.$unlockCode.'">Click here</a> or copy the following link into your browser<br /><br />
                    http://www.bleach-game.com/?id=2&act=do_unlock&username='.$user[0]['username'].'&auth='.$unlockCode.'
                    <br /><br />
                    This unlock code will remain valid for 60 minutes, or until the account has been unlocked.');
                    $mail->AddAddress($user[0]['mail']);
                    if($mail->Send()){
                        $GLOBALS['database']->execute_query("INSERT INTO `unlock` ( `time` , `username` , `auth_code` , `IP` )VALUES ('".time()."', '".$user[0]['username']."', '".$unlockCode."', '".$_SERVER['REMOTE_ADDR']."');");
                        $this->output_buffer .= '<div align="center">The unlock mail for this character has been sent.</div>';
                    }
                    else{
                        print_r($mail->errorInfo);
                    }
                }
                else{
                    $GLOBALS['error']->handle_error('Mail library could not be included',1,false);
                }
            }
            else{
                $this->output_buffer .= '<div align="center">This account is not locked<br /><a href="?id=2&act=send_unlock">Return</a></div>';   
            }
        }
        else{
            $this->output_buffer .= '<div align="center">This account does not exist<br /><a href="?id=2&act=send_unlock">Return</a></div>';   
        }
    }

}

$register = new register();
?>