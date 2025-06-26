<?php

class mod_panel{
	var $output_buffer;
	
	public function mod_panel(){
		if($_SESSION['user_rank'] == 'Moderator' || $_SESSION['user_rank'] == 'Admin' || $_SESSION['user_rank'] == 'Supermod'){
			if(!isset($_GET['act'])){
				$this->main_screen();
			}
			else{
				if($_GET['act'] == 'ban'){
					if(!isset($_POST['Submit'])){
						$this->ban_form();
					}
					else{
						$this->give_ban();
					}
				}
				elseif($_GET['act'] == 'tavernban'){
					if(!isset($_POST['Submit'])){
						$this->tavernban_form();
					}
					else{
						$this->give_tavernban();
					}
				}
				elseif($_GET['act'] == 'banlog'){
					$this->show_banlog();
				}
				elseif($_GET['act'] == 'tbanlog'){
					$this->tavern_ban_log();
				}
				elseif($_GET['act'] == 'jump'){
					if(!isset($_POST['Submit'])){
						$this->jump_form();
					}
					else{
						//	Execute jump
						$this->do_jump();
					}
				}
				elseif($_GET['act'] == 'warn'){
					if(!isset($_POST['Submit'])){	
						$this->warning_form();
					}
					else{
						$this->give_warning();
					}
				}
				elseif($_GET['act'] == 'check_user'){
					if(isset($_GET['uid']) || isset($_POST['Submit'])){
						$this->show_user_sheet();
					}
					else{
						$this->user_form();
					}
				}
				elseif($_GET['act'] == 'details'){
					$this->show_details();
				}
				elseif($_GET['act'] == 'unban'){
					if(!isset($_POST['Submit'])){
						$this->unban_form();
					}
					else{
						$this->do_unban();
					}
				}
				elseif($_GET['act'] == 'untban'){
					$this->un_tavern_ban();
				}
				elseif($_GET['act'] == 'reports'){
					$this->report_main_screen();
					if($_GET['page'] == 'unviewed' || !isset($_GET['page'])){
						$this->show_reports("unviewed");
					}
					elseif($_GET['page'] == 'current'){
						$this->show_reports("in progress");
					}
					elseif($_GET['page'] == 'details'){
						if(!isset($_POST['Submit'])){
							$this->report_show_details();
						}
						else{
							$this->update_report_status();
						}
					}
				}
                elseif($_GET['act'] == 'note'){
                    if($_GET['page'] == 'view'){
                        $this->view_note();
                    }
                    elseif($_GET['page'] == 'edit'){
                        if(!isset($_POST['Submit'])){
                            $this->edit_note();   
                        }
                        else{
                            $this->do_edit_note();   
                        }
                    }
                    elseif($_GET['page'] == 'delete'){
                        if(!isset($_POST['Submit'])){
                            $this->verify_delete_note();
                        }
                        else{
                            $this->do_delete_note();   
                        }
                    }
                    elseif($_GET['page'] == 'new'){
                        if(!isset($_POST['Submit'])){
                            $this->new_note();   
                        }
                        else{
                            $this->do_new_note();
                        }
                    }   
                }
				elseif($_SESSION['user_rank'] == 'Supermod' || $_SESSION['user_rank'] == 'Admin'){
					if($_GET['act'] == 'hire'){
						if(!isset($_POST['Submit'])){
							$this->hire_form();
						}
						else{
							$this->do_hire();
						}
					}
					elseif($_GET['act'] == 'fire'){
						if(!isset($_POST['Submit'])){
							$this->fire_form();
						}
						else{
							$this->do_fire();
						}
					}
					elseif($_GET['act'] == 'order'){
						if(!isset($_POST['Submit'])){
							$this->order_form();
						}
						else{
							$this->edit_orders();
						}
					}
				}
			}
		}
		else{
			$this->output_buffer .= '<div align="center">You are not a moderator or administrator. <br /><a href="?id=2">Return to profile</div>';
		}
		$this->return_stream();
	}
	
	private function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	//	Main screens
	
	private function main_screen(){
		$this->mod_options();
        $this->show_notes();
		if($_SESSION['user_rank'] == 'Supermod' || $_SESSION['user_rank'] == 'Admin'){
			$this->supermod_options();
		}
	}
	
	//	Mod options:
	private function mod_options(){
		$this->output_buffer .= '<div align="center">
  		<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
    	<tr><td colspan="4" align="center" class="subHeader" style="border-top:none;">Moderator HQ </td>
    	</tr><tr><td align="center" width="25%"><a href="?id='.$_GET['id'].'&act=ban">Ban user</a></td>
      	<td align="center" width="25%"><a href="?id='.$_GET['id'].'&act=tavernban">Chat ban user</a></td>
      	<td align="center" width="25%"><a href="?id='.$_GET['id'].'&act=check_user">Check user\'s record</a></td>
      	<td align="center" width="25%"><a href="?id='.$_GET['id'].'&act=jump">Switch Race</a></td>
    	</tr><tr><td align="center"><a href="?id='.$_GET['id'].'&act=banlog">Currently banned</a></td>
      	<td align="center"><a href="?id='.$_GET['id'].'&act=tbanlog">Currently chat banned</a></td>
      	<td align="center"><a href="?id='.$_GET['id'].'&act=warn">Warn user</a></td><td align="center"><a href="?id='.$_GET['id'].'&act=reports">Reports</a></td>
    	</tr>
    	<tr><td colspan="4" align="center">&nbsp;</td></tr></table></div><br />';
	}
	
	//	Supermod options:
	private function supermod_options(){
		$this->output_buffer .= '
		<div align="center"><br /><table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
    	<tr><td colspan="3" align="center" class="subHeader" style="border-top:none;">Supermod panel: </td>
    	</tr><tr>
      	<td align="center" width="25%"><a href="?id='.$_GET['id'].'&act=hire">Appoint moderator</a></td>
      	<td align="center" width="25%"><a href="?id='.$_GET['id'].'&act=fire">Fire moderator</a></td>
      	<td align="center" width="25%"><a href="?id='.$_GET['id'].'&act=note&page=new">Add note</a></td>
    	</tr><tr><td colspan="4" align="center">&nbsp;</td></tr></table></div>';
	}
	
	/*
	 *				OPTIONS
	 */
	
	//	Jump options
	
	private function jump_form(){
		$this->output_buffer .= '<div align="center"><table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
    	<tr><td width="100%" align="center" style="border-top:none;" class="subHeader">Switch Race:  </td>
    	</tr><tr><td align="center" style="padding:2px;"><form name="form1" method="post" action="">
        <select name="village">
        <option>Hollow</option>
        <option>Shinigami</option>
        </select>
        <input type="submit" name="Submit" value="Submit"></form>
      	</td></tr><tr><td align="center"><a href="?id='.$_GET['id'].'">Return</a></td>
  	  	</tr></table></div>';
	}
	
	private function do_jump(){
		$user = $GLOBALS['userdata'];
		$village = $GLOBALS['database']->fetch_data("SELECT * FROM `races` WHERE `name` = '".$_POST['village']."' LIMIT 1");
		if($village != '0 rows'){
		 	switch($_POST['village']){
				case "Shinigami": $rankid = 1; break;
				case "Hollow": $rankid = 2; break;
			}
			$GLOBALS['database']->execute_query("UPDATE `users` SET `race` = '".$_POST['village']."', `rankid` = '".$rankid."' WHERE `id` = '".$_SESSION['uid']."' LIMIT 1");
            // Memcache Update - START
            $GLOBALS['userdata'][0]['race'] = "".$_POST['village']."";
            $GLOBALS['userdata'][0]['rankid'] = "".$rankid."";
            $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,10);
            // Memcache Update - END
			$this->output_buffer .= '<div align="center">You have switched to '.$_POST['village'].'<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
		else{
			//	Village does not exist
		}
	}

	
    //  Mod / Admin notes
    
    private function show_notes(){
        if($_SESSION['user_rank'] == 'Supermod'){
            $variable = "OR `visibility` = 'Supermod'";   
        }
        elseif($_SESSION['user_rank'] == 'Admin'){
            $variable = "OR `visibility` = 'Supermod' OR `visibility` = 'Admin'";
        }
        $results = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_notes` WHERE `visibility` = 'All' OR `visibility` = 'Mod' ".$variable." ORDER BY `time` DESC");
        $this->output_buffer .= '<div align="center">
                  <table width="95%" border="0" cellspacing="0" cellpadding="0" class="table">
                    <tr>
                      <td colspan="6" align="center" class="subHeader" style="border-top:none;" >Notes</td>
                    </tr>';
        if($results != '0 rows'){
            $this->output_buffer .= '<tr>
              <td width="45%" align="center" style="border-bottom:1px solid #000000;">Title</td>
              <td width="14%" align="center" style="border-bottom:1px solid #000000;">Posted by</td>
              <td width="15%" align="center" style="border-bottom:1px solid #000000;">Date</td>
              <td width="8%" align="center" style="border-bottom:1px solid #000000;">&nbsp;</td>
              <td width="7%" align="center" style="border-bottom:1px solid #000000;">&nbsp;</td>
              <td width="11%" align="center" style="border-bottom:1px solid #000000;">&nbsp;</td>
            </tr>';
            $i = 0;
            while($i < count($results)){
                $this->output_buffer .= '
                <tr class="row'.(($i % 2) + 1).'">
                  <td align="left" style="padding-left:10px;">'.$results[$i]['title'].'</td>
                  <td align="center">'.$results[$i]['posted_by'].'</td>
                  <td align="center">'.date('d-m',$results[$i]['time']).'</td>
                  <td align="center"><a href="?id='.$_GET['id'].'&act=note&page=view&nid='.$results[$i]['id'].'">View</a></td>';
                if($_SESSION['user_rank'] == 'Supermod' || $_SESSION['user_rank'] == 'Admin'){
                    $this->output_buffer .= '
                    <td align="center"><a href="?id='.$_GET['id'].'&act=note&page=edit&nid='.$results[$i]['id'].'">Edit</a></td>
                    <td align="center"><a href="?id='.$_GET['id'].'&act=note&page=delete&nid='.$results[$i]['id'].'">Delete</a></td>';
                }
                else{
                    $this->output_buffer .= '
                    <td align="center">&nbsp;</td>
                    <td align="center">&nbsp;</td>';
                }
                $this->output_buffer .= '</tr>';
                $i++;
            }
        }
        else{
            $this->output_buffer .= '<tr class="row1">
              <td colspan="6" align="center">No notes were found</td>
            </tr>';    
        }
        $this->output_buffer .= '<tr>
              <td colspan="6" align="center" style="border-top:1px solid #000000;">&nbsp;</td>
            </tr>
          </table>
        </div>';
    }
    
    private function view_note(){
        if(isset($_GET['nid']) && is_numeric($_GET['nid'])){
            $result = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_notes` WHERE `id` = '".$_GET['nid']."' LIMIT 1");
            if($result != '0 rows'){
                if($result[0]['visibility'] == 'All' || $_SESSION['user_rank'] == 'Admin' || ($result[0]['visibility'] == 'Supermod' && $_SESSION['user_rank'] == 'Supermod')){
                    $this->output_buffer .= '<div align="center">
                    <form name="form1" method="post" action="">
                      <table width="90%" border="0" cellspacing="0" cellpadding="0" class="table">
                        <tr>
                          <td colspan="2" align="center" class="subHeader" style="border-top:none;" >View note</td>
                        </tr>
                        <tr>
                          <td align="center">Title:</td>
                          <td align="left">'.$result[0]['title'].'</td>
                        </tr>
                        <tr>
                          <td width="26%" align="center">Posted by:</td>
                          <td width="74%" align="left">'.$result[0]['posted_by'].'</td>
                        </tr>
                        <tr>
                          <td width="26%" align="center">Visibility:</td>
                          <td width="74%" align="left">'.$result[0]['visibility'].'</td>
                        </tr>
                        <tr>
                          <td align="center">Date:</td>
                          <td align="left">'.date('G:i:s d-m-Y',$result[0]['time']).'</td>
                        </tr>
                        <tr>
                          <td colspan="2" align="center" class="subHeader">Contents</td>
                        </tr>
                        <tr>
                          <td colspan="2" align="center" style="padding:5px;">'.$result[0]['text'].'</td>
                        </tr>
                    </table>
                    </form>
                      <br />
                    </div>';
                }
                else{
                    $this->output_buffer .= '<div align="center">You are not allowed to view this note<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
                }
            }
            else{
                $this->output_buffer .= '<div align="center">No note with this ID exists<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
            }
        }
        else{
            $this->output_buffer .= '<div align="center">An invalid note ID was specified<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
        }
    }
    
    private function edit_note(){
        if($_SESSION['user_rank'] == 'Supermod' || $_SESSION['user_rank'] == 'Admin'){
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_notes` WHERE `id` = '".$_GET['nid']."'");
            if($data != '0 rows'){
                if($data[0]['visibility'] != 'Admin' || $_SESSION['user_rank'] == 'Admin'){
                    $this->output_buffer .= str_replace('mini_header','subHeader',functions::parse_form('admin_notes','Update note',array('id','time','posted_by'),$data));
                }
                else{
                   $this->output_buffer .= 'You do not have access to this note.<a href="?id='.$_GET['id'].'">Return</a>';
                }
            }
            else{
                $this->output_buffer .= 'This note does not exist<br /><a href="?id='.$_GET['id'].'">Return</a>';
            }
        }
        else{
            $this->output_buffer .= 'You are not allowed to edit notes.<br /><a href="?id='.$_GET['id'].'">Return</a>';   
        }
    }
    
    private function do_edit_note(){
        if($_SESSION['user_rank'] == 'Supermod' || $_SESSION['user_rank'] == 'Admin'){
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_notes` WHERE `id` = '".$_GET['nid']."'");
            if($data != '0 rows'){
                if($data[0]['visibility'] != 'Admin' || $_SESSION['user_rank'] == 'Admin'){
                    if(functions::update_data('admin_notes','id',$_GET['nid'])){
                        $this->output_buffer .= 'The note has been updated <br /><a href="?id='.$_GET['id'].'">Return</a>';
                    }
                    else{
                        $this->output_buffer .= 'An error occured while updating the note <br /><a href="?id='.$_GET['id'].'">Return</a>';
                    }
                }
                else{
                   $this->output_buffer .= 'You do not have access to this note.<a href="?id='.$_GET['id'].'">Return</a>';
                }
            }
            else{
                $this->output_buffer .= 'This note does not exist<br /><a href="?id='.$_GET['id'].'">Return</a>';
            }
        }
        else{
            $this->output_buffer .= 'You are not allowed to edit notes.<br /><a href="?id='.$_GET['id'].'">Return</a>';   
        }
    }
    
    private function new_note(){
        if($_SESSION['user_rank'] == 'Supermod' || $_SESSION['user_rank'] == 'Admin'){
            $this->output_buffer .= str_replace('mini_header','subHeader',functions::parse_form('admin_notes','New note',array('id','posted_by','time')));
        }
        else{
            $this->output_buffer .= 'You are not allowed to add notes.<br /><a href="?id='.$_GET['id'].'">Return</a>';   
        }
    }
    
    private function do_new_note(){
        if($_SESSION['user_rank'] == 'Supermod' || $_SESSION['user_rank'] == 'Admin'){
            $data['time'] = time();
            $data['posted_by'] = $_SESSION['username'];
            if(functions::insert_data('admin_notes',$data)){
                $this->output_buffer .= 'The note has been added <br /><a href="?id='.$_GET['id'].'">Return</a>';
            }
            else{
                $this->output_buffer .= 'An error occured when adding the note<br /><a href="?id='.$_GET['id'].'">Return</a>';
            }
        }
        else{
            $this->output_buffer .= 'You are not allowed to add notes.<br /><a href="?id='.$_GET['id'].'">Return</a>';   
        }
    }
    
    private function verify_delete_note(){
        if($_SESSION['user_rank'] == 'Supermod' || $_SESSION['user_rank'] == 'Admin'){
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_notes` WHERE `id` = '".$_GET['nid']."'");
            if($data != '0 rows'){
                if($data[0]['visibility'] != 'Admin' || $_SESSION['user_rank'] == 'Admin'){
                   $this->output_buffer = '<form id="form1" name="form1" method="post" action="">
                    <div align="center"><table width="350" class="table" border="0" cellspacing="0" cellpadding="0">
                    <tr><td colspan="2" align="center" class="subHeader" style="border-top:none;">Delete item</td></tr><tr>
                    <td colspan="2" align="center" style="padding:2px;">Delete this note?</td>
                    </tr><tr><td width="224" align="center" style="padding:2px;"><input name="Submit" type="submit" value="Yes" /></td>
                    <td width="224" align="center" style="padding:2px;"><input name="Submit" type="submit" value="No" /></td>
                    </tr></table></div></form>';
                }
                else{
                   $this->output_buffer .= 'You do not have access to this note.<a href="?id='.$_GET['id'].'">Return</a>';
                }
            }
            else{
                $this->output_buffer .= 'This note does not exist<br /><a href="?id='.$_GET['id'].'">Return</a>';
            }
        }
        else{
            $this->output_buffer .= 'You are not allowed to delete notes.<br /><a href="?id='.$_GET['id'].'">Return</a>';   
        }
    }
    
    private function do_delete_note(){
        if($_SESSION['user_rank'] == 'Supermod' || $_SESSION['user_rank'] == 'Admin'){
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_notes` WHERE `id` = '".$_GET['nid']."'");
            if($data != '0 rows'){
                if($data[0]['visibility'] != 'Admin' || $_SESSION['user_rank'] == 'Admin'){
                    if($GLOBALS['database']->execute_query("DELETE FROM `admin_notes` WHERE `id` = '".$_GET['nid']."' LIMIT 1")){
                        $this->output_buffer .= 'The admin note has been deleted <br /><a href="?id='.$_GET['id'].'">Return</a>';
                    }
                }
                else{
                   $this->output_buffer .= 'You do not have access to this note.<a href="?id='.$_GET['id'].'">Return</a>';
                }
            }
            else{
                $this->output_buffer .= 'This note does not exist<br /><a href="?id='.$_GET['id'].'">Return</a>';
            }
        }
        else{
            $this->output_buffer .= 'You are not allowed to delete notes.<br /><a href="?id='.$_GET['id'].'">Return</a>';   
        }
    }
    
	//	Fire moderator:
	
	private function fire_form(){
		$this->output_buffer .= '<div align="center"><table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
    	<tr><td width="100%" align="center" style="border-top:none;" class="subHeader">Fire moderator:  </td>
    	</tr><tr><td align="center" style="padding:2px;"><form name="form1" method="post" action="">
        <select name="user">';
		$mods = $GLOBALS['database']->fetch_data("SELECT `username`,`id` FROM `users` WHERE `user_rank` = 'Moderator' ORDER BY `username` ASC");
		if($mods != '0 rows'){
			$i = 0;
			while($i < count($mods)){
				$this->output_buffer .= '<option value="'.$mods[$i]['id'].'">'.$mods[$i]['username'].'</option>';
				$i++;
			}
		}
		$this->output_buffer .= '
        </select>
        <input type="submit" name="Submit" value="Submit"></form>
      	</td></tr><tr><td align="center"><a href="?id='.$_GET['id'].'">Return</a></td>
  	  	</tr></table></div>';
	}
	
	private function do_fire(){
		if(isset($_POST['user']) && is_numeric($_POST['user'])){
			$user = $GLOBALS['database']->fetch_data("SELECT `user_rank`,`username` FROM `users` WHERE `id` = '".$_POST['user']."' LIMIT 1");
			if($user != '0 rows'){
				if($user[0]['user_rank'] == 'Moderator'){
					$GLOBALS['database']->execute_query("UPDATE `users` SET `user_rank` = 'Member' WHERE `id` = '".$_POST['user']."' LIMIT 1");
                    // Memcache Update - START
                    $GLOBALS['cache']->delete("resu:".$_POST['user']);
                    // Memcache Update - END
                    
					$this->output_buffer .= '<div align="center">You have fired '.$user[0]['username'].'<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
				}
				else{
					$this->output_buffer .= '<div align="center">This user is not a moderator<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
				}
			}
			else{
				$this->output_buffer .= '<div align="center">This user does not exist.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
		}
		else{
			//	Invalid user
			$this->output_buffer .= '<div align="center">You selected an invalid user, try again.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
	//	Hire moderator:
	
	private function hire_form(){
		$this->output_buffer .= '<div align="center"><table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
    	<tr><td width="100%" align="center" style="border-top:none;" class="subHeader">Hire moderator:</td>
    	</tr><tr><td align="center" style="padding:2px;"><form name="form1" method="post" action="">
    	<input type="text" name="username"><input type="submit" name="Submit" value="Submit"></form>
      	</td></tr><tr><td align="center"><a href="?id='.$_GET['id'].'">Return</a></td>
  	  	</tr></table></div>';
	}
	
	private function do_hire(){
		if(isset($_POST['username']) && $_POST['username'] != ''){
			$user = $GLOBALS['database']->fetch_data("SELECT `username`,`user_rank`,`id` FROM `users` WHERE `username` = '".$_POST['username']."' LIMIT 1");
			if($user != '0 rows'){
				if($user[0]['user_rank'] == 'Member' || $user[0]['user_rank'] == 'Paid'){
					$GLOBALS['database']->execute_query("UPDATE `users` SET `user_rank` = 'Moderator' WHERE `id` = '".$user[0]['id']."' LIMIT 1");					
                    // Memcache Update - START
                    $GLOBALS['cache']->delete("resu:".$user[0]['id']);
                    // Memcache Update - END
					$this->output_buffer .= '<div align="center">You have appointed '.$user[0]['username'].' as a moderator.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
				}
				else{
					$this->output_buffer .= '<div align="center">This user is already a moderator, supermod or admin.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
				}
			}
			else{
				$this->output_buffer .= '<div align="center">This user does not exist.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
		}
		else{
			$this->output_buffer .= '<div align="center">You entered an invalid user<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
	//	Warn user:
	
	private function warning_form(){
		$this->output_buffer .= '<div align="center"><form name="form1" method="post" action="">
    	<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
      	<tr><td colspan="2" align="center" style="border-top:none;" class="subHeader">Warn user: </td>
      	</tr><tr><td colspan="2" align="center"><b>About warnings:</b><br />Warnings will alert a user to misconduct on their part without banning them.</p>
        </td></tr><tr><td colspan="2" align="center">&nbsp;</td></tr><tr>
        <td width="30%" align="center" style="padding:2px;">Username:</td>
        <td width="70%" align="left" style="padding:2px;"><input name="username" type="text" id="username"></td>
      	</tr><tr><td align="center" style="padding:2px;">Reason:</td>
        <td align="left" style="padding:2px;"><input name="reason" type="text" id="reason" size="35"></td>
      	</tr><tr><td colspan="2" align="center" style="font-weight:bold;">The message below will be stored and sent to the user: </td>
      	</tr><tr><td align="center" colspan="2" style="padding:2px;">Message:</td></tr><tr>
        <td align="center" colspan="2" style="padding:2px;"><textarea name="message" rows="8" style="width:100%;"></textarea></td>
      	</tr><tr><td colspan="2" align="center" style="padding:2px;"><input type="submit" name="Submit" value="Submit"></td></tr><tr>
        <td colspan="2" align="center"><a href="?id='.$_GET['id'].'">Return</a></td>
      	</tr></table></form></div>';
	}
	
	private function give_warning(){
		if($_POST['username'] != ''){
			$user = $GLOBALS['database']->fetch_data("SELECT `username`,`id` FROM `users` WHERE `username` = '".$_POST['username']."'");
			if($user != '0 rows'){
				if(functions::store_content($_POST['message']) != ''){
					if($_POST['reason'] != ''){
						//	Log the warning:
						$GLOBALS['database']->execute_query("INSERT INTO `moderator_log` ( `time` , `uid` , `moderator` , `action` , `reason` , `message` )VALUES ('".time()."', '".$user[0]['id']."', '".$_SESSION['username']."', 'warning', '".$_POST['reason']."', '".functions::store_content($_POST['message'])."');");
						//	Send the warning message:
						$GLOBALS['database']->execute_query("INSERT INTO `users_pm` (`sender`,`reciever`,`time`,`message`,`subject`)VALUES('".$_SESSION['username']."','".$user[0]['username']."','".time()."','".functions::store_content($_POST['message'])."', 'Official warning!')");
						
                        // Update Memcache
                        $pmcache = $GLOBALS['cache2']->get("pmmp:".$user[0]['id']);
                        if( !$pmcache ){           
                            $GLOBALS['cache2']->add("pmmp:".$user[0]['id'],  'Yup', false, 86400);
                        }
                        
                        //	Output success:
						$this->output_buffer .= '<div align="center">Your warning has been sent.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
					}
					else{
						$this->output_buffer .= '<div align="center">You did not specify a reason.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
					}
				}
				else{
					$this->output_buffer .= '<div align="center">You did not specify a message to send to the user.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
				}
			}
			else{
				$this->output_buffer .= '<div align="center">This user does not exist.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
		}
		else{
			$this->output_buffer .= '<div align="center">You did not specify a username.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
	//	Ban user:
	
	private function ban_form(){
		$this->output_buffer .= '<div align="center"><form name="form1" method="post" action="">
    	<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
      	<tr><td colspan="2" align="center" style="font-weight:bolder;padding:2px;border-bottom:1px solid #000000">Ban user: </td>
      	</tr><tr><td colspan="2" align="center">&nbsp;</td></tr><tr>
        <td width="30%" align="center" style="padding:2px;">Username:</td>
        <td width="70%" align="left" style="padding:2px;"><input name="username" type="text" id="username"></td>
      	</tr><tr><td align="center" style="padding:2px;">Length:</td>
        <td align="left" style="padding:2px;"><select name="ban_time"><option>30 minutes</option>
        <option>1 hour</option><option>8 hours</option><option>12 hours</option>
        <option>1 day</option><option>2 days</option><option>1 week</option>
        </select></td></tr><tr><td align="center" style="padding:2px;">Reason:</td>
        <td align="left" style="padding:2px;"><input name="reason" type="text" id="reason" size="35"></td>
      	</tr><tr><td colspan="2" align="center" style="font-weight:bold;">The message below will be stored and shown to the user: </td>
      	</tr><tr><td align="center" colspan="2" style="padding:2px;">Message:</td></tr><tr>
        <td align="center" colspan="2" style="padding:2px;"><textarea name="message" rows="8" style="width:100%;"></textarea></td>
      	</tr><tr><td colspan="2" align="center" style="padding:2px;"><input type="submit" name="Submit" value="Submit"></td></tr><tr>
        <td colspan="2" align="center"><a href="?id='.$_GET['id'].'">Return</a></td></tr>
    	</table></form></div>';
	}
	
	private function give_ban(){
		if($_POST['username'] != ''){
			$user = $GLOBALS['database']->fetch_data("SELECT `username`,`id`,`ban_time`,`user_rank` FROM `users`,`users_timer` WHERE `username` = '".$_POST['username']."' AND `id` = `userid`");
			if($user != '0 rows'){
				if($user[0]['user_rank'] == 'Paid' || $user[0]['user_rank'] == 'Member' || $user[0]['user_rank'] == 'Moderator'){
					if(functions::store_content($_POST['message']) != ''){
						if($_POST['reason'] != ''){
							//	Determine the bantime:
							if($_POST['ban_time'] == '30 minutes'){
								$bantime = time() + 1800;
							}
							elseif($_POST['ban_time'] == '1 hour'){
								$bantime = time() + 3600;
							}
							elseif($_POST['ban_time'] == '8 hours'){
								$bantime = time() + 28800;
							}
							elseif($_POST['ban_time'] == '12 hours'){
								$bantime = time() + 43200;
							}
							elseif($_POST['ban_time'] == '1 day'){
								$bantime = time() + 86400;							
							}
							elseif($_POST['ban_time'] == '2 days'){
								$bantime = time() + 172800;
							}
							elseif($_POST['ban_time'] == '1 week'){
								$bantime = time() + 604800;
							}
							if($user[0]['ban_time'] == 0){
								//	Log the ban:
								$GLOBALS['database']->execute_query("INSERT INTO `moderator_log` ( `time` , `uid` , `duration`, `moderator` , `action` , `reason` , `message` )VALUES ('".time()."', '".$user[0]['id']."', '".$_POST['ban_time']."','".$_SESSION['username']."', 'ban', '".$_POST['reason']."', '".functions::store_content($_POST['message'])."');");
							    //	Ban the user:
								$GLOBALS['database']->execute_query("UPDATE `users_timer` SET `last_login` = 0, `ban_time` = '".$bantime."',`logout_timer` = '0' WHERE `userid` = '".$user[0]['id']."' LIMIT 1");
                                // Update Memcache
                                $GLOBALS['cache']->delete("resu:".$user[0]['id']);
								//	Output success:
								$this->output_buffer .= '<div align="center">'.$user[0]['username'].' has been banned.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
							}
							else{
								$this->output_buffer .= '<div align="center">'.$user[0]['username'].' is already banned.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
							}
						}
					else{
							$this->output_buffer .= '<div align="center">You did not specify a reason.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
						}
					}
					else{
						$this->output_buffer .= '<div align="center">You did not specify a message to send to the user.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
					}
				}
				else{
					$this->output_buffer .= '<div align="center">You cannot ban administrators or supermods<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
				}
			}
			else{
				$this->output_buffer .= '<div align="center">This user does not exist.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
		}
		else{
			$this->output_buffer .= '<div align="center">You did not specify a username.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
	private function unban_form(){
		if(isset($_GET['uid']) && is_numeric($_GET['uid']) && isset($_GET['time']) && is_numeric($_GET['time'])){
			$banned = $GLOBALS['database']->fetch_data("SELECT `username`,`id` FROM `users` WHERE `id` = '".$_GET['uid']."' LIMIT 1");
			$this->output_buffer .= '<div align="center"><br /><form name="form1" method="post" action="">
  			<table width="95%" class="table" border="0" cellspacing="0" cellpadding="0"><tr>
      		<td width="134%" style="text-align:center;border-bottom:1px solid #000000;font-weight:bold;">Unban user </td></tr>
    		<tr><td align="center">Unbanning the user: '.$banned[0]['username'].'</td></tr><tr>
      		<td align="center" style="font-weight:bold;">Reason for unbanning: </td></tr><tr>
      		<td align="center"><textarea name="override_reason" rows="5" style="width:95%;"></textarea></td></tr>
    		<tr><td align="center" style="padding:5px;"><input type="submit" name="Submit" value="Unban"><input name="time" type="hidden" value="'.$_GET['time'].'">
        <input name="uid" type="hidden" value="'.$_GET['uid'].'"></td></tr>
  			</table></form></div>';
		}
		else{
			$this->output_buffer .= '<div align="center">You specified an invalid user.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
	private function do_unban(){
		if(isset($_POST['uid']) && is_numeric($_POST['uid']) && isset($_POST['time'])){
				if(strlen($_POST['override_reason']) > 10){
					$user = $GLOBALS['database']->fetch_data("SELECT `username`,`id`,`ban_time` FROM `users`,`users_timer` WHERE `id` = '".$_POST['uid']."' AND `id` = `userid`");
					if($user != '0 rows' && $user[0]['ban_time'] > 0){
						$GLOBALS['database']->execute_query("UPDATE `moderator_log` SET `override_reason` = '".functions::store_content($_POST['override_reason'])."', `override_by` = '".$_SESSION['username']."' WHERE `uid` = '".$_POST['uid']."' AND `time` = '".$_POST['time']."' LIMIT 1");
						$GLOBALS['database']->execute_query("UPDATE `users_timer` SET `ban_time` = 0 WHERE `userid` = '".$_POST['uid']."'");
                        // Update Memcache
                        $GLOBALS['cache']->delete("resu:".$_POST['uid']);
						$this->output_buffer .= '<div align="center">You have unbanned '.$user[0]['username'].'<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
					}
					else{
						//	This user does not exist?
						$this->output_buffer .= '<div align="center">This user does not exist, or is not currently banned.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
					}
				}
				else{
					$this->output_buffer .= '<div align="center">You did not specify a valid reason for unbanning this user.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
				}
		}
		else{
			//	no or invalid user set.
			$this->output_buffer .= '<div align="center">You did not specify a user.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
	//	Tavern ban user:
	
	private function tavernban_form(){
		$this->output_buffer .= '<div align="center"><form name="form1" method="post" action="">
    	<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0"><tr>
        <td colspan="2" align="center" style="border-top:none;" class="subHeader">Tavern ban  user: </td>
      	</tr><tr><td colspan="2" align="center">&nbsp;</td></tr><tr>
        <td width="30%" align="center" style="padding:2px;">Username:</td>
        <td width="70%" align="left" style="padding:2px;"><input name="username" type="text" id="username"></td>
      	</tr><tr><td align="center" style="padding:2px;">Reason:</td>
      	<td align="left" style="padding:2px;"><input name="reason" type="text" id="reason" size="35"></td>
      	</tr><tr><td colspan="2" align="center" style="font-weight:bold;">The message below will be stored and sent to the user: </td>
      	</tr><tr><td align="center" colspan="2" style="padding:2px;">Message:</td></tr><tr>
        <td align="center" colspan="2" style="padding:2px;"><textarea name="message" rows="8" style="width:100%;"></textarea></td>
      	</tr><tr><td colspan="2" align="center" style="padding:2px;"><input type="submit" name="Submit" value="Submit"></td>
      	</tr><tr><td colspan="2" align="center"><a href="?id='.$_GET['id'].'">Return</a></td>
      	</tr></table></form></div>';
	}
	
	private function give_tavernban(){
		if($_POST['username'] != ''){
			$user = $GLOBALS['database']->fetch_data("SELECT `username`,`id`,`user_rank` FROM `users` WHERE `username` = '".$_POST['username']."'");
			if($user != '0 rows'){
				if(functions::store_content($_POST['message']) != ''){
					if($_POST['reason'] != ''){
						if($user[0]['user_rank'] == 'Member' || $user[0]['user_rank'] == 'Paid'){
							//	Log the warning:
							$GLOBALS['database']->execute_query("INSERT INTO `moderator_log` ( `time` , `uid` , `moderator` , `action` , `reason` , `message` )VALUES ('".time()."', '".$user[0]['id']."', '".$_SESSION['username']."', 'tavern-ban', '".$_POST['reason']."', '".functions::store_content($_POST['message'])."');");
							$GLOBALS['database']->execute_query("UPDATE `users` SET `post_ban` = 1 WHERE `id` = '".$user[0]['id']."' LIMIT 1");
                            // Memcache Delete
                            $GLOBALS['cache']->delete("resu:".$user[0]['id']);
							//	Send the warning message:
							$GLOBALS['database']->execute_query("INSERT INTO `users_pm` (`sender`,`reciever`,`time`,`message`,`subject`)VALUES('".$_SESSION['username']."','".$user[0]['username']."','".time()."','".functions::store_content($_POST['message'])."','You have been banned from the tavern')");
							//	Output success:
							$this->output_buffer .= '<div align="center">'.$user[0]['username'].' has been tavern-banned.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
						}
						else{
							$this->output_buffer .= '<div align="center">You cannot tavern-ban staff members.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
						}
					}
					else{
						$this->output_buffer .= '<div align="center">You did not specify a reason.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
					}
				}
				else{
					$this->output_buffer .= '<div align="center">You did not specify a message to send to the user.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
				}
			}
			else{
				$this->output_buffer .= '<div align="center">This user does not exist.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
		}
		else{
			$this->output_buffer .= '<div align="center">You did not specify a username.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
	private function un_tavern_ban(){
		if(isset($_GET['uid']) && is_numeric($_GET['uid'])){
			$user = $GLOBALS['database']->fetch_data("SELECT `username`,`id`,`ban_time` FROM `users`,`users_timer` WHERE `id` = '".$_GET['uid']."' AND `id` = `userid`");
			if($user != '0 rows'){
				$GLOBALS['database']->execute_query("UPDATE `users` SET `post_ban` = 0 WHERE `id` = '".$_GET['uid']."'");
                // Memcache Delete
                $GLOBALS['cache']->delete("resu:".$_GET['uid']);
				$this->output_buffer .= '<div align="center">You have unbanned '.$user[0]['username'].' from the tavern.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
			else{
				//	This user does not exist?
				$this->output_buffer .= '<div align="center">This user does not exist.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
		}
		else{
			//	no or invalid user set.
			$this->output_buffer .= '<div align="center">You did not specify a user.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
	//	Show banlog.
	
	private function show_banlog(){
		$this->output_buffer .= '<div align="center">
  		<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
    	<tr><td width="100%" colspan="4" align="center" style="border-top:none;" class="subHeader">Currently banned users </td>
    	</tr>';
		$banned = $GLOBALS['database']->fetch_data("SELECT users.username,users.id FROM `users`,`users_timer` WHERE `ban_time` > 0 AND `userid` = `id` ORDER BY `username`");
		if($banned != '0 rows'){
			$this->output_buffer .= '<tr><td colspan="4" align="center" style="color:red;border-bottom:1px solid #000000;">For more details, check the user\'s details</td></tr><tr>
      			<td width="20%" align="left" style="padding-left:5px;border-bottom:1px solid #000000;font-weight:bold;">Username:</td>
      			<td width="20%" align="left" style="border-bottom:1px solid #000000;font-weight:bold;">Moderator:</td>
      			<td width="40%" align="left" style="border-bottom:1px solid #000000;font-weight:bold;">Reason</td>
      			<td width="20%" align="left" style="border-bottom:1px solid #000000;font-weight:bold;">&nbsp;</td>
      			</tr>';
			$i = 0;
			while($i < count($banned)){
				$temp = $GLOBALS['database']->fetch_data("SELECT moderator_log.* FROM `moderator_log` WHERE `uid` = '".$banned[$i]['id']."' AND `action` = 'ban' ORDER BY `time` DESC LIMIT 1");
				if($temp == '0 rows'){
					unset($temp);
					$temp[0]['moderator'] = 'None';
					$temp[0]['reason'] = 'Unspecified';
				}
				$this->output_buffer .= '<tr class="row'.(($i % 2) + 1).'">
      			<td align="left" style="padding-left:5px;border-bottom:1px solid #000000;">'.$banned[$i]['username'].'</td>
      			<td align="left" style="border-bottom:1px solid #000000;">'.$temp[0]['moderator'].'</td>
      			<td align="left" style="border-bottom:1px solid #000000;">'.$temp[0]['reason'].'</td>
      			<td align="left" style="border-bottom:1px solid #000000;"><a href="?id='.$_GET['id'].'&act=unban&uid='.$banned[$i]['id'].'&time='.$temp[0]['time'].'">Unban</a></td>
      			</tr>';
				$i++;
			}
		}
		else{
			$this->output_buffer .= '<tr><td colspan="4" align="center">No users are currently banned</td></tr>';
		}
		$this->output_buffer .= '<tr><td colspan="4" align="center"><a href="?id='.$_GET['id'].'">Return</a></td></tr></table><br /></div>';
	}
	
	//	User wrapsheet.
	
	private function user_form(){
		$this->output_buffer .= '<div align="center"><table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
    	<tr><td width="100%" align="center" style="border-top:none;" class="subHeader">User details:</td>
    	</tr><tr><td align="center" style="padding:2px;"><form name="form1" method="post" action="">
    	<input type="text" name="username"><input type="submit" name="Submit" value="Submit"></form>
      	</td></tr><tr><td align="center"><a href="?id='.$_GET['id'].'">Return</a></td>
  	  	</tr></table></div>';
	}
	
	private function show_user_sheet(){
		//	Fetch user:
		if(isset($_POST['Submit'])){
			$user = $GLOBALS['database']->fetch_data("SELECT `id`,`username` FROM `users` WHERE `username` = '".$_POST['username']."' LIMIT 1");
		}
		elseif(isset($_GET['uid']) && is_numeric($_GET['uid'])){
			$user = $GLOBALS['database']->fetch_data("SELECT `id`,`username` FROM `users` WHERE `id` = '".$_GET['uid']."' LIMIT 1");
		}
		else{
			$user = '0 rows';
		}
		//	Show output:
		if($user != '0 rows'){
			$sheet = $GLOBALS['database']->fetch_data("SELECT * FROM `moderator_log` WHERE `uid` = '".$user[0]['id']."' ORDER BY `time` DESC");
			$this->output_buffer .= '<div align="center"><table width="95%" border="0" cellpadding="0" cellspacing="0" class="table">
    		<tr><td colspan="5" align="center" style="border-top:none;" class="subHeader">Details of: '.$user[0]['username'].'</td></tr>';
			if($sheet != '0 rows'){
				$this->output_buffer .= '<tr><td style="font-weight:bold;border-bottom:1px solid #000000;padding-left:5px;">Type:</td><td style="font-weight:bold;border-bottom:1px solid #000000;">Moderator</td>
				<td style="font-weight:bold;border-bottom:1px solid #000000;">Duration</td><td style="font-weight:bold;border-bottom:1px solid #000000;">On:</td><td style="border-bottom:1px solid #000000;">&nbsp;</td></tr>';
				$i = 0;
				while($i < count($sheet)){
					$this->output_buffer .= '<tr class="row'.(($i % 2) + 1).'">
      				<td width="15%" style="padding-left:5px;">'.$sheet[$i]['action'].'</td>
      				<td width="16%">'.$sheet[$i]['moderator'].'</td>
      				<td width="23%">'.$sheet[$i]['duration'].'</td>
      				<td width="23%">'.date('d-m-Y G:i:s',$sheet[$i]['time']).'</td>
      				<td width="23%" align="center"><a href="?id='.$_GET['id'].'&act=details&time='.$sheet[$i]['time'].'&uid='.$sheet[$i]['uid'].'">Details</a></td>
                    </tr>';
					$i++;
				}
			}
			else{
				$this->output_buffer .= '<tr><td colspan="5" align="center"><b>No violations in the moderator log</b></td></tr>';
			}
			$this->output_buffer .= '</table><br />';
			//	Show reports against this user: (except unfounded)
			$data = $GLOBALS['database']->fetch_data("SELECT `uid`,`rid`,`type`,`time`,`status` FROM `user_reports` WHERE `status` != 'ungrounded' AND `uid` = '".$user[0]['id']."' ORDER BY `time` DESC");
			if($data != '0 rows'){
				$i = 0;
				$this->output_buffer .= '<table width="95%" class="table" border="0" cellspacing="0" cellpadding="0">
  				<tr><td colspan="4" style="text-align:center;border-top:none;" class="subHeader">Reports filed against this user: </td>
    			</tr><tr><td style="padding-left:5px;">Filed by: </td><td>Status:</td><td>Type:</td><td>&nbsp;</td>
  				</tr>';
				while($i < count($data)){
					$userdata = $GLOBALS['database']->fetch_data("SELECT `username`,`id` FROM `users` WHERE `id` = ".$data[$i]['rid']);
					$this->output_buffer .= '<tr><td width="25%" style="padding-left:5px;">'.$userdata[0]['username'].'</td>
    				<td width="25%">'.$data[$i]['status'].'</td>
    				<td width="25%">'.$data[$i]['type'].'</td>
    				<td width="25%" align="center"><a href="?id='.$_GET['id'].'&act=reports&page=details&uid='.$data[$i]['uid'].'&time='.$data[$i]['time'].'&rid='.$data[$i]['rid'].'">Show details</a></td>
  					</tr>';
					$i++;
				}
                $this->output_buffer .= '</table>';
			}
			$this->output_buffer .= '</div>';
		}
		else{
			$this->output_buffer .= '<div align="center">No user was specified or this user does not exist.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
	private function show_details(){
		//	Fetch data:
		if(isset($_GET['time']) && is_numeric($_GET['time'])){
			if(isset($_GET['uid']) && is_numeric($_GET['uid'])){
				$data = $GLOBALS['database']->fetch_data("SELECT moderator_log.*, users.username FROM `moderator_log`,`users` WHERE `time` = '".$_GET['time']."' AND `uid` = '".$_GET['uid']."' AND `uid` = `id`");
			}
			else{
				$data = '0 rows';
			}
		}
		else{
			$data = '0 rows';
		}
		//	Show data:
		if($data != '0 rows'){
			$this->output_buffer .= '<div align="center"><table width="95%" border="0" cellpadding="0" cellspacing="0" class="table">
    		<tr><td colspan="2" align="center" style="border-top:none;" class="subHeader">'.$data[0]['action'].' details: </td>
    		</tr><tr><td width="30%" style="font-weight:bold;padding-left:5px;">Username:</td>
      		<td width="70%">'.$data[0]['username'].'</td></tr><tr>
      		<td style="font-weight:bold;padding-left:5px;">Moderator:</td><td>'.$data[0]['moderator'].'</td></tr><tr>
      		<td style="font-weight:bold;padding-left:5px;">Type:</td><td>'.$data[0]['action'].'</td></tr><tr>
      		<tr><td style="font-weight:bold;padding-left:5px;">Date:</td><td>'.date('d-m-Y G:i:s',$data[0]['time']).'</td></tr>
      		<td style="font-weight:bold;padding-left:5px;">Reason:</td><td>'.$data[0]['reason'].'</td></tr><tr>
      		<td colspan="2" align="center" class="subHeader">Message:</td></tr>
      		<tr><td colspan="2" align="center" style="border-bottom:1px solid #000000;padding:5px;">'.functions::parse_BB($data[0]['message']).'</td></tr>';
			if($data[0]['override_reason'] != ''){
				$this->output_buffer .= '<tr>
      			<td colspan="2" align="center" class="subHeader">Override reason:</td></tr>
      			<tr><td colspan="2" align="center" style="border-bottom:1px solid #000000;padding:5px;">'.functions::parse_BB($data[0]['override_reason']).'</td></tr>
      			<tr><td align="center" style="border-bottom:1px solid #000000;">Override by:</td><td align="center" style="border-bottom:1px solid #000000;">'.$data[0]['override_by'].'&nbsp;</td></tr>';
			}
			$this->output_buffer .= '
      		<tr><td colspan="2" align="center"><a href="?id='.$_GET['id'].'&act=check_user&uid='.$data[0]['uid'].'">Return to user violations</a></td>
    		</tr><td colspan="2" align="center" ><a href="?id='.$_GET['id'].'">Return to moderator controls</a></td></tr></table><br /></div>';
		}
		else{
			$this->output_buffer .= '<div align="center">This violation does not exist.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}

	//	Tavern ban log:
	
	private function tavern_ban_log(){
		if(!isset($_GET['min']) || !is_numeric($_GET['min']) || $_GET['min'] < 0){
			$min = 0;
			$newmini = 20;
			$newminm = 0;
		}
		else{
			$min = $_GET['min'];
			$newminm = $min - 20;
			if($newminm < 0){
				$newminm = 0;
			}
			$newmini = $min + 20;
		}
		if(!isset($_GET['max']) || !is_numeric($_GET['max']) || $_GET['max'] < 0){
			$max = 20;
			$newmaxi = 40;
			$newmaxm = 20;
		}
		else{
			$max = $_GET['max'];
			$min = $_GET['min'];
			$newmaxm = $max - 20;
			if($newmaxm < 20){
				$newmaxm = 20;
			}
			$newmaxi = $max + 20;
		}
		//echo ':'.$min.':'.$max.':';
		$users = $GLOBALS['database']->fetch_data("SELECT `username`,`id` FROM `users` WHERE `post_ban` = 1 ORDER BY `username` LIMIT ".$min.",".$max."");
		$this->output_buffer .= '<div align="center">
  		<table border="0" cellpadding="0" cellspacing="0" class="table" width="95%">
        <tr><td colspan="5" align="center" style="border-top:none;" class="subHeader">Tavern banned users</td></tr>';
		if($users != '0 rows'){
			$this->output_buffer .= '<tr>
      		<td width="15%" align="left" style="padding-left:5px;border-bottom:1px solid #000000;"><b>Username</b></td>
      		<td width="15%" align="center" style="border-bottom:1px solid #000000;"><b>Banned by:</b></td>
      		<td width="20%" align="center" style="border-bottom:1px solid #000000;"><b>Date:</b></td>
      		<td width="40%" align="left" style="border-bottom:1px solid #000000;"><b>Reason:</b></td>
      		<td width="10%" align="left" style="border-bottom:1px solid #000000;">&nbsp;</td>
    		</tr>';
			$i = 0;
			while($i < count($users)){
				$temp = $GLOBALS['database']->fetch_data("SELECT * FROM `moderator_log` WHERE `uid` = '".$users[$i]['id']."' AND `action` = 'tavern-ban' ORDER BY `time` DESC LIMIT 1");
    			if($temp == '0 rows'){
    				unset($temp);
    				$temp[0]['moderator'] = 'Unknown';
    				$temp[0]['reason'] = 'Unknown';
    				$temp[0]['time'] = time();
    			}
				$this->output_buffer .= '<tr>
    	  		<td align="left" style="padding-left:5px;">'.$users[$i]['username'].'</td>
    	  		<td align="center">'.$temp[0]['moderator'].'</td>
    	 		<td align="center">'.date('Y-m-d',$temp[0]['time']).'</td>
    			<td align="left">'.$temp[0]['reason'].'</td>
    			<td align="left"><a href="?id='.$_GET['id'].'&act=untban&uid='.$users[$i]['id'].'">Unban</a></td>
    			</tr>';
    			$i++;
			}
		}
		else{
			$this->output_buffer .= '<tr><td colspan="5" align="center">No users found</td></tr>';
		}
		$this->output_buffer .= '<tr><td colspan="2" align="center" style="border-top:1px solid #000000;"><a href="?id='.$_GET['id'].'&act=users&min='.($newminm).'&max='.($newmaxm).'">&laquo; Previous</a></td><td colspan="3" align="center" style="border-top:1px solid #000000;"><a href="?id='.$_GET['id'].'&act=users&max='.($newmaxi).'&min='.($newmini).'">Next &raquo;</a></td></tr>';
		$this->output_buffer .= '<tr><td colspan="5" align="center"><a href="?id='.$_GET['id'].'">Return</a></td></tr></table></div>';
	}
	
	//	User generated reports:
	
	private function report_main_screen(){
		$this->output_buffer .= '<div align="center"><br />
		<table width="95%" class="table" border="0" cellspacing="0" cellpadding="0"><tr>
	    <td colspan="2" align="center" style="border-top:none;" class="subHeader">User reports </td></tr>
  		<tr><td align="center" width="50%"><a href="?id='.$_GET['id'].'&act=reports&page=unviewed">Show unviewed reports</a></td>
    	<td align="center"><a href="?id='.$_GET['id'].'&act=reports&page=current">Show ongoing reports</a></td>
  		</tr></table></div>';
	}
	
	private function show_reports($type){
		$this->output_buffer .= '<div align="center">
		<br /><table width="95%" class="table" border="0" cellspacing="0" cellpadding="0">
  		<tr><td colspan="4" align="center" style="border-top:none;" class="subHeader">Reports: "'.$type.'" </td></tr>';
		$data = $GLOBALS['database']->fetch_data("SELECT `uid`,`rid`,`type`,`time` FROM `user_reports` WHERE `status` = '".$type."' ORDER BY `time` ASC");
		if($data != '0 rows'){
			$this->output_buffer .= '<tr><td align="center">User:</td><td align="center">Report by: </td>
    		<td align="center">Type:</td><td align="center">Show details: </td></tr>';
			$i = 0;
			while($i < count($data)){
				$userdata = $GLOBALS['database']->fetch_data("SELECT `username`,`id` FROM `users` WHERE `id` = '".$data[$i]['uid']."' OR `id` = ".$data[$i]['rid']);
				if($data[$i]['rid'] == $data[$i]['uid']){
					$rname = $userdata[0]['username'];
					$name = $userdata[0]['username'];
				}
				elseif($userdata[0]['id'] == $data[$i]['rid']){
					$rname = $userdata[0]['username'];
					$name = $userdata[1]['username'];
				}
				else{
					$name = $userdata[0]['username'];
					$rname = $userdata[1]['username'];
				}
  				$this->output_buffer .= '<tr><td width="25%" align="center">'.$name.'</td><td width="25%" align="center">'.$rname.'</td>
    			<td width="25%" align="center">'.$data[$i]['type'].'</td><td width="25%" align="center"><a href="?id='.$_GET['id'].'&act=reports&page=details&uid='.$data[$i]['uid'].'&time='.$data[$i]['time'].'&rid='.$data[$i]['rid'].'">Details</a></td>
  				</tr>';
  				$i++;
			}
		}
		else{
			$this->output_buffer .= '<tr><td align="center">There are no reports to show</td></tr>';
		}
  		$this->output_buffer .= '</table><br /></div>';
	}
	
	private function report_show_details(){
		if(isset($_GET['uid']) && is_numeric($_GET['uid']) && (isset($_GET['rid']) && is_numeric($_GET['rid'])) && (isset($_GET['time']) && is_numeric($_GET['time']))){
			$report = $GLOBALS['database']->fetch_data("SELECT * FROM `user_reports` WHERE `time` = '".$_GET['time']."' AND `rid` = '".$_GET['rid']."' AND `uid` = '".$_GET['uid']."' LIMIT 1");
			$userdata = $GLOBALS['database']->fetch_data("SELECT `username`,`id` FROM `users` WHERE `id` = '".$report[0]['uid']."' OR `id` = ".$report[0]['rid']);
			if($_GET['rid'] == $_GET['uid']){
				$rname = $userdata[0]['username'];
				$name = $userdata[0]['username'];
			}
			elseif($userdata[0]['id'] == $_GET['rid']){
				$rname = $userdata[0]['username'];
				$name = $userdata[1]['username'];
			}
			else{
				$name = $userdata[0]['username'];
				$rname = $userdata[1]['username'];
			}
			if($report[0]['processed_by'] != ''){
				$processed = '<a href="?id=3&act=newpm&user='.$report[0]['processed_by'].'">'.$report[0]['processed_by'].'</a>';
			}
			else{
				$processed = 'Nobody';
			}
			
			if($report[0]['type'] != 'user'){
				$message = '<tr><td colspan="4" align="center" class="subHeader">Reported message: </td>
    			</tr><tr><td colspan="4" align="center" style="padding:10px;">'.functions::parse_BB($report[0]['message']).'</td></tr>';
			}
		
			$this->output_buffer .= '<div align="center">
			<br /><table width="95%" class="table" border="0" cellspacing="0" cellpadding="0">
  			<tr><td colspan="4" style="border-top:none;" class="subHeader">User Report: </td>
    		</tr><tr><td width="25%" align="center" style="font-weight:bold;">User:</td>
    		<td width="25%" align="center"><a href="?id=16&page=profile&name='.$name.'">'.$name.'</a></td>
    		<td width="25%" align="center" style="font-weight:bold;">Reported by: </td>
    		<td width="25%" align="center"><a href="?id=16&page=profile&name='.$rname.'">'.$rname.'</a></td></tr><tr>
    		<td align="center" style="font-weight:bold;">Date:</td>
    		<td align="center">'.date('Y-m-d G:i:s',$report[0]['time']).'</td>
    		<td align="center" style="font-weight:bold;">Type:</td>
    		<td align="center">'.$report[0]['type'].'</td>
  			</tr><tr>
    		<td align="center" style="font-weight:bold;">Status:</td>
    		<td align="center">'.$report[0]['status'].'</td>
    		<td align="center" style="font-weight:bold;">Processed by:</td>
    		<td align="center">'.$processed.'</td>
  			</tr><tr>
    		<td align="center" style="font-weight:bold;">Reason:</td>
    		<td colspan="3" align="center">'.$report[0]['reason'].'</td>
    		</tr>'.$message.'<tr>
    		<td colspan="4" align="center">&nbsp;</td></tr></table><br />
  			</div>';
			if(($report[0]['processed_by'] == '' || $report[0]['processed_by'] == $_SESSION['username']) && ($report[0]['status'] != 'handled' && $report[0]['status'] != 'ungrounded')){
				$this->output_buffer .= '<div align="center"><form name="form1" method="post" action="">
				<br /><table width="95%" class="table" border="0" cellspacing="0" cellpadding="0"><tr>
    			<td style="border-top:none;" class="subHeader" align="center">Alter report status: </td>
  				</tr><tr><td align="center" style="padding:10px;color:darkred;">Altering the report status will designate you as the processing moderator, any questions regarding this matter, and it\'s processing can and will be directed at you.<br>
        		<br>Once you have set the status to anything other than &quot;unviewed&quot; no other moderator or admin can alter the report\'s status, unless you set it back to &quot;unviewed&quot;<br>
      			<br>you cannot reset &quot;ungrounded&quot; or &quot;handled&quot; reports to unviewed.</td></tr>
  				<tr><td align="center" style="padding:5px;"><select name="status">
      			<option>unviewed</option><option>in progress</option>
      			<option>ungrounded</option><option>handled</option>
    			</select></td></tr><tr>
    			<td align="center" style="padding:5px;"><input type="submit" name="Submit" value="Submit"></td>
    			</tr></table><br /></form></div>';
			}
		}
		else{
			//	Incorrect flags
		}
	}
	
	private function update_report_status(){
		if(isset($_GET['uid']) && is_numeric($_GET['uid']) && (isset($_GET['rid']) && is_numeric($_GET['rid'])) && (isset($_GET['time']) && is_numeric($_GET['time']))){
            if( $_SESSION['uid'] !== $_GET['uid'] ){
			    $GLOBALS['database']->execute_query("UPDATE `user_reports` SET 
                                `processed_by` = '".$_SESSION['username']."', 
                                `status` = '".$_POST['status']."' 
                          WHERE `rid` = '".$_GET['rid']."' AND `uid` = '".$_GET['uid']."' AND `time` = '".$_GET['time']."' LIMIT 1");
			    $this->output_buffer .= '<div align="center">The report status has been updated.</div>';
            }
            else{
                $this->output_buffer .= '<div align="center">You are not allowed to handle reports about yourself.</div>';
            }
		}
		else{
			//	Incorrect flags
		}
	}
}

$mod = new mod_panel();
?>