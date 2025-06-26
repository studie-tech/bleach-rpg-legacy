<?php
/*
 *				Global functions file
 */


abstract class functions{
      
	//			BBCode Functions
	public function parse_BB($contents){	//preps content for parsing
		/*				BBcode Replacement:			*/
		$contents = str_replace("[/n]","<br />",$contents);
		$contents = str_replace("[b]","<b>",$contents);
		$contents = str_replace("[/b]","</b>",$contents);
		$contents = str_replace("[i]","<i>",$contents);
		$contents = str_replace("[/i]","</i>",$contents);
		$contents = str_replace("[u]","<u>",$contents);
		$contents = str_replace("[/u]","</u>",$contents);
		$contents = str_replace("[list]","<ul type='disc'>",$contents);
		$contents = str_replace("[/list]","</ul>",$contents);
		$contents = str_replace("[*]","<li>",$contents);
		$contents = str_replace("[mail]",'<a href="mailto:',$contents);
		$contents = str_replace("[/mail]",'">E-mail</a>',$contents);
		$contents = str_replace("[url]",'<a href="',$contents);
		$contents = str_replace("[/url]",'">Link!</a>',$contents);
        
        $contents = preg_replace("(\[color=(.+?)\](.+?)\[\/color\])is","<span style=\"color: $1\">$2</span>",$contents);
        
        //$contents = str_replace("[color=",'<a href="',$contents);
        //$contents = str_replace("[/color]",'">Link!</a>',$contents);
		/*			Strip slashes		*/
		$contents = stripslashes($contents);
		/*			Required fix
		$contents = str_replace("[","",$contents);
		$contents = str_replace("]","",$contents);*/
		$contents = nl2br($contents);
		/*				Other replacements:			*/
		return $contents;
	} 	// Call on all outgoing text.

	public function check_BB($contents){
        //          Check BBtag validation
		$check = array('[b]','[i]','[u]','[list]','[mail]');
		$check_close = array('[/b]','[/i]','[/u]','[/list]','[/mail]');
		$i_max = (count($check)-1);
		$i = 0;
		while($i <= $i_max){
			$checking = $check[$i];
			$closing = $check_close[$i];
			$open = substr_count($contents,$checking);
			$close = substr_count($contents,$closing);
			if($open > $close){
				$difference = ($open - $close);
				if($checking == '[mail]'){
					$contents = str_replace($checking,'',$contents);
				}
				else{
					$contents .= $check_close[$i];
				}
			}
			$i++;
		}
		return $contents;
	}	// Automatically called in store_content

	public function store_content($contents) {
		$contents = strip_tags($contents);
		$contents = functions::check_BB(stripslashes($contents));
		$contents = str_replace("--#","",$contents);
        $contents = str_replace("--","",$contents);
		$contents = str_replace("/*","",$contents);
        $contents = str_replace('&nbsp;','',$contents);
		$contents = addslashes($contents);
        $contents = nl2br($contents);
		$contents = trim($contents);
        $contents = functions::insert_linebreaks($contents);
		return $contents;
	}	//preps the content to be stored in the database

	public function mod_contents($contents){
		return $contents;
	}

    public function insert_linebreaks($message){
        if(strlen($message) > 50){
            if(stristr($message,' ') || stristr($message,'<br />')){
                $tmp = explode('<br />',$message);
                $i = 0;
                while($i < count($tmp)){
                    if(strlen($tmp[$i]) > 50){
                        //echo ': lineno #'.$i.':'.strlen($tmp[$i].':');
                        //  Cut string (preferably on a space)
                        if(!stristr($tmp[$i],' ')){
                            //echo 'no space, split!:';
                            //  No space exists.
                            $temp = str_split($tmp[$i],50);
                            $tmp[$i] = implode('<br />',$temp);
                            //echo $tmp[$i].':<br />';
                        }
                        else{
                            //echo stristr($tmp[$i],' ');   
                        }
                    }
                    $i++;
                }
                $outMsg = implode('',$tmp);
                return $outMsg;
            }
            else{
                $temp = str_split($message,50);
                $outMsg = implode('<br />',$temp);
                return $outMsg;
            }
        }
        else{
            return $message;   
        }
    }  
    
    //          Get the user
    public function get_user( $id ){
        $data = $GLOBALS['cache']->get("resu:".$id);
        if( !$data ){
            // Get From Database
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `users_timer`,`users` WHERE `userid` = '".$id."' AND users.id = users_timer.userid LIMIT 1");
            // Insert into cache
            if(!isset($data[0]['cur_rei']) || $data[0]['cur_rei'] == "" ){ $data[0]['cur_rei'] = 100; }
            if(!isset($data[0]['cur_health']) || $data[0]['cur_health'] == ""){ $data[0]['cur_health'] = 100; }
            $GLOBALS['cache']->add("resu:".$id,  $data, false, 43200);
        }
        if(!isset($data[0]['cur_rei']) || $data[0]['cur_rei'] == "" ){ $data[0]['cur_rei'] = 100; }
        if(!isset($data[0]['cur_health']) || $data[0]['cur_health'] == ""){ $data[0]['cur_health'] = 100; }
        return $data;
    }
    
    //          Get battle-data
    public function get_options( $id ){
        $data = $GLOBALS['cache']->get("elttab:".$id);
        if( !$data ){
            // Get From Database
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `battle_options` WHERE `uid` = '".$id."' LIMIT 1");
            // If row doesn't exist, then insert it
            if($data == "0 rows"){
                $GLOBALS['database']->execute_query("INSERT INTO `battle_options` (`uid`) VALUES ('".$id."')");
            }
            // Insert into cache
            $GLOBALS['cache']->add("elttab:".$id,  $data, false, 43200);
        }
        return $data;
    }
    
	//			Message check
	public function check_messages($user){
        $this->data = $user;
        $pmdata = $GLOBALS['database']->fetch_data("SELECT `time` FROM `users_pm` WHERE `reciever` = '".$_SESSION['username']."' AND `read` = 'no' LIMIT 1");
		if($this->data != '0 rows'){
		 	$row = "row1";
		 	$initialized = 0;
		 	$start .= '<table class="table" width="95%" border="0" cellspacing="0" cellpadding="0">
	    				<tr>
				        	<td colspan="2" align="center" style="border-top:none;" class="subHeader">Notifications</td>
				        </tr>';
			/*	Show them news... WUAHHH!!!	*/
			if($this->data[0]['read_news'] == 0){
			 	$news = $GLOBALS['database']->fetch_data("SELECT `related_text` FROM `event_options` WHERE `option` = 'news' LIMIT 1");
			 	if($news != '0 rows' && isset($news[0]['related_text'])){
				 	if($initialized == 0){$output .= $start; $initialized = 1;}
					$output .= '<tr><td colspan="2" align="center" style="border-top:none;" class="'.$row.'" ><font size="3">'.$news[0]['related_text'].'</font><br /><a href="?id='.$_GET['id'].'&remove=IhAvEaLaTeXbRaIn">I have read the news!</a></td></tr>'; 
					if($row == "row1"){$row = "row2";}else{$row = "row1";}
					if($_GET['remove'] == "IhAvEaLaTeXbRaIn"){
						$GLOBALS['database']->execute_query("UPDATE `users` SET `read_news` = 1 WHERE `id` = '".$_SESSION['uid']."'");
                        
                        // Update Memcache
                        $GLOBALS['userdata'][0]['read_news'] = "1";
                        $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
						
                        header('Location:?id='.$_GET['id'].'');
					}
				}
			}        
            
            
			/*	Globale messages and battle notifications; healed field	*/
			if($this->data[0]['healed'] != ''){
			 	if($initialized == 0){$output .= $start; $initialized = 1;}
				$output .= '<tr><td colspan="2" align="center" style="border-top:none;" class="'.$row.'" ><font size="3" color="#000080">'.$this->data[0]['healed'].'</font></td></tr>'; 
				if($row == "row1"){$row = "row2";}else{$row = "row1";}
				$GLOBALS['database']->execute_query("UPDATE `users` SET `healed` = '' WHERE `id` = '".$_SESSION['uid']."'");
                
                // Update Memcache
                $GLOBALS['userdata'][0]['healed'] = "";
                $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
                
			}
			
			/* Notify of whatever the user is training 	*/
			if( isset($this->data[0]['activity']) && $this->data[0]['activity'] !== "" ){
			 	if($initialized == 0){$output .= $start; $initialized = 1;}
			 	$timer = $this->data[0]['technique_timer'] - time() + 1;
				//if($timer <= 0){header('Location:?id=4');}
				if($this->data[0]['race']=="Hollow"){
					if($this->data[0]['activity'] == "Sword"){$mess = 'You are currently devouring humans. ';}
					if($this->data[0]['activity'] == "Shikai"){$mess = 'You are currently devouring hollows. ';}
					if($this->data[0]['activity'] == "Bankai"){$mess = 'You are currently training resurrection. ';}
					if($this->data[0]['activity'] == "Strength" || $this->data[0]['activity'] == "Intelligence" || $this->data[0]['activity'] == "Speed"){
					 	$mess = 'You are currently training: '.$this->data[0]['activity'].'. ';
					}
				}else{
					$mess = 'You are currently training: '.$this->data[0]['activity'].'. ';
				}
				$output .= '<tr><td colspan="2" align="center" style="border-top:none;" class="'.$row.'" >
					  <font size="3" color="#000080">'.$mess.'Time left: 
					  <script>var countDownInterval1='.$timer.';</script>
					  <script src="libs/javascript/NotificationCounter.js"></script>
					  <script>startit1();</script></font></td></tr>'; 
				if($row == "row1"){$row = "row2";}else{$row = "row1";}
				$GLOBALS['database']->execute_query("UPDATE `users` SET `healed` = '' WHERE `id` = '".$_SESSION['uid']."'");
                
                // Update Memcache
                $GLOBALS['userdata'][0]['healed'] = "";
                $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
			}
			
			/* Notify the user that he is flagged for deletion	*/
			if($this->data[0]['deletion_timer'] > 0){
			 	if($initialized == 0){$output .= $start; $initialized = 1;}
				$output .= '<tr><td colspan="2" align="center" style="border-top:none;" class="'.$row.'" ><a href="?id=7&act=delete">Account flagged for deletion</a></td></tr>';
				if($row == "row1"){$row = "row2";}else{$row = "row1";}
			}
            
            /*    EVENT character win   */
            $eventwin = $GLOBALS['cache2']->get("event:");
            if( $eventwin ){
                if($initialized == 0){$output .= $start; $initialized = 1;}
                $output .= '<tr><td colspan="2" align="center" style="border-top:none;" class="'.$row.'" ><font size="3" color="#000080">'.$eventwin.'</font></td></tr>'; 
                if($row == "row1"){$row = "row2";}else{$row = "row1";}                
            }
                
			
			/*	Show PMs	*/
			if(isset($pmdata[0]['time'])){
			 	if($initialized == 0){$output .= $start; $initialized = 1;}
				$output .= '<tr><td colspan="2" align="center" style="border-top:none;" class="'.$row.'" ><a href="?id=8">You have unread PM\'s. Click here to go to inbox</a></td></tr>';
				if($row == "row1"){$row = "row2";}else{$row = "row1";}
			}
			/*if($this->data[0]['user_rank'] == 'Moderator' || $this->data[0]['user_rank'] == 'Supermod' || $this->data[0]['user_rank'] == 'Admin'){
				$count = $GLOBALS['database']->fetch_data("SELECT COUNT(`time`) AS `count` FROM `user_reports` WHERE `status` = 'unviewed'");
				if($count[0]['count'] > 0){
				 	if($initialized == 0){$output .= $start; $initialized = 1;}
					$output .= '<tr><td colspan="2" align="center" style="border-top:none;" class="'.$row.'" ><a href="?id=16&act=reports">There are unviewed reports!</a></td></tr>';
				}
			}*/
			if($initialized == 1){$output .= '</table><br />';}
		}
		return $output;
	}

	//			Set options

	public function set_options(){
		$output = '<a href="?id=42">Users online</a>';
		return $output;
	}

	//	Convert seconds to days / hours / minutes / seconds
	public function convert_time($time){
		$seconds = $time % 60;
		$rest = ($time - $seconds) / 60;	//	Raw minutes
		$minutes = $rest % 60;
		$rest = ($rest - $minutes) / 60;
		$hours = $rest % 24;
		$rest = ($rest - $hours) / 24;
		$days = $rest;

		if($seconds > 0){
			$string = $seconds.' seconds';
		}
		if($minutes > 0){
			$string = $minutes.' minutes '.$string;
		}
		if($hours > 0){
			$string = $hours.' hours '.$string;
		}
		if($days > 0){
			$string = $days.' days '.$string;
		}

		return $string;
	}
    
    public function convert_PM_time($time){
        $seconds = $time % 60;
        $rest = ($time - $seconds) / 60;    //    Raw minutes
        $minutes = $rest % 60;
        $rest = ($rest - $minutes) / 60;
        $hours = $rest % 24;
        $rest = ($rest - $hours) / 24;
        $days = $rest;

        if($days > 0){
            $string = $days.' days ago';   
        }
        elseif($hours > 0){
            $string = $hours.' hours ago';
        }
        elseif($minutes > 0){
            $string = $minutes.' minutes ago';   
        }
        elseif($seconds > 0){
            $string = $seconds.' seconds ago';   
        }
        return $string;
    }

    /*
 	 *				Admin functions
 	 *		Only used inside the admin panel		
 	 */

	//	Parses a form for the specified table:
	public function parse_form($table,$title = 'Form',$ignore_fields = null,$data = null){
		error_reporting(0);
		if($data != null){
			//	Parse data already present
		}
		$fields = $GLOBALS['database']->fetch_data("SHOW COLUMNS FROM `".$table."`");
		if($fields != '0 rows'){
			$i = 0;
			$form_buffer = '<form id="form1" name="form1" method="post" action=""><div align="center"><table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
  			<tr><td colspan="2" align="center" class="mini_header">::'.$title.'::</td></tr>';
			while($i < count($fields)){
				if(!in_array($fields[$i]['Field'],$ignore_fields)){
					if(stristr($fields[$i]['Type'],'enum')){
						//	Dropbox, ENUM
						$formfield = '<select class="listbox" name="'.$fields[$i]['Field'].'">';
						$temp = str_replace('enum(','',$fields[$i]['Type']);
						$temp .= '$1233';
						$temp = str_replace(')$1233','',$temp);
						$temp = '#'.$temp.'#';
						$temp = str_replace('#\'','',$temp);
						$temp = str_replace('\'#','',$temp);
						$temp = explode('\',\'',$temp);
						$u = 0;
                        $override = false;
						while($u < count($temp)){
                            if($data != null){
							    if($data[0][$fields[$i]['Field']] == $temp[$u]){
                                    $override = true;
								    $formfield .= '<option selected value="'.$temp[$u].'">'.stripslashes($temp[$u]).'</option>';
							    }
							    else{
								    $formfield .= '<option value="'.$temp[$u].'">'.stripslashes($temp[$u]).'</option>';
							    }
                            }
                            elseif($temp[$u] == $fields[$i]['Default']){
                                $formfield .= '<option selected value="'.$temp[$u].'">'.stripslashes($temp[$u]).'</option>';
                            }
                            else{
                                $formfield .= '<option value="'.$temp[$u].'">'.stripslashes($temp[$u]).'</option>';
                            }
                            $u++;
						}
						if($fields[$i]['Null'] == 'YES'){
                            if($override == false){
							    $formfield .= '<option value="NULL" selected>NULL</option>';
                            }
                            else{
                                $formfield .= '<option value="NULL">NULL</option>';   
                            }
						}
						$formfield .= '</select>';
						$form_buffer .= '<tr>
    					<td width="181" align="left" style="padding-left:15px;">'.str_replace('_',' ',$fields[$i]['Field']).'</td>
    					<td width="387" align="left" style="padding:5px;">'.$formfield.'
    					</select></td></tr>';
					}
					elseif(stristr($fields[$i]['Type'],'TEXT') || stristr($fields[$i]['Type'],'BLOB')){
						//	TEXT blob
						if($data != null){
						$value = $data[0][$fields[$i]['Field']];
							}
						elseif($fields[$i]['Default'] != null){
							$value = $fields[$i]['Default'];
						}
						else{
							$value = 'Insert text';
						}
						$formfield = '<textarea class="textfield" cols="55" rows="5" name="'.$fields[$i]['Field'].'">'.stripslashes($value).'</textarea>';
						$form_buffer .= '<tr><td colspan="2" align="center" class="sub_header">'.str_replace('_',' ',$fields[$i]['Field']).'</td>
  						</tr><tr><td colspan="2" style="padding:5px;" align="center">'.$formfield.'</td></tr>';
					}
					else{
						//	INT, float or VARCHAR
						if($data != null){
							$value = $data[0][$fields[$i]['Field']];
						}
						elseif($fields[$i]['Default'] != null){
							$value = $fields[$i]['Default'];
						}
						else{
							$value = '';
						}
						if($fields[$i]['Field'] != 'password'){
							$formfield = '<input type="text" size="40" value="'.stripslashes($value).'" class="textfield" name="'.$fields[$i]['Field'].'">';
						}
						else{
							$formfield = '<input type="text" size="40" value="" class="textfield" name="'.$fields[$i]['Field'].'">';
						}
						$form_buffer .= '<tr>
    					<td width="181" align="left" style="padding-left:15px;">'.str_replace('_',' ',$fields[$i]['Field']).'</td>
    					<td width="387" align="left" style="padding:5px;">'.$formfield.'</td></tr>';
					}
				}
				$i++;
				}
			$form_buffer .= '<tr><td colspan="2" style="padding:5px;" align="center">
			<input name="Submit" type="submit" class="button" value="Submit" /></td>
	  		</tr><tr><td colspan="2" align="center">&nbsp;</td></tr></table></div></form>';
		}
		else{
			$form_buffer = 'Error, the specified table:'.$table.' contains no columns';
		}
		return $form_buffer;
	}

	public function insert_data($table,$fdata = null){
		$fields = $GLOBALS['database']->fetch_data("SHOW COLUMNS FROM `".$table."` ");
		$i = 0;
		$preset = 0;
		while($i < count($fields)){
			if(isset($_POST[$fields[$i]['Field']])){
				if($_POST[$fields[$i]['Field']] != '' && $_POST[$fields[$i]['Field']] != 'NULL'){
					if($fields[$i]['Field'] == 'password'){
						$_POST[$fields[$i]['Field']] = md5($_POST[$fields[$i]['Field']]);
					}
					if($preset == 0){
						$data .= "`".$fields[$i]['Field']."`";
							$values .= "'".addslashes($_POST[$fields[$i]['Field']])."'";
					}
					else{
						$data .= ", `".$fields[$i]['Field']."`";
						$values .= ", '".addslashes($_POST[$fields[$i]['Field']])."'";
					}
					$preset = 1;
				}
			}
			elseif($fdata[$fields[$i]['Field']] != ''){
				if($preset == 0){
				    $data .= "`".$fields[$i]['Field']."`";
				    $values .= "'".addslashes($fdata[$fields[$i]['Field']])."'";
				}
				else{
					$data .= ", `".$fields[$i]['Field']."`";
					$values .= ", '".addslashes($fdata[$fields[$i]['Field']])."'";
				}
				$preset = 1;
			}
			$i++;
		}
		$query = "INSERT INTO `".$table."` (".$data.") VALUES(".$values.")";
		/*				DEBUG ECHO			*/
		//echo $query;
		if($GLOBALS['database']->execute_query($query)){
			return true;
		}
		else{
			return false;
		}
	}

	public function update_data($table,$key,$key_value){
		$fields = $GLOBALS['database']->fetch_data("SHOW COLUMNS FROM `".$table."` ");
		$i = 0;
		$preset = 0;
		while($i < count($fields)){
			if(isset($_POST[$fields[$i]['Field']])){
				if($_POST[$fields[$i]['Field']] != ''){
					if($fields[$i]['Field'] == 'password'){
						$_POST[$fields[$i]['Field']] = md5($_POST[$fields[$i]['Field']]);
					}
					if($preset == 0){
						//$data = "`".$fields[$i]['Field']."`"." = '".addslashes($_POST[$fields[$i]['Field']])."'";
                        $data = "`".$fields[$i]['Field']."`"." = '".$_POST[$fields[$i]['Field']]."'";
					}
					else{
						//$data .= ", `".$fields[$i]['Field']."`"." = '".addslashes($_POST[$fields[$i]['Field']])."'";
                        $data .= ", `".$fields[$i]['Field']."`"." = '".$_POST[$fields[$i]['Field']]."'";
					}
					$preset = 1;				    
                }
                elseif($fields[$i]['Null'] == 'YES'){
                    if($preset == 0){
                        $data = "`".$fields[$i]['Field']."`"." = NULL ";
                    }
                    else{
                        $data .= ", `".$fields[$i]['Field']."`"." = NULL ";
                    }
                    $preset = 1;
                }
			}
			$i++;
		}
		$query = "UPDATE`".$table."` SET ".$data." WHERE `".$key."` = '".$key_value."' LIMIT 1";
		if($GLOBALS['database']->execute_query($query) !== false){
			return true;
		}
		else{
			return false;
		}
	}
    
    public function check_data($table,$key,$key_value,$skip_fields = null){
        $old_data = $GLOBALS['database']->fetch_data("SELECT * FROM `".$table."` WHERE `".$key."` = '".$key_value."' LIMIT 1");
        $new_data = $_POST;
        $fields = $GLOBALS['database']->fetch_data("SHOW COLUMNS FROM `".$table."`");
        $output = '';
        $i = 0;
        while($i < count($fields)){
            if($fields[$i]['Field'] != $key){
                if(!in_array($fields[$i]['Field'],$skip_fields)){
                    if($fields[$i]['Field'] != 'password' || $new_data['password'] != ''){
                        if($old_data[0][$fields[$i]['Field']] != $new_data[$fields[$i]['Field']]){
                            $output .= 'Altered Field: "'.$fields[$i]['Field'].'" old value: "'.$old_data[0][$fields[$i]['Field']].'" new value: "'.$new_data[$fields[$i]['Field']].'" <br />';
                        }
                    }
                }
            }
            $i++;
        }
        if($output == ''){
            $output = 'No fields altered';   
        }
        return $output;
    }
    
}
?>