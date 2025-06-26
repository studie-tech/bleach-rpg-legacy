<?php

class userpref{
	var $user;
	var $output_buffer;
	
	var $MAX_AVATAR_SIZE = 25600;
	
	function userpref(){
		$this->user = $GLOBALS['userdata'];
		if(!isset($_POST['Submit'])){
			$this->main_screen();
			if($_GET['act'] == 'password'){
				$this->change_password();
			}
			elseif($_GET['act'] == 'avatar'){
				$this->change_avatar();
			}
			elseif($_GET['act'] == 'delete'){
				if($this->user[0]['deletion_timer'] == 0){
					$this->delete_form();
				}
				elseif($this->user[0]['deletion_timer'] > 0 && $this->user[0]['deletion_timer'] > time()){
					$this->undelete_form();
				}
				else{
					$this->account_deletion();
				}
			}
			elseif($_GET['act'] == 'main'){
				$this->preferences();
			}
            elseif($_GET['act'] == 'layout'){
                $this->layout_form();
            }
			elseif($_GET['act'] == 'battle'){
				$this->battle_form();
			}
            elseif($_GET['act'] == 'blacklist'){
                $this->blacklist_screen();   
            }
		}
		elseif($_POST['Submit'] == 'Submit'){
			$this->change_settings();
		}
		elseif($_POST['Submit'] == 'Alter'){
			$this->change_Bsettings();
		}
		elseif($_POST['Submit'] == 'Upload'){
			$this->do_avatar_change();
		}
		elseif($_POST['Submit'] == 'Change'){
			$this->do_change_password();
		}
		elseif($_POST['Submit'] == 'Delete'){
			$this->set_delete_flag();
		}
		elseif($_POST['Submit'] == 'Cancel'){
			$this->unset_delete_flag();
		}
		elseif($_POST['Submit'] == 'Save'){
			$this->alter_nindo();
		}
		elseif($_POST['Submit'] == 'Yes'){
			$this->leave_village();
		}
		elseif($_POST['Submit'] == 'Change layout'){
			$this->alter_layout();
		}
        elseif($_POST['Submit'] == 'Remove selected'){
            $this->update_lists();
        }
        elseif($_POST['Submit'] == 'Add user'){
            $this->list_add_user();
        }
        elseif($_POST['Submit'] == 'Save setting'){
            $this->list_save_setting();
        }
        $this->return_stream();
	}
	
	function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer.'<br /><br />');
	}
	
	//	Account deletions
	
	function account_deletion(){
		$this->output_buffer .= '<div align="center">
  		<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
    	<tr>
      	<td colspan="2" align="center" style="font-weight:bolder;padding:2px;">Delete your account </td>
    	</tr>
    	<tr>
    	  <td colspan="2" align="center" style="padding:5px;">Your account was flagged for deletion over 7 days ago, you can now no longer cancel the process<br />
    	  Your account will be deleted in the next sweep.</td>
    	  </tr>
		<tr>
      	<td colspan="2" align="center">&nbsp;</td>
    	</tr></table>
	  	</div>';
	}
	
	function undelete_form(){
		$time = $this->user[0]['deletion_timer'] - time();
		$this->output_buffer .= '<div align="center">
	  	<form name="form1" method="post" action="">
  		<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
    	<tr>
      	<td colspan="2" align="center" style="font-weight:bolder;padding:2px;">Delete your account </td>
    	</tr>
    	<tr>
    	  <td colspan="2" align="center" style="padding:5px;">Your account is currently flagged for deletion<br />
    	  you have <b>'.functions::convert_time($time).'</b> left to cancel this process.</td>
    	  </tr>
    	<tr>
      	<td width="45%" align="right" style="padding-right:10px;">Password: </td>
      	<td width="55%" align="left" style="padding-bottom:5px;"><input style="height:20px;" type="password" name="old_pass"></td>
    	</tr><tr>
      	<td colspan="2" align="center"><input type="submit" name="Submit" value="Cancel"></td>
    	</tr><tr>
      	<td colspan="2" align="center">&nbsp;</td>
    	</tr></table>
	  	</form></div>';
	}
	
	function set_delete_flag(){
		if(md5($_POST['old_pass']) == $this->user[0]['password']){
			$query = "UPDATE `users_timer` SET `deletion_timer` = '".(time() + 604800)."' WHERE `userid` = '".$_SESSION['uid']."' LIMIT 1";
            
            // Update Memcache
            $GLOBALS['userdata'][0]['deletion_timer'] = "".(time() + 604800)."";  
            $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
            
			$GLOBALS['database']->execute_query($query);
			$this->output_buffer .= '<div align="center">Your account has now been flagged for deletion, you have 7 days to cancel this procedure <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
		else{
			$this->output_buffer .= '<div align="center">Your password is incorrect <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
	function unset_delete_flag(){
		if(md5($_POST['old_pass']) == $this->user[0]['password']){
			$query = "UPDATE `users_timer` SET `deletion_timer` = '0' WHERE `userid` = '".$_SESSION['uid']."' LIMIT 1";
			$GLOBALS['database']->execute_query($query);
            
            // Update Memcache
            $GLOBALS['userdata'][0]['deletion_timer'] = "0";  
            $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
            
			$this->output_buffer .= '<div align="center">Your account is no longer flagged for deletion <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
		else{
			$this->output_buffer .= '<div align="center">Your password is incorrect <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
	function delete_form(){
		$this->output_buffer .= '<div align="center">
	  	<form name="form1" method="post" action="">
  		<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
    	<tr>
      	<td colspan="2" align="center" style="border-top:none;padding:2px;" class="subHeader">Delete your account </td>
    	</tr>
    	<tr>
    	  <td colspan="2" align="center" style="color:red;padding:5px;">This will flag your account for deletion, 7 days after this your account will be deleted<br>
    	    without any way to retrieve it. You may cancel this at any time before the 7 days are up. </td>
    	  </tr>
    	<tr>
      	<td width="45%" align="right" style="padding-right:10px;">Password: </td>
      	<td width="55%" align="left" style="padding-bottom:5px;"><input style="height:20px;" type="password" name="old_pass"></td>
    	</tr><tr>
      	<td colspan="2" align="center"><input type="submit" name="Submit" value="Delete"></td>
    	</tr><tr>
      	<td colspan="2" align="center">&nbsp;</td>
    	</tr></table>
	  	</form></div>';
	}
		
	//	Password settings
	function do_change_password(){
		if(md5($_POST['old_pass']) == $this->user[0]['password']){
			if($_POST['new_pass'] == $_POST['new_pass_conf'] && $_POST['new_pass'] != ''){
				$new_pass = md5($_POST['new_pass']);
				$query = "UPDATE `users` SET `password` = '".$new_pass."'  WHERE `id` = '".$_SESSION['uid']."' LIMIT 1";
                
                // Update Memcache
                $GLOBALS['userdata'][0]['password'] = "".$new_pass."";
                $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
                
				$GLOBALS['database']->execute_query($query);
				$this->output_buffer .= '<div align="center">Your password has been modified <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
			else{
				$this->output_buffer .= '<div align="center">Your passwords do not match! <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
		}
		else{
			$this->output_buffer .= '<div align="center">Your old password is incorrect <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
	function change_password(){
		$this->output_buffer .= '<div align="center">
	  	<form name="form1" method="post" action="">
  		<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
    	<tr>
      	<td colspan="2" align="center" style="border-top:none;padding:2px;" class="subHeader">Edit password: </td>
    	</tr><tr>
      	<td width="45%" align="right" style="padding-right:10px;padding-top:5px;">Old password: </td>
      	<td width="55%" align="left" style="padding-bottom:5px;padding-top:5px;"><input style="height:20px;" type="password" name="old_pass"></td>
    	</tr><tr>
      	<td align="right" style="padding-right:10px;">New password: </td>
      	<td align="left" style="padding-bottom:5px;"><input style="height:20px;" type="password" name="new_pass"></td>
    	</tr>
    	<tr>
      	<td align="right" style="padding-right:10px;">Confirm new password: </td>
      	<td align="left" style="padding-bottom:5px;"><input style="height:20px;" type="password" name="new_pass_conf"></td>
    	</tr><tr>
      	<td colspan="2" align="center"><input type="submit" name="Submit" value="Change"></td>
    	</tr><tr>
      	<td colspan="2" align="center">&nbsp;</td>
    	</tr></table></form></div>';
	}
		
	//	Avatar settings
	function change_avatar(){
		if($_SESSION['user_rank'] != 'Member'){
			$maxdim = 150;
		}
		else{
			$maxdim = 100;
		}
		if(file_exists('./images/avatars/'.$_SESSION['uid'].'.gif')){
			$image = './images/avatars/'.$_SESSION['uid'].'.gif';
		}
		else{
			$image = './images/default_avatar.gif';
		}
		$this->output_buffer .= '<div align="center">
  		<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
    	<tr>
    	  <td align="center" style="border-top:none;padding:2px;" class="subHeader">Edit avatar: </td>
    	</tr>
    	<tr>
      	<td align="center" valign="top" style="padding:15px;"><img style="border:1px solid #000000;" src="'.$image.'"></td>
    	</tr>
    	<tr>
      	<td align="center">Maximum dimensions: '.$maxdim.' x '.$maxdim.' pixels, 25kb </td>
    	</tr>
    	<tr>
      	<td align="center" style="padding:5px;"><form action="" method="post" enctype="multipart/form-data" name="form1">
	        <input type="file" name="userfile">&nbsp;<input type="submit" name="Submit" value="Upload">
         </form></td>
    	</tr>
    	</table></div>';
	}
	
	function do_avatar_change(){
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		/*				Set Max dimensions			*/
		if($_SESSION['user_rank'] != 'Member'){
			$maxdim = 150;
		}
		else{
			$maxdim = 100;
		}
        if($_SESSION['user_rank'] != 'Member'){
            $maxsize = 102400;
        }
        else{
            $maxsize = 25600;    
        }
		if($_FILES['userfile']['size'] > 0){
            if($_FILES['userfile']['size'] <= $maxsize){
			    $destination = './images/avatars/';
			    $filename = explode('.',strtolower($_FILES['userfile']['name']));
			    $filename[0] = $_SESSION['uid'];
			    if($_FILES['userfile']['type'] == 'image/gif'){
				    $imgdata = getimagesize($_FILES['userfile']['tmp_name']);
				    if($imgdata[0] <= $maxdim && $imgdata[1] <= $maxdim){
					    if(file_exists('./images/avatars/'.$_SESSION['uid'].'.gif')){
						    unlink('./images/avatars/'.$_SESSION['uid'].'.gif');
					    }
					    if(move_uploaded_file($_FILES['userfile']['tmp_name'],$destination.$filename[0].'.'.$filename[1])){	
						    $this->output_buffer .= '<div align="center">Your new avatar was uploaded successfully <br /><br /><span style="font-size:14px;font-weight:bold;color:darkred;">If your new avatar does not show up press CTRL + F5.</span><br /><a href="?id='.$_GET['id'].'">Return</a></div>';
					    }
					    else{
						    $this->output_buffer .= '<div align="center">An error occured while moving the file <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
					    }
				    }
				    else{
					    $this->output_buffer .= '<div align="center">The image dimensions exceed '.$maxdim.' x '.$maxdim.'<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
				    }
			    }
			    else{
				    $this->output_buffer .= '<div align="center">Invalid file type or corrupt image data <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			    }
            }
            else{
                $this->output_buffer .= '<div align="center">The filesize exceeds '.($maxsize / 1024).' kb<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
            }
		}
		else{
			$this->output_buffer .= '<div align="center">You did not upload a file! <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
	//	Main settings
	function preferences(){
		/*				Echo main screen			*/
		$this->output_buffer .=  '
            <div align="center"><form id="form1" name="form1" method="post" action="">
		        <table width="95%" class="table" cellspacing="0" cellpadding="0">
  		            <tr>
                        <td colspan="4" align="center" class="subHeader">
                            User preferences: 
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" style="padding-left:15px;">
                            Receive PM\'s
                        </td>
    	                <td width="23%">Yes:';
   	 	if($this->user[0]['pm_block'] == '1'){
    		$this->output_buffer .=  '<input name="pm_block" type="radio" value="0" />';
   		 }
    	else{
    		$this->output_buffer .=  '<input name="pm_block" type="radio" checked="checked" value="0" />';
    	}
    	$this->output_buffer .=  '</td><td colspan="2">No:';
    	if($this->user[0]['pm_block'] == '1'){
    		$this->output_buffer .=  '<input name="pm_block" type="radio" checked="checked" value="1" />';
    	}
   	 	else{
   		 	$this->output_buffer .=  '<input name="pm_block" type="radio" value="1" />';
    	}
  		$this->output_buffer .=  '</td></tr><tr><td style="padding-left:15px;">Lock account on 3 unsuccessful login attempts</td><td>Yes:';
        /*              Account lock on attempts                */
        if($this->user[0]['lock'] == '1'){
            $this->output_buffer .=  '<input name="account_lock" type="radio" checked="checked" value="1" />';
        }
        else{
            $this->output_buffer .=  '<input name="account_lock" type="radio" value="1" />';
        }
        $this->output_buffer .=  '</td><td width="11%" style="border-right:1px solid #000000;">No:';
        if($this->user[0]['lock'] == '0'){
            $this->output_buffer .=  '<input name="account_lock" type="radio" checked="checked" value="0" />';
        }
        else{
            $this->output_buffer .=  '<input name="account_lock" type="radio" value="0" />';
        } 
        /*              Battle PMs                */
        $this->output_buffer .=  '</td></tr><tr><td style="padding-left:15px;">Receive Battle PMs</td><td>Yes:';
        if($this->user[0]['bpm_block'] == '1'){
            $this->output_buffer .=  '<input name="bpm_block" type="radio" checked="checked" value="1" />';
        }
        else{
            $this->output_buffer .=  '<input name="bpm_block" type="radio" value="1" />';
        }
        $this->output_buffer .=  '</td><td width="11%" style="border-right:1px solid #000000;">No:';
        if($this->user[0]['bpm_block'] == '0'){
            $this->output_buffer .=  '<input name="bpm_block" type="radio" checked="checked" value="0" />';
        }
        else{
            $this->output_buffer .=  '<input name="bpm_block" type="radio" value="0" />';
        } 

  		$this->output_buffer .=  '<tr><td>&nbsp;</td><td>&nbsp;</td><td colspan="2">&nbsp;</td></tr><tr>
    	<td colspan="4" align="center" style="padding:5px;"><input type="submit" class="button" name="Submit" value="Submit" /></td>
    	</tr></table></form></div>';
	}
	
	function change_settings(){
		/*				Check and upload settings				*/
		/*			Check PM block		*/
		$temp_query = "UPDATE `users` SET";
		if($_POST['pm_block'] == '0' || $_POST['pm_block'] == '1'){
			$temp_query .= " `pm_block` = '".$_POST['pm_block']."'";
            if(is_numeric($_POST['account_lock'])){
                $temp_query .= ", `lock` = '".$_POST['account_lock']."'";
            }
            if(is_numeric($_POST['bpm_block'])){
                $temp_query .= ", `bpm_block` = '".$_POST['bpm_block']."'";
            }

			$temp_query .= " WHERE `id` = '".$_SESSION['uid']."' LIMIT 1";
			$GLOBALS['database']->execute_query($temp_query);
            
            // Memcache delete
            $GLOBALS['cache']->delete("resu:".$_SESSION['uid']);
            
			$this->output_buffer .=  '<center>Your preferences have been updated<br /><a href="?id='.$_GET['id'].'">Return</a></center>';
		}
		else{
			$this->output_buffer .=  '<center><b>Form error, please try again</b></center>';
		}
	}
	
	//	Change layout
	
	function battle_form(){
		/*				Echo main screen			*/
		$this->output_buffer .=  '<div align="center">
		<form id="form1" name="form1" method="post" action="">
		<table width="95%" class="table" cellspacing="0" cellpadding="0">
  		<tr><td colspan="6" align="center" class="subHeader">Edit Battle Settings</td>
  		</tr>';
  		
  		/*	Battle options - Copy/Paste this one to next function if edited	*/
  		$variables = array(array("Release Shikai",25,50,75,100), array("Release Bankai",25,50,75,100), array("Use HP item",10,30,50,70), array("Flee Battle",25,50,75,100));
  		
  		/*	Retrieve battle data	*/
  		$this->battle_setup = functions::get_options($_SESSION['uid']); 
  		
  		/*	Run through all battle options	*/
  		foreach ($variables as $value){
  		 	/*	Define what should be shown	*/
  		 	if($this->user[0]['race'] == "Hollow"){
				switch($value['0']){
					case "Release Shikai":  $name = "Release ability";; break;
					case "Use HP item": 	$name = "Use HP item"; break;
					case "Release Bankai":  $name = "Release Resurrection"; $pass = 0; break;
					case "Flee Battle": 	$name = "Flee Battle"; $pass = 0; break;
				}
			}
			else{
				$name = $value['0'];
			}
			
			/*	Show what should be shown */
			if($pass !== 1){
			 	$this->output_buffer .= '<tr><td style="padding-left:15px;"><b>'.$name.'</b></td>';
 	       			/*		Create passable name for all options 	*/
 	       			$value['0'] = strtolower(str_replace(" ", "_", "".$value['0'].""));
					/*		Never	*/
					$set = $this->battle_setup[0][''.$value['0'].''];
	    			$this->output_buffer .=  '</td><td><i>Never:</i>';
			    	if($set == '0'){
			    		$this->output_buffer .=  '<input name="'.$value['0'].'" type="radio" checked="checked" value="5" />';
			    	}
			   	 	else{
			   		 	$this->output_buffer .=  '<input name="'.$value['0'].'" type="radio" value="5" />';
			    	}
			    	/*		Option 1	*/
					$this->output_buffer .= '<td><i>'.$value['1'].'% Health</i>';
					if($set == '1'){
					 	$this->output_buffer .=  '<input name="'.$value['0'].'" type="radio" checked="checked" value="1" />';
					}
					else{
			    		$this->output_buffer .=  '<input name="'.$value['0'].'" type="radio" value="1" />';
	    			}
	    			/*		Option 2	*/
	    			$this->output_buffer .=  '</td><td><i>'.$value['2'].'% Health:</i>';
			    	if($set == '2'){
			    		$this->output_buffer .=  '<input name="'.$value['0'].'" type="radio" checked="checked" value="2" />';
			    	}
			   	 	else{
			   		 	$this->output_buffer .=  '<input name="'.$value['0'].'" type="radio" value="2" />';
			    	}
			    	/*		Option 3	*/
	    			$this->output_buffer .=  '</td><td><i>'.$value['3'].'% Health:</i>';
			    	if($set == '3'){
			    		$this->output_buffer .=  '<input name="'.$value['0'].'" type="radio" checked="checked" value="3" />';
			    	}
			   	 	else{
			   		 	$this->output_buffer .=  '<input name="'.$value['0'].'" type="radio" value="3" />';
			    	}
			    	/*		Option 4	*/
	    			$this->output_buffer .=  '</td><td><i>'.$value['4'].'% Health:</i>';
			    	if($set == '4'){
			    		$this->output_buffer .=  '<input name="'.$value['0'].'" type="radio" checked="checked" value="4" />';
			    	}
			   	 	else{
			   		 	$this->output_buffer .=  '<input name="'.$value['0'].'" type="radio" value="4" />';
			    	}
			}   	
	  		$this->output_buffer .=  '</td></tr>';
	    }
		$this->output_buffer .=  '<tr><td colspan="6" align="center" style="padding:5px;"><input type="submit" class="button" name="Submit" value="Alter" /></td>
						    	</tr></table></form></div>';		
	}
	
	function change_Bsettings(){
	 	/*	Battle Variables, copy/paste from previous function		*/
		$variables = array(array("Release Shikai",25,50,75,100), array("Release Bankai",25,50,75,100), array("Use HP item",10,30,50,70), array("Flee Battle",25,50,75,100));
		/*	Construct temporary query	*/
		$temp_query = "UPDATE `battle_options` SET";
		/*	Loop through all variables	*/
		foreach ($variables as $value){
		 	/*	Construct the names of the passed variables	*/
			$value['0'] = strtolower(str_replace(" ", "_", "".$value['0'].""));
			
			/*	Check that a valid option has been chosen	*/
			if($_POST[''.$value['0'].'']){
				if(strstr("12345", $_POST[''.$value['0'].''])){
				 	if($_POST[''.$value['0'].''] == 5){$_POST[''.$value['0'].''] = 0;}
				 	/*	Update temporary query	*/
					$temp_query .= " `".$value['0']."` = ".$_POST[''.$value['0'].''].", ";
				}
				else{
					$this->output_buffer .=  '<center><b>Form error, please try again</b></center>';
					break;
				}
			}
		}
		/*	End the temporary query	*/
		$temp_query .= "`uid` = `uid` WHERE `uid` = '".$_SESSION['uid']."' LIMIT 1";
		//echo"$temp_query";
		$GLOBALS['database']->execute_query($temp_query);
        $GLOBALS['cache']->delete("elttab:".$_SESSION['uid']);

		$this->output_buffer .=  '<center>Your battle preferences have been updated<br /><a href="?id='.$_GET['id'].'">Return</a></center>';
	}
	
	//    Change layout
    
    function layout_form(){
        $this->output_buffer .= '<div align="center"><br />
          <form name="form1" method="post" action="">
          <table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
        <tr><td colspan="3" align="center" style="border-top:none;" class="subHeader">Change layout</td>
        </tr><tr><td colspan="3" align="center">Current layout: '.str_replace('_',' ',$_SESSION['layout']).' </td></tr>
        <tr><td colspan="3" align="center"><select name="layout">';
        $dir = opendir('./files');
        readdir($dir);
        while (false !== ($file = readdir($dir))) {
               if(is_dir('./files/'.$file) && $file != '..' && $file != '.'){
                   $this->output_buffer .= '<option>'.str_replace('_',' ',str_replace('layout_','',$file)).'</option>';
               }
           }
        closedir($dir);
        $this->output_buffer .= '</select></td></tr><tr>
          <td colspan="3" align="center" style="padding:3px;"><input type="submit" name="Submit" value="Change layout"></td>
          </tr></table></form><br /><br /></div>';
    }
    
    function alter_layout(){
        if(isset($_POST['layout'])){
            if(is_dir('./files/layout_'.str_replace(' ','_',trim($_POST['layout'])))){
                $GLOBALS['database']->execute_query("UPDATE `users` SET `layout` = '".str_replace(' ','_',trim($_POST['layout']))."' WHERE `id` = '".$_SESSION['uid']."' LIMIT 1");
                $_SESSION['layout'] = str_replace(' ','_',trim($_POST['layout']));
                $this->output_buffer .= '<div align="center">The layout has been changed.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
            }
            else{
                //    Invalid layout?
                $this->output_buffer .= '<div align="center">The layout you selected does not exist / is corrupted.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
            }
        }
        else{
            //    Layout not set
            $this->output_buffer .= '<div align="center">There was no layout selected.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
        }
    }
    
    
    
	//	Main screen:
	
	function main_screen(){
		$this->output_buffer .= '<div align="center">
  		<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0"><tr>
      	<td colspan="3" align="center" style="border-top:none;" class="subHeader">Preferences</td>
    	</tr><tr><td align="center"><a href="?id='.$_GET['id'].'&act=main">Preferences</a></td>
      	<td align="center"><a href="?id='.$_GET['id'].'&act=password">Password</a></td>
      	<td align="center"><a href="?id='.$_GET['id'].'&act=delete">Delete account</a></td>
      	</tr>
        <tr>
            <td align="center" width="33%"><a href="?id='.$_GET['id'].'&act=avatar">Avatar</a></td>
              <td align="center" width="33%"><a href="?id='.$_GET['id'].'&act=blacklist">Black/Whitelist</a></td>
              <td align="center" width="33%"><a href="?id='.$_GET['id'].'&act=battle"><b>Battle Settings</b></a></td>
        </tr>
        <tr>
            <td align="center" width="33%"></td>
              <td align="center"><a href="?id='.$_GET['id'].'&act=layout">Change layout</a></td>
              <td align="center" width="33%"></td>
        </tr>
        <tr>
    	  <td align="center"></td>
    	  <td align="center">';
        
        $this->output_buffer .= '</td>
    	  <td align="center"></td>
  	  	</tr></table>
  		<br /></div>';
	}

    //  PM Settings
    function blacklist_screen(){
        $settings = $GLOBALS['database']->fetch_data("SELECT `setting` FROM `users_pm_settings` WHERE `userid` = '".$_SESSION['uid']."' LIMIT 1");
        $this->output_buffer .= '<div align="center">
              <form name="form1" method="post" action="">
              <table width="95%" cellspacing="0" cellpadding="0" class="table">
                <tr>
                  <td colspan="2" align="center" class="subHeader">Black / whitelist</td>
                </tr>
                <tr>
                  <td width="36%" style="padding-top:2px;">Blacklist:</td>
                  <td width="64%" style="padding:2px;"><select name="blacklist[]" size="5" multiple id="blacklist" style="width:200px;">';
        //        Parse people on the blacklist
        $blacklisted = $GLOBALS['database']->fetch_data("SELECT `username` , `id` FROM `users` , `users_pm_settings` WHERE INSTR( `blacklist` , CONCAT( ';', `id` , ';' ) ) AND `userid` = '".$_SESSION['uid']."' ORDER BY `username` ASC ");
        $this->output_buffer .= '<option selected value="none"><i>(none)</i></option>';
        $i = 0;
        if($blacklisted != '0 rows'){
            while($i < count($blacklisted)){
                if($blacklisted[$i] != ''){
                    $this->output_buffer .= '<option value="'.$blacklisted[$i]['id'].'">'.$blacklisted[$i]['username'].'</option>';
                }
                $i++;
            }
        }
        $this->output_buffer .= '</select>
                  </td>
                </tr>
                <tr>
                  <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                  <td>Whitelist:</td>
                  <td style="padding:2px;"><select name="whitelist[]" size="5" multiple id="blacklist2" style="width:200px;">';
        //        Parse people on the whitelist
        $whitelisted = $GLOBALS['database']->fetch_data("SELECT `username` , `id` FROM `users` , `users_pm_settings` WHERE INSTR( `whitelist` , CONCAT( ';', `id` , ';' ) ) AND `userid` = '".$_SESSION['uid']."' ORDER BY `username` ASC ");
        $i = 0;
        $this->output_buffer .= '<option selected value="none"><i>(none)</i></option>';
        if($whitelisted != '0 rows'){
            while($i < count($whitelisted)){
                if($whitelisted[$i] != ''){
                    $this->output_buffer .= '<option value="'.$whitelisted[$i]['id'].'">'.$whitelisted[$i]['username'].'</option>';
                }
                $i++;
            }
        }
        $this->output_buffer .= '
                  </select></td>
                </tr>
                <tr>
                  <td colspan="2" align="center"><input type="submit" name="Submit" id="Submit" value="Remove selected"></td>
                </tr>
                <tr>
                  <td colspan="2">&nbsp;</td>
                </tr>
              </table></form>
              <form name="form2" method="post" action="">
                <table width="95%" class="table" cellspacing="0" cellpadding="0">
                  <tr>
                    <td class="subHeader" align="center">Add user</td>
                  </tr>
                  <tr>
                    <td style="padding-top:2px;" align="center">Username: 
                    <input type="text" name="username" id="textfield"></td>
                  </tr>
                  <tr>
                    <td style="padding-top:2px;" align="center">Add user to the: 
                      <label>
                          <input type="radio" name="listtype" value="black" id="listtype_0">
                          Blacklist</label>
                        <label>
                          <input type="radio" name="listtype" value="white" id="listtype_1">
                          Whitelist</label>
                    </td>
                  </tr>
                  <tr>
                    <td style="padding-top:5px;padding-bottom:2px;" align="center"><input type="submit" name="Submit" value="Add user"></td>
                  </tr>
                </table>
              </form>
              <form name="form3" method="post" action="">
                <table width="95%" class="table" cellspacing="0" cellpadding="0">
                  <tr>
                    <td colspan="2" align="center" class="subHeader">Settings</td>
                  </tr>
                  <tr>
                    <td width="32%" align="center">PM setting:</td>
                    <td width="68%" align="center" style="padding-top:5px;"><select name="setting" id="select">';
        //          parse blackllist setting
        if($settings[0]['setting'] == 'white_only'){
            $this->output_buffer .='<option selected value="white_only">Recieve PM\'s only from people on my whitelist</option>
            <option value="block_black">Block PM\'s from people on my blacklist only</option>
            <option value="off">Ignore the black / whitelist completely</option>';
        }
        elseif($settings[0]['setting'] == 'block_black'){
            $this->output_buffer .='<option selected value="white_only">Recieve PM\'s only from people on my whitelist</option>
            <option selected value="block_black">Block PM\'s from people on my blacklist only</option>
            <option value="off">Ignore the black / whitelist completely</option>';
        }
        elseif($settings[0]['setting'] == 'off'){
            $this->output_buffer .='<option selected value="white_only">Recieve PM\'s only from people on my whitelist</option>
            <option value="block_black">Block PM\'s from people on my blacklist only</option>
            <option selected value="off">Ignore the black / whitelist completely</option>';
        }
        $this->output_buffer .= '</select>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" align="center" style="padding:3px;"><input type="submit" name="Submit" value="Save setting"></td>
                  </tr>
                </table>
              </form>
              <br /></div>';   
    }
    
    function list_add_user(){
        $settings = $GLOBALS['database']->fetch_data("SELECT * FROM `users_pm_settings` WHERE `userid` = '".$_SESSION['uid']."' LIMIT 1");
        if(isset($_POST['listtype'])){
            $user = $GLOBALS['database']->fetch_data("SELECT `id`,`user_rank`,`username` FROM `users` WHERE `username` LIKE '".addslashes($_POST['username'])."' LIMIT 1");
            if($user != '0 rows'){
                if($user[0]['user_rank'] == 'Member' || $user[0]['user_rank'] == 'Paid'){
                    //  Check if user is already white or blacklisted
                    if(!stristr($settings[0]['whitelist'],';'.$user[0]['id'].';') && !stristr($settings[0]['blacklist'],';'.$user[0]['id'].';')){
                        //  Check white or blacklist
                        if($_POST['listtype'] == 'white'){
                            $column = 'whitelist';
                        }
                        else{
                            $column = 'blacklist';
                        }
                                          
                        if($GLOBALS['database']->execute_query("UPDATE `users_pm_settings` SET `".$column."` = CONCAT(`".$column."`,'".$user[0]['id']."',';') WHERE `userid` = '".$_SESSION['uid']."' LIMIT 1")){
                            $this->output_buffer .= '<div align="center">'.$user[0]['username'].' has been added to your '.$column.'<br /><a href="?id='.$_GET['id'].'&act='.$_GET['act'].'">Return</a></div>';
                        }
                        else{
                            $this->output_buffer .= '<div align="center">An error occured while updating your '.$column.'<br /><a href="?id='.$_GET['id'].'&act='.$_GET['act'].'">Return</a></div>';
                        }
                    }
                    else{
                        $this->output_buffer .= '<div align="center">This user is already on your white or blacklist.<br /><a href="?id='.$_GET['id'].'&act='.$_GET['act'].'">Return</a></div>';
                    }
                }
                else{
                    $this->output_buffer .= '<div align="center">You cannot black / whitelist admins or mods<br /><a href="?id='.$_GET['id'].'&act='.$_GET['act'].'">Return</a></div>';
                }
            }
            else{
                $this->output_buffer .= '<div align="center">The user you specified does not exist.<br /><a href="?id='.$_GET['id'].'&act='.$_GET['act'].'">Return</a></div>';
            }
        }
        else{
            $this->output_buffer .= '<div align="center">You did not specify which list the user has to be added to.<br /><a href="?id='.$_GET['id'].'&act='.$_GET['act'].'">Return</a></div>';   
        }
    }
    
    function update_lists(){
        //  Fetch string
        $settings = $GLOBALS['database']->fetch_data("SELECT * FROM `users_pm_settings` WHERE `userid` = '".$_SESSION['uid']."' LIMIT 1");
        
        //  Run through whitelist
        $i = 0;
        $whitelist = $settings[0]['whitelist'];
        while($i < count($_POST['whitelist'])){
            if($_POST['whitelist'][$i] != 'none'){
                $whitelist = str_replace(';'.$_POST['whitelist'][$i].';', ';', $whitelist);
            }
            $i++;
        }

        
        //  Run through black
        $i = 0;
        $blacklist = $settings[0]['blacklist'];
        while($i < count($_POST['blacklist'])){
            if($_POST['blacklist'][$i] != 'none'){
                $blacklist = str_replace(';'.$_POST['blacklist'][$i].';', ';', $blacklist);
            }
            $i++;   
        }
        
        //  UPDATE white / blacklists in the database
        if($GLOBALS['database']->execute_query("UPDATE `users_pm_settings` SET `whitelist` = '".$whitelist."', `blacklist`= '".$blacklist."' WHERE `userid` = '".$_SESSION['uid']."' LIMIT 1")){
               $this->output_buffer .= '<div align="center">Your black and whitelist have been updated.<br /><a href="?id='.$_GET['id'].'&act='.$_GET['act'].'">Return</a></div>';
        }
        else{
            $this->output_buffer .= '<div align="center">An error occured updating your black/whitelist.<br /><a href="?id='.$_GET['id'].'&act='.$_GET['act'].'">Return</a></div>';
        }
    }
    
    function list_save_setting(){
        if(isset($_POST['setting'])){
            if($_POST['setting'] == 'off' || $_POST['setting'] == 'white_only' || $_POST['setting'] == 'block_black'){
                if($GLOBALS['database']->execute_query("UPDATE `users_pm_settings` SET `setting` = '".$_POST['setting']."' WHERE `userid` = '".$_SESSION['uid']."' LIMIT 1")){
                    $this->output_buffer .= '<div align="center">The blacklist setting was updated.<br /><a href="?id='.$_GET['id'].'&act='.$_GET['act'].'">Return</a></div>';
                }
                else{
                    $this->output_buffer .= '<div align="center">An error occured while updating the setting.<br /><a href="?id='.$_GET['id'].'&act='.$_GET['act'].'">Return</a></div>';
                }
            }
            else{
                $this->output_buffer .= '<div align="center">Invalid setting was specified.<br /><a href="?id='.$_GET['id'].'&act='.$_GET['act'].'">Return</a></div>';
            }   
        }
        else{
            $this->output_buffer .= '<div align="center">No setting was specified.<br /><a href="?id='.$_GET['id'].'&act='.$_GET['act'].'">Return</a></div>';
        }
    }
}

$preferences = new userpref();

?>