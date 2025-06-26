<?php
/*
 *		Report users for rule violations in PM's, tavern, nindo, or user picture.
 */
class report{
	var $output_buffer;
	
	function report(){
		if($_GET['act'] == 'tavern'){
			//	Report tavern message
			if(!isset($_POST['Submit'])){
				$this->tavern_report();
			}
			else{
				$this->file_tavern_report();
			}
		}
		elseif($_GET['act'] == 'nindo'){
			//	Report nindo
			if(!isset($_POST['Submit'])){
				$this->nindo_report();
			}
			else{
				$this->file_nindo_report();
			}
		}
		elseif($_GET['act'] == 'pm'){
			//	Report PM
			if(!isset($_POST['Submit'])){
				$this->PM_report();
			}
			else{
				$this->file_PM_report();
			}
		}
		elseif($_GET['act'] == 'user'){
			//	Report generic rule violation
			if(!isset($_POST['Submit'])){
				$this->user_report();
			}
			else{
				$this->file_user_report();
			}
		}
		$this->return_stream();
	}
	
	function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	//		Header
	function header(){
		return '<table width="95%" class="table" border="0" cellspacing="0" cellpadding="0">
    	<tr><td colspan="2" style="text-align:center;font-weight:bold;color:#CC0000">Warning!</td></tr>
    	<tr><td colspan="2" style="text-align:center;">You are about to report a user for a rule violation / misconduct. Users who abuse this feature will be <b>severely punished</b>.<br>
        Abuse includes filing false reports, spamming the same report multiple times, and filling out the report in anything but understandable english. </td>
      	</tr></table><br />';
	}
	
	//		Report tavern message
	function tavern_report(){
		if(isset($_GET['mt']) && is_numeric($_GET['mt'])){
			if(isset($_GET['uid']) && is_numeric($_GET['uid'])){
				$this->output_buffer .= $this->header();
				$message = $GLOBALS['database']->fetch_data("SELECT * FROM `tavern`,`users` WHERE `time` = '".$_GET['mt']."' AND `user_id` = '".$_GET['uid']."' LIMIT 1");
				if($message != '0 rows'){
					$this->output_buffer .= '<div align="center"><br>
  					<form name="form1" method="post" action="">
    				<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0"><tr>
        			<td colspan="2" align="center" style="border-top:none;" class="subHeader">Report user: </td>
      				</tr><tr><td colspan="2" align="center">&nbsp;</td></tr><tr>
        			<td width="30%" align="center" style="padding:2px;">Username:</td>
        			<td width="70%" align="left" style="padding:2px;">'.$message[0]['user'].'</td>
      				</tr><tr><td align="center" style="padding:2px;">Report by: </td>
        			<td align="left" style="padding:2px;">'.$_SESSION['username'].'</td>
      				</tr><tr><td align="center" style="padding:2px;">Reason:</td>
        			<td align="left" style="padding:2px;"><select name="reason" id="reason">
          			<option>Harassment</option><option>Foul language</option><option>Spamming</option>
          			<option>Selling / Buying accounts</option><option value="other">Other (enter below)</option>
                    </select></td></tr><tr><td align="center" style="padding:2px;">&nbsp;</td>
        			<td align="left" style="padding:2px;"><input name="reason_text" type="text" id="reason_text" size="35"></td>
      				</tr><tr><td align="center" colspan="2" style="padding:2px;"><strong>Tavern Message:</strong></td>
      				</tr><tr><td align="center" colspan="2" style="padding:10px;border-top:double 3px #000000;border-bottom:double 3px #000000;">'.functions::parse_BB($message[0]['message']).'</td>
      				</tr><tr><td colspan="2" align="center" style="padding:2px;"><input type="submit" name="Submit" value="Submit"></td>
      				</tr></table></form></div>';
				}
				else{
					//	This message does not exist
					$GLOBALS['error']->handle_error('404','The message you are trying to report doesn\'t exist','0');
				}
			}
			else{
				//	Invalid message
				$GLOBALS['error']->handle_error('500','You are trying to report an invalid message','0');
			}
		}
		else{
			//	invalid message
			$GLOBALS['error']->handle_error('500','You are trying to report an invalid message','0');
		}
	}
	
	//		File the tavern report:
	function file_tavern_report(){
		if(isset($_GET['mt']) && is_numeric($_GET['mt'])){
			if(isset($_GET['uid']) && is_numeric($_GET['uid'])){
				$message = $GLOBALS['database']->fetch_data("SELECT * FROM `tavern`,`users` WHERE `time` = '".$_GET['mt']."' AND `user_id` = '".$_GET['uid']."' LIMIT 1");
				if($message != '0 rows'){
					$test = $GLOBALS['database']->fetch_data("SELECT COUNT(`time`) AS `count` FROM `user_reports` WHERE `uid` = '".$message[0]['user_id']."' AND `message` = '".addslashes($message[0]['message'])."'");
					if($test[0]['count'] == 0){
						if($_POST['reason'] != 'other' && $_POST['reason'] != ''){
							$reason = $_POST['reason'];
						}
						elseif($_POST['reason_text'] != ''){
							$reason = functions::store_content($_POST['reason_text']);
						}
						if($reason != ''){
							$GLOBALS['database']->execute_query(" INSERT INTO `user_reports` ( `time` , `uid` , `rid` , `reason` , `message` , `status` , `processed_by` , `type` )VALUES ('".time()."', '".$message[0]['user_id']."', '".$_SESSION['uid']."', '".$reason."', '".functions::store_content($message[0]['message'])."', 'unviewed', '', 'tavern') ");
							$this->output_buffer .= '<div align="center"><b>Report user:</b><br />Your report has been submitted, a moderator will review it as soon as possible.</div>';
						}
						else{
							//	No reason submitted
							$GLOBALS['error']->handle_error('500','You did not submit a valid reason for reporting this user /  message','0');
						}
					}
					else{
						//	Message was already reported:
						$this->output_buffer .= '<div align="center"><b>Report user / message:</b><br />This message was already reported.</div>';
					}
				}
				else{
					//	This message does not exist
					$GLOBALS['error']->handle_error('404','The message you are trying to report doesn\'t exist','0');
				}
			}
			else{
				//	Invalid message
				$GLOBALS['error']->handle_error('500','You are trying to report an invalid message','0');
			}
		}
		else{
			//	invalid message
			$GLOBALS['error']->handle_error('500','You are trying to report an invalid message','0');
		}
	}


	//		PM report
	function PM_report(){
		if(isset($_GET['pmid']) && is_numeric($_GET['pmid']) && isset($_GET['uid'])){
			$this->output_buffer .= $this->header();
			$message = $GLOBALS['database']->fetch_data("SELECT users_pm.* FROM `users_pm` WHERE `time` = '".$_GET['pmid']."' AND `sender` = '".$_GET['uid']."' AND `reciever` LIKE '".$_SESSION['username']."' LIMIT 1");
			if($message != '0 rows'){
				if(strtolower($_SESSION['username']) == strtolower($message[0]['reciever'])){
					$this->output_buffer .= '<div align="center"><br>
					<form name="form1" method="post" action="">
   					<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0"><tr>
       				<td colspan="2" align="center" style="border-top:none;" class="subHeader">Report user: </td>
   					</tr><tr><td colspan="2" align="center">&nbsp;</td></tr><tr>
      				<td width="30%" align="center" style="padding:2px;">Username:</td>
       				<td width="70%" align="left" style="padding:2px;">'.$message[0]['sender'].'</td>
   					</tr><tr><td align="center" style="padding:2px;">Report by: </td>
       				<td align="left" style="padding:2px;">'.$_SESSION['username'].'</td>
     				</tr><tr><td align="center" style="padding:2px;">Reason:</td>
        			<td align="left" style="padding:2px;"><select name="reason" id="reason">
          			<option>Harassment</option><option>Foul language</option><option>Spamming</option>
          			<option>Selling / Buying accounts</option><option value="other">Other (enter below)</option>
                	</select></td></tr><tr><td align="center" style="padding:2px;">&nbsp;</td>
        			<td align="left" style="padding:2px;"><input name="reason_text" type="text" id="reason_text" size="35"></td>
      				</tr><tr><td align="center" colspan="2" style="padding:2px;"><strong>Private Message:</strong></td>
      				</tr><tr><td align="center" colspan="2" style="padding:10px;border-top:double 3px #000000;border-bottom:double 3px #000000;">'.functions::parse_BB("".$message[0]['subject'].": ".$message[0]['message']."").'</td>
      				</tr><tr><td colspan="2" align="center" style="padding:2px;"><input type="submit" name="Submit" value="Submit"></td>
      				</tr></table></form></div>';
				}
				else{
					$GLOBALS['error']->handle_error('404','You cannot report a PM that does not belong to you','0');
				}
			}
			else{
				//	This message does not exist
				$GLOBALS['error']->handle_error('404','The message you are trying to report doesn\'t exist, or the PM does not belong to you.','0');
			}
		}
		else{
			//	invalid message
			$GLOBALS['error']->handle_error('500','You are trying to report an invalid PM','0');
		}
	}
	
	//		File PM report
	function file_PM_report(){
		if(isset($_GET['pmid']) && is_numeric($_GET['pmid']) && isset($_GET['uid'])){
				$message = $GLOBALS['database']->fetch_data("SELECT users_pm.* FROM `users_pm` WHERE `time` = '".$_GET['pmid']."' AND `sender` = '".$_GET['uid']."' AND `reciever` LIKE '".$_SESSION['username']."' LIMIT 1");
				if($message != '0 rows'){
					$test = $GLOBALS['database']->fetch_data("SELECT COUNT(`time`) AS `count` FROM `user_reports` WHERE `uid` = '".$message[0]['uid']."' AND `message` = '".$message[0]['message']."'");
                    if($test[0]['count'] == 0){
						if($_POST['reason'] != 'other' && $_POST['reason'] != ''){
							$reason = $_POST['reason'];
						}
						elseif($_POST['reason_text'] != ''){
							$reason = functions::store_content($_POST['reason_text']);
						}
						if($reason != ''){
                            $user = $GLOBALS['database']->fetch_data("SELECT `id` FROM `users` WHERE `username` LIKE '".$message[0]['sender']."' LIMIT 1");
							$GLOBALS['database']->execute_query(" INSERT INTO `user_reports` ( `time` , `uid` , `rid` , `reason` , `message` , `status` , `processed_by` , `type` )VALUES ('".time()."', '".$user[0]['id']."', '".$_SESSION['uid']."', '".$reason."', '".functions::store_content("".$message[0]['subject'].": ".$message[0]['message']."")."', 'unviewed', '', 'PM') ");
							$this->output_buffer .= '<div align="center"><b>Report user:</b><br />Your report has been submitted, a moderator will review it as soon as possible.</div>';
						}
						else{
							//	No reason submitted
							$GLOBALS['error']->handle_error('500','You did not submit a valid reason for reporting this user /  message','0');
						}
					}
					else{
						//	Message was already reported:
						$this->output_buffer .= '<div align="center"><b>Report user / message:</b><br />This message was already reported.</div>';
					}
				}
				else{
					//	This message does not exist
					$GLOBALS['error']->handle_error('404','The message you are trying to report doesn\'t exist','0');
				}
			}
			else{
				//	Invalid message
				$GLOBALS['error']->handle_error('500','You are trying to report an invalid message','0');
			}
	}
	
	//		User report
	function user_report(){
		if(isset($_GET['uid']) && is_numeric($_GET['uid'])){
			$this->output_buffer .= $this->header();
			$message = $GLOBALS['database']->fetch_data("SELECT `username` FROM `users` WHERE `id` = '".$_GET['uid']."' LIMIT 1");
			if($message != '0 rows'){
				$this->output_buffer .= '<div align="center"><br>
				<form name="form1" method="post" action="">
 				<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0"><tr>
     			<td colspan="2" align="center" style="border-top:none;" class="subHeader">Report user: </td>
   				</tr><tr><td colspan="2" align="center">&nbsp;</td></tr><tr>
      			<td width="30%" align="center" style="padding:2px;">Username:</td>
       			<td width="70%" align="left" style="padding:2px;">'.$message[0]['sender'].'</td>
   				</tr><tr><td align="center" style="padding:2px;">Report by: </td>
       			<td align="left" style="padding:2px;">'.$_SESSION['username'].'</td>
     			</tr><tr><td align="center" style="padding:2px;">Reason:</td>
        		<td align="left" style="padding:2px;"><select name="reason" id="reason">
          		<option>User picture</option><option>Username</option><option value="other">Other (enter below)</option>
                </select></td></tr><tr><td align="center" style="padding:2px;">&nbsp;</td>
        		<td align="left" style="padding:2px;"><input name="reason_text" type="text" id="reason_text" size="35"></td>
      			</tr><tr><td colspan="2" align="center" style="padding:2px;"><input type="submit" name="Submit" value="Submit"></td>
      			</tr></table></form></div>';
			}
			else{
				//	This message does not exist
				$GLOBALS['error']->handle_error('404','The user you are trying to report doesn\'t exist','0');
			}
		}
		else{
			//	invalid message
			$GLOBALS['error']->handle_error('500','You are trying to report an invalid user','0');
		}
	}
	
	//		File user report
	function file_user_report(){
		if(isset($_GET['uid']) && is_numeric($_GET['uid'])){
				$message = $GLOBALS['database']->fetch_data("SELECT `username` FROM `users` WHERE `id` = '".$_GET['uid']."' LIMIT 1");
				if($message != '0 rows'){
					if($_POST['reason'] != 'other' && $_POST['reason'] != ''){
						$reason = $_POST['reason'];
					}
					elseif($_POST['reason_text'] != ''){
						$reason = functions::store_content($_POST['reason_text']);
					}
					$test = $GLOBALS['database']->fetch_data("SELECT COUNT(`time`) AS `count` FROM `user_reports` WHERE `uid` = '".$message[0]['uid']."' AND `reason` = '".$reason."'");
					if($test[0]['count'] == 0){
						if($reason != ''){
							$GLOBALS['database']->execute_query(" INSERT INTO `user_reports` ( `time` , `uid` , `rid` , `reason` , `message` , `status` , `processed_by` , `type` )VALUES ('".time()."', '".$_GET['uid']."', '".$_SESSION['uid']."', '".$reason."', '".$message[0]['message']."', 'unviewed', '', 'user') ");
							$this->output_buffer .= '<div align="center"><b>Report user:</b><br />Your report has been submitted, a moderator will review it as soon as possible.</div>';
						}
						else{
							//	No reason submitted
							$GLOBALS['error']->handle_error('500','You did not submit a valid reason for reporting this user /  message','0');
						}
					}
					else{
						//	Message was already reported:
						$this->output_buffer .= '<div align="center"><b>Report user / message:</b><br />This message was already reported.</div>';
					}
				}
				else{
					//	This message does not exist
					$GLOBALS['error']->handle_error('404','The user you are trying to report doesn\'t exist','0');
				}
			}
			else{
				//	Invalid message
				$GLOBALS['error']->handle_error('500','You are trying to report an invalid user','0');
			}
	}
}
$class = new report();
?>