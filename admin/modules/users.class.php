<?php
/*
 *				User administration
 *		Remove, modify, and search for users / characters
 */
class users{
	private $output_buffer;
	
	//	Constructor
	public function users(){
		if(!isset($_GET['act'])){
			$this->main_page();
		}
		elseif($_GET['act'] == 'search'){
			//	Search for specific user(s)
            if(!isset($_POST['Submit'])){
             	if(isset($_GET['uid'])){
					$this->search_results();
				}
				else{
					$this->search_form();
				}
            }
            else{
             	$this->search_results();	
            }
		}
		elseif($_GET['act'] == 'new'){
			//	Add new user
			if(!isset($_POST['Submit'])){
				$this->create_form();
			}
			else{
				$this->insert_user();
			}
		}
        elseif($_GET['act'] == 'del'){
            //  Delete user
            if(!isset($_POST['Submit'])){
                $this->verify_delete();
            }
            else{
                $this->do_delete();
            }   
        }
        elseif($_GET['act'] == 'enter'){
            //  Delete user
            if(!isset($_POST['Submit'])){
                $this->verify_enter();
            }
            else{
                $this->do_enter();
            }   
        }
		elseif($_GET['act'] == 'mod'){
			if(!isset($_GET['type'])){
                $this->edit_main();
            }
            elseif($_GET['type'] == 'char'){
                //      Edit chardata
                if(!isset($_POST['Submit'])){
                    $this->edit_user_form();
                }
                else{
                    $this->do_edit_user();
                }
            }
            elseif($_GET['type'] == 'assignment'){
                //      Edit chardata
                if(!isset($_POST['Submit'])){
                    $this->edit_assignment_form();
                }
                else{
                    $this->do_edit_assignment();
                }
            }
            elseif($_GET['type'] == 'alterstat'){
                //      Edit chardata
                if(!isset($_POST['Submit'])){
                    $this->alter_stat_form();
                }
                else{
                    $this->do_alter_stat();
                }
            }
			elseif($_GET['type'] == 'inv'){
				//      Edit inventory
                $this->show_inventory();
			}
            elseif($_GET['type'] == 'invadd'){
                //      Insert new item   
                if(!isset($_POST['Submit'])){
                    $this->add_item_form();
                }
                else{
                    $this->do_item_add();
                }
            }
            elseif($_GET['type'] == 'invdel'){
                //      Remove item from inventory
                if(!isset($_POST['Submit'])){
                    $this->confirm_item_delete();   
                }
                else{
                    $this->do_item_delete();   
                }
            }
			elseif($_GET['type'] == 'jut'){
			    //      Edit jutsu	
                $this->show_jutsu();
			}
            elseif($_GET['type'] == 'jutdel'){
                if(!isset($_POST['Submit'])){
                    $this->confirm_jutsu_delete();
                }
                else{
                    $this->do_jutsu_delete();
                } 
            }
			elseif($_GET['type'] == 'misc'){
				//      Edit mission data
                if(!isset($_POST['Submit'])){
                    $this->edit_misc_form();   
                }
                else{
                    $this->do_edit_misc();
                }
			}
			elseif($_GET['type'] == 'nin'){
				//      Remove nindo
                 if(!isset($_POST['Submit'])){
                    $this->confirm_nindo_delete();   
                }
                else{
                    $this->do_nindo_delete();   
                }
			}
            elseif($_GET['type'] == 'userpic'){
                //      Remove userpic
                if(!isset($_POST['Submit'])){
                    $this->confirm_userpic_delete();
                }
                else{
                    $this->do_userpic_delete();
                }
            }
            elseif($_GET['type'] == 'fedstatus'){
                if(!isset($_POST['Submit'])){
                    $this->fed_status_form();
                }
                else{
                    $this->update_fed_status();
                }   
            }
            elseif($_GET['type'] == 'delstatus'){
                if(!isset($_POST['Submit'])){
                    $this->del_status_form();
                }
                else{
                    $this->remove_del_status();
                }   
            }
            elseif($_GET['type'] == 'ban'){
                if(!isset($_POST['Submit'])){
                    $this->user_ban_form();
                }
                else{
                    $this->do_user_ban();
                }   
            }
            elseif($_GET['type'] == 'tradelog'){
                $this->tradelog();
            }
            elseif($_GET['type'] == 'replog'){
                $this->replog();
            }
            elseif($_GET['type'] == 'ryolog'){
                $this->ryolog();
            }
		}
        elseif($_GET['act'] == 'edits'){
            $this->view_edits_main();
        }
        elseif($_GET['act'] == 'editsearch'){
            if(!isset($_POST['Submit'])){
                $this->search_edits_form();
            }
            else{
                $this->search_edits_results();   
            }
        }
		$this->return_stream();
	}
	
	//	Return stream
	private function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	//	Main page
	
	private function main_page(){
		$this->output_buffer .= '<table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
  		<tr><td style="text-align:center;width:100%;" colspan="3" class="mini_header">::User admin:: </td>
  		</tr><tr>
    	<td style="width:33%;" align="center"><a href="?id='.$_GET['id'].'&act=edits">User modifications</a></td>
    	<td style="width:33%;" align="center"><a href="?id='.$_GET['id'].'&act=search">Search userlist</a></td>
  		</tr><tr><td colspan="3" align="center">&nbsp;</td></tr></table>';
	}
	
	
	//	Remove user(s)
	
	private function verify_enter(){
		$this->output_buffer = '<form id="form1" name="form1" method="post" action="">
        <table width="350" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
         <tr><td colspan="2" class="mini_header"><center>:: Mind Control:: </center></td></tr>
         <tr>
           <td colspan="2" align="center" style="padding:2px;color:#CC0000;font-weight:bold;">Take control over this character. <br />Note that actions will be logged.</td>
         </tr>
		<tr><td colspan="2"  width="224" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" value="Mind Control no jutsu" /></td>
        
          </tr></table>
          </form>';
	}
	
	private function do_enter(){
        $user = $GLOBALS['database']->fetch_data("SELECT * FROM `users`, `users_timer` WHERE `id` = '".$_GET['uid']."' LIMIT 1");
        if($user != '0 rows'){
            session_register('uid','username','ip','user_rank');
			$_SESSION['uid'] = $user[0]['id'];
			$_SESSION['username'] = $user[0]['username'];
			$_SESSION['override'] = true;
			$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
			$_SESSION['user_rank'] = $user[0]['user_rank'];

			$GLOBALS['database']->execute_query("UPDATE `users_timer` SET `last_login` = '".time()."', `logout_timer` = '".(time() + 7200)."' WHERE `userid` = '".$user[0]['id']."' LIMIT 1");
            
            // Update Memcache
            $user[0]['last_login'] = "".time()."";
            $user[0]['logout_timer'] = "".(time() + 7200)."";
            $GLOBALS['cache']->replace("resu:".$user[0]['id'],$user,false,43200);
            
			$this->output_buffer .= 'You have taken control of the character.<br /><a href="http://www.bleach-game.com">Return to game as this user</a>';
        }
        else{
            $this->output_buffer .= 'The userid submitted is not valid.<br /><a href="?id='.$_GET['id'].'">Return</a>';
        }
	}
	
	//	Search
	
	private function search_form(){
		$this->output_buffer .= '<div align="center">
        <form id="form1" name="form1" method="post" action="">
        <table width="500" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td colspan="2" align="center" class="mini_header">Search users</td>
          </tr>
          <tr>
            <td width="227" align="right" style="padding:2px;">Username:</td>
            <td width="271" align="left" style="padding:2px;"><input type="text" class="textfield" name="username" id="textfield" /></td>
          </tr>
          <tr>
            <td align="right" style="padding:2px;">E-mail:</td>
            <td align="left" style="padding:2px;"><input type="text" class="textfield" name="email" id="textfield2" /></td>
          </tr>
          <tr>
            <td align="right" style="padding:2px;">User ID:</td>
            <td align="left" style="padding:2px;"><input type="text" class="textfield" name="userid" id="textfield3" /></td>
          </tr>
          <tr>
            <td align="right" style="padding:2px;">Last IP</td>
            <td align="left" style="padding:2px;"><input type="text" class="textfield" name="last_ip" id="textfield4" /></td>
          </tr>
          <tr>
            <td colspan="2" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" value="Submit" /></td>
            </tr>
        </table>
        </form>
        </div>';
	}
	
	private function search_results(){
        $query = "SELECT users_timer.*,`username`,`id`,`mail` FROM `users_timer`,`users` WHERE ";
		if($_POST['username'] != ''){
            $query .= " `username` LIKE '".$_POST['username']."'";
        }
        if($_POST['email'] != ''){
            if($_POST['username'] != ''){
                $query .= " AND ";   
            }
            $query .= " `mail` LIKE '".$_POST['email']."'";
        }
        if($_POST['userid'] != ''){
            if($_POST['username'] != '' || $_POST['email'] != ''){
                $query .= " AND ";   
            }
            $query .= " `id` = ".$_POST['userid']."";
        }
        if($_POST['last_ip'] != ''){
            if($_POST['username'] != '' || $_POST['email'] != '' || $_POST['userid'] != ''){
                $query .= " AND ";   
            }
            $query .= " `last_ip` = '".$_POST['last_ip']."'";
        }
        if($_GET['uid'] != ''){
            if($_POST['username'] != '' || $_POST['email'] != ''){
                $query .= " AND ";   
            }
            $query .= " `id` = ".$_GET['uid']."";
        }
        
        $query .= " AND users.id = users_timer.userid ORDER BY `join_date` ASC";
        $results = $GLOBALS['database']->fetch_data($query);
        $this->output_buffer .= '<div align="center">
        <table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td colspan="6" align="center" class="mini_header">Search results:</td>
          </tr>
          <tr>
            <td width="71" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">id</td>
            <td width="127" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">username</td>
            <td width="125" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">Last IP</td>
            <td width="92" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">&nbsp;</td>
            <td width="83" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">&nbsp;</td>
          </tr>';
        if($results != '0 rows'){
            $i = 0;
            while($i < count($results)){
                $this->output_buffer .= '<tr class="row'.($i % 2).'">
                <td align="center">'.$results[$i]['id'].'</td>
                <td align="center">'.$results[$i]['username']; 
                if( $results[$i]['perm_ban'] == 1 ){
                    $this->output_buffer .= '*';    
                }
                $this->output_buffer .= '</td>
                <td align="center">'.$results[$i]['last_ip'].'</td>
                <td align="center"><a href="?id='.$_GET['id'].'&act=mod&uid='.$results[$i]['id'].'">edit user</a></td>
                <td align="center"><a href="?id='.$_GET['id'].'&act=enter&uid='.$results[$i]['id'].'">control user</a></td>
                </tr>';
                $i++;
            }
        }
        else{
             $this->output_buffer .= '<tr class="row0">
            <td colspan="6" align="center">No users found</td>
            </tr>';  
        }
        $this->output_buffer .= '<tr>
            <td colspan="6" align="center" style="border-top:1px solid #000000;">* = permanent banned</td>
            </tr>
        </table>
        </div>';
	}
	
	//	Edit user
    
    private function edit_main(){
        $this->output_buffer .= '<div align="center">
        <table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td colspan="3" align="center" class="mini_header">Edit user</td>
          </tr>
          <tr>
            <td align="center"><a href="?id='.$_GET['id'].'&act=mod&uid='.$_GET['uid'].'&type=char">Edit stats</a></td>
            <td align="center"></td>
            <td align="center"><a href="?id='.$_GET['id'].'&act=mod&uid='.$_GET['uid'].'&type=inv">See inventory</a></td>
          </tr>
          <tr>
            <td width="33%" align="center"><a href="?id='.$_GET['id'].'&act=mod&uid='.$_GET['uid'].'&type=ban">Ban options</a></td>
            <td width="33%" align="center"><a href="?id='.$_GET['id'].'&act=mod&type=delstatus&uid='.$_GET['uid'].'">Deletion Timer</a></td>
            <td width="33%" align="center"><a href="?id='.$_GET['id'].'&act=mod&uid='.$_GET['uid'].'&type=userpic">Remove userpic</a></td>
          </tr>
        </table>
        </div><br />';
    }
	
	
    /*
     *                  EDIT USER FUNCTIONS  
     */
     
    /*
     *          Edit main user stats
     */
     
    private function edit_user_form(){
        $this->edit_main();
        if(isset($_GET['uid'])){
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `id` = '".$_GET['uid']."' LIMIT 1");
            if($data != '0 rows'){
                $this->output_buffer .= functions::parse_form('users','Edit user',array('id','join_date','logout','ip_lock','healed'),$data);
            }
            else{
                $this->output_buffer .= '<div align="center">This user does not exist?<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
            }
        }
        else{
            $this->output_buffer .= '<div align="center">No user was specified.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
        }
    }
    
    private function do_edit_user(){
        $this->edit_main();
        if(isset($_GET['uid']) && is_numeric($_GET['uid'])){
            $changed = functions::check_data('users','id',$_GET['uid'],array('join_date','id','healed'));
            if(functions::update_data('users','id',$_GET['uid'])){
                $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('".time()."', '".$_SESSION['username']."', '".$_GET['uid']."', 'User stats updated:<br /> ".$changed."', '".$_SERVER['REMOTE_ADDR']."');");
                $this->output_buffer .= 'The user has been updated <br /><a href="?id='.$_GET['id'].'">Return to user CP</a>';
                $GLOBALS['cache']->delete("resu:".$_GET['uid']);
            }
            else{
                $this->output_buffer .= 'An error occured while updating the user <br /><a href="?id='.$_GET['id'].'">Return to user CP</a>';
            }
        }
    }
        


    
    //          Inventory
    
    private function show_inventory(){
        $this->edit_main();
        $items = $GLOBALS['database']->fetch_data("SELECT users_inventory.*, items.name FROM `users_inventory`,`items` WHERE `uid` = '".$_GET['uid']."' AND users_inventory.iid = items.id ORDER BY `timekey` ASC");
        $this->output_buffer .= '<table width="500" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td colspan="5" align="center" class="mini_header">User inventory</td>
          </tr>
          <tr>
            <td width="11%" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">item id</td>
            <td width="26%" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">item name</td>
            <td width="21%" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">Equipped</td>
            <td width="21%" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">Stack size</td>
            <td width="21%" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">&nbsp;</td>
          </tr>';
        if($items != '0 rows'){
            $i = 0;
            while($i < count($items)){
                $this->output_buffer .= '<tr class="row'.($i % 2).'">
                    <td align="center">'.$items[$i]['iid'].'</td>
                    <td align="center">'.stripslashes($items[$i]['name']).'</td>
                    <td align="center">'.$items[$i]['equipped'].'</td>
                    <td align="center">'.$items[$i]['stack'].'</td>
                    <td align="center"><a href="?id='.$_GET['id'].'&act=mod&uid='.$_GET['uid'].'&type=invdel&iid='.$items[$i]['iid'].'&timekey='.$items[$i]['timekey'].'">Remove</a></td>
                </tr>';
                $i++;
            }
        }
        else{
            $this->output_buffer .= '<tr>
            <td colspan="5" align="center">No items were found in this user\'s inventory</td></tr>';   
        }
        $this->output_buffer .= '<tr>
            <td colspan="5" align="center" style="border-top:1px solid #000000;"><a href="?id='.$_GET['id'].'&act=mod&uid='.$_GET['uid'].'&type=invadd">Add new item</a></td>
          </tr>
          <tr>
            <td colspan="5" align="center">&nbsp;</td>
          </tr>
        </table>
        </div>';
    }

    //          Remove item from inventory
    
    private function confirm_item_delete(){
        $this->output_buffer = '<form id="form1" name="form1" method="post" action="">
        <table width="350" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
         <tr><td colspan="2" class="mini_header">::Remove item from inventory:: </td></tr><tr>
        <td colspan="2" align="center" style="padding:2px;">Are you sure you wish to delete this item from the user\'s inventory?</td>
          </tr><tr><td width="224" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" value="Yes" /></td>
          <td width="224" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" value="No" /></td>
          </tr></table></form>';
    }
    
    private function do_item_delete(){
        $this->edit_main();
        if($_POST['Submit'] == 'Yes'){
            if($GLOBALS['database']->execute_query("DELETE FROM `users_inventory` WHERE `uid` = '".$_GET['uid']."' AND `iid` = '".$_GET['iid']."' AND `timekey` = '".$_GET['timekey']."' LIMIT 1")){
                $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('".time()."', '".$_SESSION['username']."', '".$_GET['uid']."', 'Item deleted from inventory: ".$_GET['iid']."', '".$_SERVER['REMOTE_ADDR']."');");
                $this->output_buffer .= '<div align="center">The item was removed from the user\'s inventory!</div>';
            }
            else{
                $this->output_buffer .= '<div align="center">The item could not be removed from the user\'s inventory!</div>';
            }
        }
        else{
            
        }
    }
    
    //          Add item to inventory
    
    private function add_item_form(){
        $this->edit_main();
        $this->output_buffer .= '<form id="form1" name="form1" method="post" action="">
        <table width="500" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td colspan="2" align="center" class="mini_header">Add item to user inventory</td>
          </tr>
          <tr>
            <td align="center" style="padding:2px;">User id:</td>
            <td align="center" style="padding:2px;font-weight:bold;">'.$_GET['uid'].'</td>
          </tr>
          <tr>
            <td width="50%" align="center" style="padding:2px;">Item:</td>
            <td width="50%" align="center" style="padding:2px;"><select name="iid" class="listbox" id="select">';
        $items = $GLOBALS['database']->fetch_data("SELECT `id`,`name` FROM `items` ORDER BY `id` ASC");
        if($items != '0 rows'){
            $i = 0;
            while($i < count($items)){
                $this->output_buffer .= '<option value="'.$items[$i]['id'].'">'.stripslashes($items[$i]['name']).'</option>';
                $i++;
            }
        }
        $this->output_buffer .= '</select>    </td>
          </tr>
          <tr>
            <td colspan="2" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" value="Submit" /></td>
            </tr>
        </table>
        </form>';
    }
    
    private function do_item_add(){
        $this->edit_main();
        if(isset($_POST['iid']) && is_numeric($_POST['iid'])){
            $item = $GLOBALS['database']->fetch_data("SELECT `name` FROM `items` WHERE `id` = '".$_POST['iid']."' LIMIT 1");
            if($item != '0 rows'){
                if($GLOBALS['database']->execute_query("INSERT INTO `users_inventory` ( `uid` , `iid` , `equipped` , `stack` , `timekey` ) VALUES ('".$_GET['uid']."', '".$_POST['iid']."', 'no', '1', '".time()."');")){
                    $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('".time()."', '".$_SESSION['username']."', '".$_GET['uid']."', 'Item added to inventory: ".addslashes($item[0]['name'])."', '".$_SERVER['REMOTE_ADDR']."');");
                    $this->output_buffer .= '<div align="center">One '.stripslashes($item[0]['name']).' has been added to the user\'s inventory.</div>';
                }
                else{
                    $this->output_buffer .= '<div align="center">The '.stripslashes($item[0]['name']).' could not be added to the user\'s inventory.</div>';
                }
            }
            else{
               $this->output_buffer .= '<div align="center>The specified item does not exist!</div>';  
            }
        }
        else{
            $this->output_buffer .= '<div align="center>No item ID was set!</div>';   
        }
    }
    
  
    
    /*
     *              Remove userpic features
     */
     
    private function confirm_userpic_delete(){
        $this->output_buffer = '<form id="form1" name="form1" method="post" action="">
        <table width="350" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
         <tr><td colspan="2" class="mini_header">::Remove avatar:: </td></tr><tr>
        <td colspan="2" align="center" style="padding:2px;">Are you sure you wish to delete this user\'s avatar?</td>
          </tr><tr><td width="224" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" value="Yes" /></td>
          <td width="224" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" value="No" /></td>
          </tr></table></form>';
    }
    
    private function do_userpic_delete(){
        if(file_exists('../images/avatars/'.$_GET['uid'].'.gif')){
            if(unlink('../images/avatars/'.$_GET['uid'].'.gif')){
                $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('".time()."', '".$_SESSION['username']."', '".$_GET['uid']."', 'Removed avatar', '".$_SERVER['REMOTE_ADDR']."');");
                $this->output_buffer .= '<div align="center">The user\'s avatar has been removed.</div>';
            }
            else{
                $this->output_buffer .= '<div align="center">The user\'s avatar could not be removed!</div>';
            }
        }
        else{
            $this->output_buffer .= '<div align="center">This user does not have an avatar!</div>';
        }
    }

  

    
    /*
     *              View edits to users
     */
     
    private function view_edits_main(){
        $this->output_buffer .= '<div align="center">
          <table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td width="498%" colspan="4" align="center" class="mini_header">::Modifications to user accounts::</td>
            </tr>
            <tr>
              <td colspan="4" align="center" style="padding:2px;color:darkred;">Using these pages you can track modifications made to user accounts or find edits made by specific admins.</td>
            </tr>
            <tr>
              <td colspan="4" align="center"><a href="?id='.$_GET['id'].'&act=editsearch">Search for modifications</a></td>
            <tr>
              <td colspan="4" align="center" class="sub_header" style="">Latest changes:</td></tr>';
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_edits` ORDER BY `time` DESC LIMIT 100");
        if($edits != '0 rows'){
            $this->output_buffer .= '<tr class="row1">
                  <td align="center" style="font-weight:bold;padding-left:5px;">Admin name:</td>
                  <td align="center" style="font-weight:bold;">IP</td>
                  <td align="center" style="font-weight:bold;">User</td>
                  <td align="center" style="font-weight:bold;">Edit</td>
                </tr>';
            $i = 0;
            while($i < count($edits)){
                $this->output_buffer .= '
                <tr class="row'.($i % 2).'">
                  <td align="center">'.$edits[$i]['aid'].'</td>
                  <td align="center">'.$edits[$i]['IP'].'</td>
                  <td align="center">'.$edits[$i]['uid'].'</td>
                  <td align="center">'.$edits[$i]['changes'].'</td>
                </tr>';
                $i++;
            }
        }
        else{
            $this->output_buffer .= '<tr>
            <td colspan="4" align="center" class="row1" style="border-top:1px solid #000000;">No modifications found in the table.</td></tr>';
        }
        $this->output_buffer .= '<tr>
        <td colspan="4" align="center" style="border-top:1px solid #000000;">&nbsp;</td></tr></table></div>';
    }

    private function search_edits_form(){
        $this->output_buffer .= '<div align="center"><form id="form1" name="form1" method="post" action="">
          <table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td colspan="2" align="center" class="mini_header">::Search for user modifications::</td>
            </tr>
            <tr>
              <td width="279" align="center" style="padding:2px;">Username:</td>
              <td width="269" align="center" style="padding:2px;"><input name="username" type="text" class="textfield" id="textfield" /></td>
            </tr>
            <tr>
              <td align="center" style="padding:2px;">Admin name:</td>
              <td align="center" style="padding:2px;"><input name="adminname" type="text" class="textfield" /></td>
            </tr>
            <tr>
              <td colspan="2" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" value="Submit" /></td>
            </tr>
            <tr>
              <td colspan="2" align="center">&nbsp;</td>
            </tr>
          </table>
        </form></div>';
    }

    private function search_edits_results(){
        $this->output_buffer .= '<div align="center"><table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="498%" colspan="4" align="center" class="mini_header">::User account modifications::</td>
        </tr>
        <tr>
          <td colspan="4" align="center" style="border-bottom:1px solid #000000;"><a href="?id='.$_GET['id'].'&act=editsearch">New search</a></td>
        <tr>
          <td colspan="4" align="center" style="border-bottom:1px solid #000000;font-weight:bold;">Search results</td>
        </tr>';
        //      GENERATE QUERY
        if(isset($_POST['adminname']) && $_POST['adminname'] != ''){
            $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_edits` WHERE `aid` = '".$_POST['adminname']."' ORDER BY `time` DESC LIMIT 200");
        }
        elseif(isset($_POST['username'])){
            if(!is_numeric($_POST['username']) && $_POST['username'] != 'MULTIPLE'){
                $uidt = $GLOBALS['database']->fetch_data("SELECT `id` FROM `users` WHERE `username` = '".$_POST['username']."' LIMIT 1");
                if($uidt != '0 rows'){
                       $uid = $uidt[0]['id'];
                }
                else{
                    $edits = 'noquery';
                    $editserror = 'this user does not exist';
                }
            }
            else{
                $uid = $_POST['username'];
            }
            if(!isset($editserror)){
                $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_edits` WHERE `uid` = '".$uid."' ORDER BY `time` DESC LIMIT 200");
            }
        }
        else{
            $edits = 'noquery';
            $editserror = 'No query was submitted';
        }
        //      EXECUTE QUERY
        if($edits != '0 rows' && $edits != 'noquery'){
            $this->output_buffer .= '<tr class="row1">
                  <td align="center" style="font-weight:bold;padding-left:5px;">Admin name:</td>
                  <td align="center" style="font-weight:bold;">IP</td>
                  <td align="center" style="font-weight:bold;">User</td>
                  <td align="center" style="font-weight:bold;">Edit</td>
                </tr>';
            $i = 0;
            while($i < count($edits)){
                $this->output_buffer .= '
                <tr class="row'.($i % 2).'">
                  <td align="center">'.$edits[$i]['aid'].'</td>
                  <td align="center">'.$edits[$i]['IP'].'</td>
                  <td align="center"><a href="?id=15&act=search&uid='.$edits[$i]['uid'].'">'.$edits[$i]['uid'].'</a></td>
                  <td align="center">'.$edits[$i]['changes'].'</td>
                </tr>';
                $i++;
            }
        }
        elseif($edits == '0 rows'){
            $this->output_buffer .= '<tr>
            <td colspan="4" align="center" class="row1" style="border-top:1px solid #000000;">No results found.</td></tr>';
        }
        elseif($edits == 'noquery'){
            $this->output_buffer .= '<tr>
            <td colspan="4" align="center" class="row1" style="border-top:1px solid #000000;">'.$editserror.'</td></tr>';
        }
        $this->output_buffer .= '<tr>
              <td colspan="4" align="center" style="border-top:1px solid #000000;">&nbsp;</td>
            </tr>
          </table></div>';
    }


    /*
     *              Deletion timer options
     */
    
    private function remove_del_status(){
        if(isset($_GET['uid']) && is_numeric($_GET['uid'])){
            if($_POST['Submit'] == 'Remove Timer'){
                $GLOBALS['database']->execute_query("UPDATE `users`,`users_timer` SET `deletion_timer` = 0 WHERE users.id = users_timer.userid AND users.id = '".$_GET['uid']."'");
                $GLOBALS['cache']->delete("resu:".$_GET['uid']);
                $this->output_buffer .= '<div align="center">Deletion timer was reset<br /><a href="?id='.$_GET['id'].'&act=mod">Return</a></div>';
            }
        }
        else{
            $this->output_buffer .= '<div align="center">Data could not be retrieved (does this user exist?)<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
        }
    }
    
    private function del_status_form(){
        if(isset($_GET['uid']) && is_numeric($_GET['uid'])){
            $this->output_buffer .= '<div align="center" style="color:darkred;">This panels allow you to reset the deletion time of users.</div>';
            $fed_data = $GLOBALS['database']->fetch_data("SELECT users_timer.deletion_timer, users.username FROM users,users_timer WHERE users.id = users_timer.userid AND users.id = '".$_GET['uid']."'");
            $this->output_buffer .= '<form id="form1" name="form1" method="post" action="">
              <div align="center">
              <table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td colspan="2" align="center" class="mini_header">Remove deletion timer</td>
                </tr>
                <tr>
                  <td align="center" style="padding-top:5px;">Deletion at:</td>
                  <td align="left" style="padding-top:5px;padding-left:5px;">';
                    if($fed_data[0]['deletion_timer'] == 0){
                        $this->output_buffer .= '<div align="center" style="color:darkred;">No deletion timer set</div>';
                    }
                    else{
                        $this->output_buffer .= date('G:i:s d-m-Y',$fed_data[0]['deletion_timer']);
                    }
                    $this->output_buffer .= '</td>
                </tr>
                <tr>
                  <td colspan="2" align="center" style="padding-top:5px;">
                    <input name="Submit" type="submit" class="button" id="Submit" value="Remove Timer" />&nbsp;</td>
                </tr>
                <tr>
                  <td colspan="2" align="center">&nbsp;</td>
                </tr>
              </table>
            </div>
            </form>';
        }
        else{
            $this->output_buffer .= '<div align="center">Data could not be retrieved (does this user exist?)<br /><a href="?id='.$_GET['id'].'&act=mod">Return</a></div>';
        }
    }

    /*
     *              User banning options
     */
     
    private function user_ban_form(){
        $this->output_buffer .= '<form id="form1" name="form1" method="post" action="">
          <div align="center">
          <table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td colspan="2" align="center" class="mini_header">Ban options</td>
            </tr>
            <tr>
              <td width="280" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" id="Submit" value="Perm-ban" /></td>
              <td width="268" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" id="Submit" value="Un perm-ban" /></td>
            </tr>
            <tr>
              <td colspan="2" align="center" style="border-top:1px solid #000000;"><a href="?id='.$_GET['id'].'">Return to main page</a></td>
              </tr>
          </table>
        </div>
        </form>';
    }
    
    private function do_user_ban(){
        $bandata = $GLOBALS['database']->fetch_data("SELECT `ban_time`,`perm_ban` FROM `users_timer` WHERE `userid` = '".$_GET['uid']."' LIMIT 1");
            if($bandata != '0 rows'){
            if($_POST['Submit'] == 'Perm-ban'){
                if($bandata[0]['perm_ban'] == 0){
                    $GLOBALS['database']->execute_query("UPDATE `users_timer` SET `perm_ban` = '1', `logout_timer` = '1' WHERE `userid` = '".$_GET['uid']."' LIMIT 1");
                    
                    // Update Memcache
                    $GLOBALS['userdata'][0]['perm_ban'] = "1";
                    $GLOBALS['userdata'][0]['logout_timer'] = "1";
                    $GLOBALS['cache']->replace("resu:".$_GET['uid'],$GLOBALS['userdata'],false,43200);
                    
                    $this->output_buffer .= '<div align="center">The user has been permanently banned.<br /><a href="?id='.$_GET['id'].'&act=mod&uid='.$_GET['uid'].'">Return</a></div>';   
                }
                else{
                    $this->output_buffer .= '<div align="center">This user is already banned permanently?<br /><a href="?id='.$_GET['id'].'&act=mod&uid='.$_GET['uid'].'">Return</a></div>';   
                }
            }
            elseif($_POST['Submit'] == 'Un perm-ban'){
                if($bandata[0]['perm_ban'] == 1){
                    $GLOBALS['database']->execute_query("UPDATE `users_timer` SET `perm_ban` = '0' WHERE `userid` = '".$_GET['uid']."' LIMIT 1");
                    
                    // Update Memcache
                    $GLOBALS['userdata'][0]['perm_ban'] = "0";
                    $GLOBALS['cache']->replace("resu:".$_GET['uid'],$GLOBALS['userdata'],false,43200);
                    
                    $this->output_buffer .= '<div align="center">The user has been unbanned.<br /><a href="?id='.$_GET['id'].'&act=mod&uid='.$_GET['uid'].'">Return</a></div>';   
                }
                else{
                    $this->output_buffer .= '<div align="center">This user is not banned permanently?<br /><a href="?id='.$_GET['id'].'&act=mod&uid='.$_GET['uid'].'">Return</a></div>';   
                }
            }
        }
        else{
            $this->output_buffer .= '<div align="center">Data could not be retrieved (does this user exist?)<br /><a href="?id='.$_GET['id'].'">Return</a></div>';   
        }
    }

    
}
$users = new users();
?>