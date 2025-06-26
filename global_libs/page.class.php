<?php
/*------------------------------------------------------*/
/*						Page Parser Class				*/
/*------------------------------------------------------*/



class page{
	public $page_buffer;
	public $page_key_buffer;
	public $key_no;
	public $visible_content;
    
    public $logout_override;
		
	function page(){
		$this->page_buffer = 'Empty Page';
		$this->key_no = 0;
		$this->visible_content = true;
	}
	
	 function parse_layout($layout_file){
		 if(true){//ereg("Firefox",$_SERVER['HTTP_USER_AGENT'])){
			if(file_exists($layout_file)){	
	 			if($file = fopen($layout_file,'r')){
					if($buffer = fread($file,filesize($layout_file))){
						fclose($file);
						$this->page_buffer = $buffer;
					}
					else{
						//	Layout parse failed, fread error
						$GLOBALS['error']->handle_error('500','Unable to read layout file','8');
					}			
				}
				else{
					//	Layout parse failed;
					$GLOBALS['error']->handle_error('500','Unable to open the layout file','8');
				}
			}
			elseif(isset($_SESSION['uid'])){
				$GLOBALS['database']->execute_query("UPDATE `users` SET `layout` = 'default' WHERE `id` = '".$_SESSION['uid']."' LIMIT 1");
				$_SESSION['layout'] = 'default';
				$GLOBALS['error']->handle_error('500','The layout file does not exist<br />Most likely you are using an outdated layout that was removed.<br />Your layout will now automatically reset to the default layout, please refresh.','8');
			}
			else{
				$GLOBALS['error']->handle_error('500','The layout file does not exist','8');
			}
		}
		else{
		 	$file = fopen('./files/layout_firefox/firefox.htm','r');
		 	$buffer = fread($file,filesize($layout_file));
		 	fclose($file);
		 	$this->page_buffer = $buffer;
		}
	}
	
	 function insert_page_data($keyword,$insert_data){
		//	places keyword in page_key_buffer with data parsed externally, keywords are replaced from
		//	layout.html later on.
		//	Used for content / menu includer
		$this->page_key_buffer[$this->key_no][0] = $keyword;
		$this->page_key_buffer[$this->key_no][1] = $insert_data;
		$this->key_no++;
	}
	
	 function content_visibility($toggle){
		if($this->visible_content != false){
			$this->visible_content = $toggle;
		}
	}
	
     function analytics(){
        $this->page_buffer .= '';   
     }
     
     public function get_page( $id ){
        $data = $GLOBALS['cache2']->get("pageegap:".$id);
        if( !$data ){
            // Get From Database
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `pages` WHERE `id` = '".$id."' LIMIT 1");
            // Insert into cache
            $GLOBALS['cache2']->add("pageegap:".$id,  $data, false, 3600);
        }
        return $data;
    }
    
	 function load_content($user,$ally){	
		/*	Function loads page from database / includes include page
		 *	function to be adapted to circumstances, 
		 */
         if($this->logout_override == 0){
		    if(is_numeric($_GET['id']) || !isset($_GET['id'])){
			    if(!isset($_GET['id'])){ $_GET['id'] = 1; }
			    if(isset($_SESSION['uid'])&& $_GET['id']==1){ $_GET['id'] = 3; }
			    if($page_data = $this->get_page($_GET['id']) ){
                    
                    $userPageData = $GLOBALS['cache']->get("BRPGpd:".$_SESSION['uid']);
                    if( !$userPageData ){         
                        $data = array("timer" => time() , "loads" => 0);
                        $GLOBALS['cache']->add("BRPGpd:".$_SESSION['uid'],  $data, false, 10);
                    }
                    $page_time = time();
                    if( !isset($userPageData['loads']) || $userPageData['loads'] < 2 ){
                        
                        if( isset($userPageData['loads']) ){
                            if( $userPageData['timer'] == $page_time ){
                                $userPageData['loads']++;
                                $GLOBALS['cache']->replace("BRPGpd:".$_SESSION['uid'],  $userPageData, false, 10);
                            }
                            else{
                                $userPageData['loads'] = 0;
                                $userPageData['timer'] = $page_time;
                                $GLOBALS['cache']->replace("BRPGpd:".$_SESSION['uid'],  $userPageData, false, 10);
                            }
                        }
                        
                        
				        if($page_data != '0 rows'){
                            
					        $this->insert_page_data('[TITLE]',$page_data[0]['title']);
					        //	Check if user is allowed to load the page:
						        if($page_data[0]['require_login'] == 'no'){
							        if(file_exists('./content/'.$page_data[0]['content'])){
								        if(!include('./content/'.$page_data[0]['content'])){
									        //ERROR	500 inclusion error
									        $GLOBALS['error']->handle_error("404","Error including file",1);
								        }
							        }
							        else{
								        //ERROR file not found, echo 500;
								        $GLOBALS['error']->handle_error("404","this page include file does not exist",5);
							        }
						        }
						        elseif($page_data[0]['require_login'] == 'yes' && isset($_SESSION['uid'])){
							        //	Check if user rank is allowed to load the page:
							        if( stristr($page_data[0]['allow_ranks'],$user[0]['rankid']) || 
                                        $page_data[0]['allow_ranks'] == 'ALL' || 
                                        ($page_data[0]['VastoLord_access'] == 'yes' && $user[0]['level'] >= 20) 
                                    ){
								        $access_mods = '|NONE';
								        if(strpos($access_mods,$page_data[0]['access_restrictions']) !== false){
											        //	Hospitalized check
											        if(($user[0]['status'] == 'hospitalized' && $page_data[0]['hospital_access'] == 'yes') || $user[0]['status'] != 'hospitalized'){
												        //	In battle check
												        if($user[0]['status'] != 'battle' || ($user[0]['status'] == 'battle' && $page_data[0]['battle_access'] == 'ANY')){
													        if($user[0]['status'] != 'combat' || ($user[0]['status'] == 'combat' && $page_data[0]['battle_access'] == 'ANY')){
														        if(($page_data[0]['rank_access'] == 'ANY') || $user[0]['race'] == $page_data[0]['rank_access'] || ($page_data[0]['rank_access'] == 'Shinigami' && $page_data[0]['VastoLord_access'] == 'yes')){
															        //	INCLUDE of external code file required
															        if(file_exists('./content/'.$page_data[0]['content'])){
																        if(!include('./content/'.$page_data[0]['content'])){
																	        //ERROR	500 inclusion error
																	        $GLOBALS['error']->handle_error("404","Error including file",1);
																        }
															        }
															        else{
																        //ERROR file not found, echo 500;
																        $GLOBALS['error']->handle_error("404","this page include file does not exist",5);
															        }
														        }
														        elseif($user[0]['village'] == ''){
															        $this->insert_page_data('[CONTENT]','<div align="center" style="color:red;">You cannot access this page - it belongs to the enemy!<br /><a href="?id=2">Return to profile</a></div>');	
														        }
														        else{
															        $this->insert_page_data('[CONTENT]','<div align="center" style="color:red;">You cannot access this page as a villager<br /><a href="?id=2">Return to profile</a></div>');
														        }
													        }
													        else{
														        $this->insert_page_data('[CONTENT]','<div align="center" style="color:red;">You cannot visit this page while in battle!<br /><a href="?id=41">Return to combat</a></div>');
													        }
												        }
												        else{
													        $this->insert_page_data('[CONTENT]','<div align="center" style="color:red;">You cannot visit this page while in battle!<br /><a href="?id=33">Return to combat</a></div>');
												        }
											        }
											        else{
												        $this->insert_page_data('[CONTENT]','<div align="center" style="color:red;">You have been hospitalized!<br /><a href="?id=34">Go to the hospital</a></div>');
											        }
								    
									    
								        }
								        else{
									        $this->insert_page_data('[CONTENT]','<div align="center" style="color:red;">You cannot access this page at this location!</div>');
								        }
						        }
						        else{
							        $this->insert_page_data('[CONTENT]','<div align="center" style="color:red;">You are not the correct rank to access this page</div>');
						        }
					        }
					        else{
						        //echo '::'.$_SESSION['uid'].'::'.$page_data[0]['require_login'].'::';
						        $GLOBALS['error']->handle_error('500','Please log in to view this page',1);
					        }
				        }
				        else{
					        //ERROR Page not found in the database, echo 404;
					        $this->insert_page_data('[TITLE]','Error: 404!');
					        $GLOBALS['error']->handle_error("404","Page does not exist",1);
				        }
                    }
                    else{
                        if( isset( $userPageData['loads'] ) ){                                    
                            if( $userPageData['timer'] != $page_time ){
                                $userPageData['loads'] = 0;
                                $userPageData['timer'] = $page_time;
                                $GLOBALS['cache']->replace("BRPGpd:".$_SESSION['uid'],  $userPageData, false, 10);
                            }
                            $this->insert_page_data('[TITLE]','Error: 666!');
                            $GLOBALS['error']->handle_error("404","You can't view more than 2 pages / second. Please try again.",1);
                        }
                        else{
                            //ERROR Page not found in the database, echo 404;
                            $this->insert_page_data('[TITLE]','Error: 404!');
                            $GLOBALS['error']->handle_error("404","You can't view more than 2 pages / second. Please try again.",1);
                        }
                    }
			    }
			    else{
				    //ERROR Page not found in the database, echo 404;
				    $this->insert_page_data('[TITLE]','Error: 404!');
				    $GLOBALS['error']->handle_error("404","Page does not exist",1);
			    }
		    }
		    else{
			    //ERROR	invalid pagemark
			    $this->insert_page_data('[TITLE]','Error!');
			    $GLOBALS['error']->handle_error("404","Invalid pagemark: ".strip_tags($_GET['id']),2);
		    }
         }
         else{
            //  LOGOUT override
            $this->insert_page_data('[CONTENT]','<div align="center">You have been logged out<br /><a href="?id=1">Return</a></div>');    
         }
         return $buffer;
	}
	
	 function return_page(){
		//	Compile the page from the pre-parsed elements
		$i = 0;
		while($i < count($this->page_key_buffer)){
			if($this->page_key_buffer[$i][0] != '[CONTENT]' || $this->visible_content == true){
				$this->page_buffer = str_replace($this->page_key_buffer[$i][0],$this->page_key_buffer[$i][1],$this->page_buffer);
			}
			$i++;
		}
		if($this->visible_content == false){
			$this->page_buffer = str_replace('content_visible','content_hidden',$this->page_buffer);
		}

		/*	Strip all other keys	(stupid users that input keys they dont use .-.)
		while(stristr($this->page_buffer,'[') && $i < 10){
			$temp_buffer = explode('[',$this->page_buffer,2);
			$this->page_buffer = $temp_buffer[0];
			$temp_buffer = explode(']',$temp_buffer[1],2);
			$this->page_buffer .= $temp_buffer[1];
		}*/
		$this->page_buffer = str_replace('[LOGIN]','',$this->page_buffer);
		$this->page_buffer = str_replace('[CONTENT]','',$this->page_buffer);
		$this->page_buffer = str_replace('[ERROR]','',$this->page_buffer);
		$this->page_buffer = str_replace('[MENU]','',$this->page_buffer);
		$this->page_buffer = str_replace('[OPTIONS]','',$this->page_buffer);
		$this->page_buffer = str_replace('[MSG]','',$this->page_buffer);
		//	Echo the parsed page to screen
        $this->analytics();
		echo $this->page_buffer;
	}
	
	 function append_content($data,$keyword,$append_type){
		//	Find keyword instance if instantiated
		$key = -1;
		$i = 0;
		while($i < count($this->page_key_buffer)){
			if($this->page_key_buffer[$i][0] == $keyword){
				$key = $i;
	
				break;
			}
			$i++;
		}
		if($key != -1){
			if($append_type == 1){
				//	Append at start
				$this->page_key_buffer[$i][1] = $data.$this->page_key_buffer[$i][1];
			}
			else{
				//	Append at end
				$this->page_key_buffer[$i][1] .= $data;
			}
		}
		else{
			$this->insert_page_data($keyword,$data);
		}
	}
	
}
?>