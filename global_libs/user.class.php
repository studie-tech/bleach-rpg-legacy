<?php
/*--------------------------------------------------*/
/*					User Class						*/
/*			Class for logging in / out users		*/
/*--------------------------------------------------*/
class user{
    
	function parse(){
		if(isset($_SESSION['uid'])){
			$this->update_user();	
		}
		$this->set_login();
		$this->show_loginbox();	
	}
    
    //    Get data
    public function setData($userData){
        if(($userData != '0 rows' && $userData != '')){
            $this->data = $userData;
        }
    }
	
	function update_user(){
		$time = time();
		//		Set update time
		if($this->data[0]['last_regen'] == 0){
			$update_time = time();
			$query = "UPDATE `users_timer` SET `last_regen` = '".time()."' WHERE `userid` = '".$_SESSION['uid']."'";
            
            // Update Memcache
            $GLOBALS['userdata'][0]['last_regen'] = "".time()."";  
            $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
		}
		else{
			$update_time = $this->data[0]['last_regen'];
		}
        
          
		//		Determine if user regenerates
		if($update_time + 60 < $time){
            
        // Update IP if it's changed
        $ip = $_SERVER['REMOTE_ADDR'];
        if(($this->data[0]['last_ip'] == 0 || $this->data[0]['last_ip'] != $ip) && isset($this->data[0]['last_ip'])){
             $GLOBALS['database']->execute_query("UPDATE `users_timer` SET `last_ip` = '".$ip."' WHERE `userid` = '".$this->data[0]['userid']."' LIMIT 1");
             
             // Update Memcache
           $GLOBALS['userdata'][0]['last_ip'] = "".$ip."";  
           $GLOBALS['cache']->replace("resu:".$this->data[0]['userid'],$GLOBALS['userdata'],false,43200);
        }
        
        
        // Set Regen
		$race = $GLOBALS['database']->fetch_data("SELECT `regen` FROM `race_vars` WHERE `name` = '".$this->data[0]['race']."'");             
  		$regen = $this->data[0]['regen_rate'] + $this->char_data[0]['regen_boost'] + $race[0]['regen'];

        // Ability Regen
        if(strstr($this->data[0]['abillity'], "REG")){
            $tempo = explode("|",$this->data[0]['abillity']);
            if(strstr($tempo[0],"REG")){
                $regger = explode(":", $tempo[0]); $abillityreg = $regger[3];
            }
            elseif(strstr($tempo[1],"REG")){
                $regger = explode(":", $tempo[1]); $abillityreg = $regger[3];
            }
            elseif(strstr($tempo[2],"REG")){
                $regger = explode(":", $tempo[2]); $abillityreg = $regger[3];
            }
        }
        else{
            $abillityreg = 0;
        }
        $regen = round($regen*(1 + $abillityreg / 100),1);
            
            
            //determine regen values
            $passed_time = $time - $update_time;
            $regen_cycles = floor($passed_time / 60);
			//		Do upload
			if($this->data[0]['status'] != 'battle' && $this->data[0]['status'] != 'combat' && $this->data[0]['status'] != 'hospitalized'){
                //        Calculate new current stats:
                $new_rei = $this->data[0]['cur_rei'] + $regen_cycles * $regen;
                $new_health = $this->data[0]['cur_health'] + $regen_cycles * $regen;
                //        Check for caps
                if($new_rei > $this->data[0]['max_rei']){
                    $new_rei = $this->data[0]['max_rei'];
                }
                if($new_health > $this->data[0]['max_health']){
                    $new_health = $this->data[0]['max_health'];
                }
                $query = "UPDATE `users`,`users_timer` SET users_timer.last_regen = '".time()."', 
                                                           users.cur_rei = '".$new_rei."', 
                                                           users.cur_health = '".$new_health."' 
                         WHERE users.id = '".$_SESSION['uid']."' AND users.id = users_timer.userid AND users.cur_rei < ".($this->data[0]['cur_rei'] + 1)."";
			}
		}
		if($query != ''){
			$GLOBALS['database']->execute_query($query);
            // Update Memcache
            $GLOBALS['userdata'][0]['last_regen'] = "".time()."";
            $GLOBALS['userdata'][0]['cur_rei'] = "".$new_rei."";
            $GLOBALS['userdata'][0]['cur_health'] = "".$new_health."";
            $GLOBALS['userdata'][0]['cur_health'] = "".$new_health."";
            $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
		}

	}
	
	function logged_users(){
		$logged = time()-120;
		$result = $GLOBALS['database']->execute_query("SELECT COUNT(userid) as TOTALFOUND FROM `users_timer` WHERE `last_regen` > '$logged'");
  		return (mysql_result($result,0,"TOTALFOUND"));
	}


		
	function set_login(){
		//	Function logs in user if login data was set.
		if(isset($_POST['lgn_usr_stpd']) && $_POST['lgn_usr_stpd'] != ''){
            if(!isset($_SESSION['uid'])){
				//	Log in user
				if(is_object($GLOBALS['database'])){
                    if(
                            (isset($_POST["recaptcha_challenge_field"]) && isset($_POST["recaptcha_response_field"]) ) 
                        ){
                            if( 
                                $GLOBALS['error']->checkCaptcha()
                            ){
				 	                if($logindata = $GLOBALS['database']->fetch_data("SELECT `username`,`password`,`mail`,`id`,`user_rank`,`race`, `layout`,`ban_time`,`perm_ban`, `last_regen`,`activation`,`lock_count`,`lock` FROM `users`,`users_timer` WHERE `username` = '".$_POST['lgn_usr_stpd']."' AND users_timer.userid = users.id LIMIT 1")){
				 	 	                $logged_users = $this->logged_users();
					 	                if($logged_users <= 500 || $logindata[0]['last_regen'] == 0 || $logindata[0]['username'] == "System" || $logindata[0]['username'] == "Terriator"){				 	
							                if("0 rows" == $GLOBALS['database']->fetch_data("SELECT `id` FROM `banned_ips` WHERE `ip` = '".$_SERVER['REMOTE_ADDR']."' LIMIT 1")){
                                                if($logindata != '0 rows'){
                                                    if($logindata[0]['lock_count'] < 3 || $logindata[0]['lock'] == 0){
                                 	                    //if($logindata[0]['mail'] == $_POST['login_email']){
								                            if($logindata[0]['activation'] == 1){
									                            if($logindata[0]['password'] == md5($_POST['login_password'])){      
                                                                     if($logindata[0]['perm_ban'] == 0){
										                                if($logindata[0]['ban_time'] == 0 || ($logindata[0]['ban_time'] < time())){
											                                if($logindata[0]['ban_time'] != 0){
												                                $GLOBALS['database']->execute_query("UPDATE `users_timer` SET `ban_time` = '0' WHERE `userid` = '".$logindata[0]['id']."' LIMIT 1");
                                                                                
											                                }     
											                                //    Admin account IP lockdown:
                                                                            if( $logindata[0]['username'] == 'Terriator' ||
                                                                                $logindata[0]['username'] == 'Takeda_Xin' ||
                                                                                $logindata[0]['username'] == 'CrownedClown'
                                                                              ){  
                                                                                $data = $GLOBALS['database']->fetch_data("SELECT `value` FROM `site_information` WHERE `option` = 'admin_ips'");
                                                                                if( $data !== "0 rows" ){
                                                                                    $admin = false;
                                                                                    $entries = explode(";", $data[0]['value']);
                                                                                    foreach ($entries as $entry){
                                                                                        if( strstr($entry,$logindata[0]['username']) ){
                                                                                            $adminip = explode(",",$entry);
                                                                                            if( $adminip[1] == $_SERVER['REMOTE_ADDR'] || $adminip[1] == $_SERVER['HTTP_X_FORWARD_FOR'] ){
                                                                                                $admin = true;
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                                else{
                                                                                    $admin = false;
                                                                                }
                                                                              }
                                                                              else{
                                                                                $admin = true;
                                                                              }
                                                                    

                                                                               
											                                
											                                  if($admin == true){
												                                //	Log login:
                                                                                if(false){
												                                    $file = fopen('./logs/login.log','a');
												                                    fwrite($file,date('Y-m-d G:i:s').' '.$_POST['lgn_usr_stpd'].' '.$_SERVER['REMOTE_ADDR']." \r\n");
												                                    fclose($file);
                                                                                }
												                                //	Create session:
                                                                                
												                                session_register('uid','username','ip','user_rank');
												                                $_SESSION['uid'] = $logindata[0]['id'];
												                                $_SESSION['username'] = $logindata[0]['username'];
												                                $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
												                                $_SESSION['user_rank'] = $logindata[0]['user_rank'];
												                                $_SESSION['layout'] = $logindata[0]['layout'];
												                                $_SESSION['race'] = $logindata[0]['race'];
												                                $_SESSION['login'] = time();
                                                                    
                                                                                $GLOBALS['database']->execute_query("UPDATE `users` SET `lock_count` = '0' WHERE `id` = '".$logindata[0]['id']."' LIMIT 1");
												                                $GLOBALS['database']->execute_query("UPDATE `users_timer` SET `last_login` = '".time()."' WHERE `userid` = '".$logindata[0]['id']."' LIMIT 1");
                                                                                
                                                                                // Login bug fix - need to set time initially
                                                                                $GLOBALS['userdata'] = functions::get_user($logindata[0]['id']);           
                                                                                $GLOBALS['userdata'][0]['last_login'] = time();
                                                                                $GLOBALS['cache']->replace("resu:".$logindata[0]['id'],$GLOBALS['userdata'],false,43200);  
                                                                                
                                                                                
												                                header('Location:?id=3');
            								                                }
            								                                else{
            									                                $GLOBALS['error']->handle_error('600','Admin accounts are locked down to IP\'s','1',true);
            									                                //	Log login:
												                                $file = fopen('./logs/bad_login.log','a');
												                                fwrite($file,'Wrong IP '.date('Y-m-d G:i:s').' '.$_POST['lgn_usr_stpd'].' '.$_SERVER['REMOTE_ADDR']." \r\n");
												                                fclose($file);
	            							                                }
										                                }
										                                else{
											                                /*			User is banned!			*/
											                                $bandata = $GLOBALS['database']->fetch_data("SELECT * FROM `moderator_log` WHERE `action` = 'ban' AND `uid` = '".$logindata[0]['id']."' ORDER BY `time` DESC LIMIT 1");
											                                if($bandata != '0 rows'){
												                                $message = 'You have been banned for the following reason:<br /><b>'.$bandata[0]['reason'].'</b><br />'.$bandata[0]['message'].'<br /><br /><b>Your ban will last until: '.date('Y-m-d G:i:s',$logindata[0]['ban_time']); 
											                                }
											                                $GLOBALS['error']->handle_error('403',$message,'1',false);
										                                }
                                                                    }
                                                                    else{
                                                                        /*          User is perm-banned         */
                                                                        $bandata = $GLOBALS['database']->fetch_data("SELECT * FROM `moderator_log` WHERE `action` = 'ban' AND `uid` = '".$logindata[0]['id']."' ORDER BY `time` DESC LIMIT 1");
                                                                        if($bandata != '0 rows'){
                                                                            $message = 'You have been banned for the following reason:<br /><b>'.$bandata[0]['reason'].'</b><br />'.$bandata[0]['message'].'<br />'; 
                                                                        }
                                                                        $GLOBALS['error']->handle_error('403','Your account has been blocked from the site indefinitely<br />'.$message.'','1',false);
                                                                    }
									                            }
									                            else{
										                            //	Password Incorrect
										                            $GLOBALS['error']->handle_error('403','Your username or password are incorrect','1',true);
                                                                    $GLOBALS['database']->execute_query("UPDATE `users` SET `lock_count` = `lock_count` + 1 WHERE `username` = '".$_POST['lgn_usr_stpd']."'");
										                            //	Log login attempt:
										                            $file = fopen('./logs/bad_login.log','a');
										                            fwrite($file,'Incorrect password '.date('Y-m-d G:i:s').' '.$_POST['lgn_usr_stpd'].' '.$_SERVER['REMOTE_ADDR']." \r\n");
										                            fclose($file);
									                            }
								                            }
								                            else{
									                            $GLOBALS['error']->handle_error('403','This account has not yet been activated<br /><a href="?id=2&act=resend_activation">Resend activation mail</a>','1',true);
								                            }
								                        /*}
								                        else{
									                            $GLOBALS['error']->handle_error('403','This is not the email on which the account is registered<br />','1',true);
								                        } */
                                                    }
                                                    else{
                                                        $GLOBALS['error']->handle_error('401','3 Invalid login attempts have been made on your account. It has now been locked<br />To unlock your account <a href="?id=2&act=send_unlock">Click here</a>','1',false); 
                                                    }
							                    }
							                    else{
								                    //	User does not exist
								                    $GLOBALS['error']->handle_error('403','This user does not exist','1',true);
							                    }
                                            }
                                            else{
                                                //    User does not exist
                                                $GLOBALS['error']->handle_error('403','This IP has been banned','1',true);
                                            }					
						                }
						                else{
							                //	Query Error
							                $message = 'There are currently over 20 users logged in! This is the maximum right now! <br />
									                I want to ensure that the cost of these automatically scaling servers isnt too crazy before I allow everyone in.:<br /><br />
									                <a href="http://www.theninja-rpg.com"><img src="images/ads/TNR.gif"></a><br /><br />';
							                $GLOBALS['error']->handle_error('100',''.$message.'','5');
						                }
					                }
					                else{
						                //	Query Error
						                $GLOBALS['error']->handle_error('100','This user does not exist','5');
					                }
                             	
                             }
                            else{
                                // No recaptcha entered yet
                                $GLOBALS['error']->captchaRequire('The captcha code you entered was incorrect. Please try again!');
                            }
                        }
                        else{
                            // No recaptcha entered yet
                            $GLOBALS['error']->captchaRequire('Confirm your humanity by entering the confirmation code. Press "Enter" to continue!');
                        }
				}
				else{
					//	No database module set
					$GLOBALS['error']->handle_error('100','The database module has not been loaded','7');
				}
                }        
                else{
                    $GLOBALS['error']->handle_error('500','You\'re already logged in, don\'t hit refresh.','1',true);
                }
			}
			elseif($_GET['act'] == 'logout'){
				//	logs out user, destroys session
                $GLOBALS['database']->execute_query("UPDATE `users_timer` SET `logout_timer` = '0' WHERE `userid` = '".$_SESSION['uid']."' LIMIT 1");
                // Update Memcache
                $GLOBALS['userdata'][0]['logout_timer'] = "0";    
                $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
                
				session_unset();
				session_destroy();
                header('Location: ?id=1');
			}
	}
	
	function show_loginbox(){
		//	Echoes login box, parses loginbox.tpl
		if(!isset($_SESSION['uid'])){
			$file = fopen('./templates/loginbox.tpl','r');
			$file_data = fread($file,filesize('./templates/loginbox.tpl'));
			$file_data = explode('-->',$file_data);
			fclose($file);
			$output_data = str_replace('[USERNAME]','<input type="text" size="11" class="textfield" name="lgn_usr_stpd" />',$file_data[1]);
			$output_data = str_replace('[EMAIL]','<input type="text" size="11" class="textfield" name="login_email" />',$output_data);
			$output_data = str_replace('[PASSWORD]','<input type="password" size="11" class="textfield" name="login_password" />',$output_data);
			$output_data = str_replace('[CODE]','<input type="text" size="11" class="textfield" name="login_code"/>',$output_data);
			$output_data = str_replace('[SUBMIT]', '<input type="submit" class="button" name="LoginSubmit" value="Submit" />',$output_data);
			//	Done parsing file, output to page stream
			$GLOBALS['page']->insert_page_data('[LOGIN]',$output_data);
			$GLOBALS['page']->insert_page_data('[AUTO]','');

		}
		else{
		 	$autologout = ($GLOBALS['userdata'][0]['last_login'] + 3600) - time();
        
            
		 	if($autologout <= 0){                
                header('Location:?id=1&act=logout');
            }
			$GLOBALS['page']->insert_page_data('[LOGIN]','<font size="-2"><a href="?id=1&act=logout">Log out</a></font>');
			$GLOBALS['page']->insert_page_data('[AUTO]','
					  <font size="-2">Automatic Logout: 
					  <script>var countDownInterval3='.$autologout.';</script>
					  <script src="libs/javascript/LogoutCounter.js"></script>
					  <script>startit3();</script>
					  </font>');
		}
	}
	
}
?>