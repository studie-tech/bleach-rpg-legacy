<?php
/*			Tavern			*/
class tavern{
	var $output_buffer;
	var $user;
	var $location;
	var $kage;
	
	function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	function tavern(){
		//	Fetch user data
		$this->user = $GLOBALS['userdata'];
		//	Set current location
		$this->location = $this->user[0]['location'];

		//	Execute required page
		if($_GET['act'] == 'delete'){
			$this->delete_post();
		}
		elseif($_POST['Submit'] == 'Post'){
			$this->do_post();
		}
		$this->show_tavern();
		$this->return_stream();
	}
	
	function show_tavern(){
        
	 	if($this->user[0]['race'] == "Shinigami"){
			$tavern_subheader = 'Using your communication device you are able to communicate with your fellow troops';
		}
		else{
			$tavern_subheader = 'Using telepathy you are able to communicate with your fellow species';
		}
        
        if(!isset($_GET['min']) || !is_numeric($_GET['min']) || $_GET['min'] < 0){
            $min = 0;
            $newmini = 10;
            $newminm = 0;
        }
        else{
            $min = $_GET['min'];
            $newminm = $min - 10;
            if($newminm < 0){
                $newminm = 0;
            }
            $newmini = $min + 10;
        }
		

		$this->output_buffer .= '<div align="center">
  		
  		<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
  		<tr>
        <td colspan="2" class="subHeader" align="center" width="751">'.$tavern_subheader.'</td>
        </tr>
  		<tr>
  		<td width="20%" align="center" style="border-bottom:1px solid #000000;"><b>Username</b></td>
    	<td width="80%" align="center" style="border-bottom:1px solid #000000;"><b>Message</b></td>
    	</tr>';
             
              
        // Start Showing Cached Tavern
        if( $tavern = $GLOBALS['cache2']->get("nrevat:".$this->user[0]['rankid']) ){
            /*      Cache       */
            $i = $min;
            while($i < count($tavern)){
                $this->output_buffer .= '
                    <tr>
                      <td align="center" style="font-size:14px;padding-top:2px;"><span style="color:'.$tavern[$i]['color'].';">'.$tavern[$i]['user'].'</span></td>
                      <td rowspan="3" align="center" style="padding:2px; border-bottom:1px solid #000000;border-left:1px solid #000000;" valign="top">'.functions::parse_BB($tavern[$i]['message']).'</td>
                    </tr>
                    <tr>
                        <td align="center" valign="top" style="font-size:12px;">'.$tavern[$i]['user_data'].'</td>
                    </tr>
                    <tr>
                        <td align="center" valign="top" style="border-bottom:1px solid #000000;padding-bottom:5px;">';
                    if($this->user[0]['user_rank'] == 'Admin' || $this->user[0]['user_rank'] == 'Moderator' || $this->user[0]['user_rank'] == 'Supermod' || $this->kage == $this->location){
                        $this->output_buffer .= '<a href="?id='.$_GET['id'].'&act=delete&mt='.$tavern[$i]['time'].'&uid='.$tavern[$i]['user_id'].'" style="font-size:11px;"><img src="./images/trash.gif" alt="Delete message" style="border:none;"></a>';
                    }
                    $this->output_buffer .= '<a href="?id=15&act=tavern&mt='.$tavern[$i]['time'].'&uid='.$tavern[$i]['user_id'].'">
                                                <img src="./images/report.gif" alt="Report message" style="border:none;"></a>
                                             <a href="?id=17&act=profile&name='.$tavern[$i]['user'].'">
                                                <img src="./images/profile.png" alt="View profile" style="border:none;"></a>
                                             </td>
                    </tr>';
                
                $i++;
            }
        }
        
		
        $this->output_buffer .= '<tr>
                        <td colspan="2" align="center" style="border-top:1px solid #000000;"><a href="?id='.$_GET['id'].'&min='.$newminm.'">&laquo; Newer</a> - <a href="?id='.$_GET['id'].'&min='.$newmini.'">Older &raquo;</a></td>
                    </tr>';
		
    	$this->output_buffer .= '</table></div>';
    	if($this->user[0]['post_ban'] == 0){
			$this->output_buffer .= '<br /><br />
			<div align="center">	
			<form id="form1" name="form1" method="post" action="?id='.$_GET['id'].'">
			<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
		    <tr><td align="center"><b>Post new message:</b></td></tr><tr>
      		<td style="padding:5px;" align="center"><textarea name="message" cols="30" rows="3" id="message"></textarea></td>
    		</tr><tr>
      		<td align="center" style="padding:5px;"><input type="submit" name="Submit" value="Post" /></td>
    		</tr></table></form></div>';
		}
	}
	
	function do_post(){
        $insert_time = time();
		if($_POST['message'] != '' && functions::store_content($_POST['message'] != '')){
			$message = functions::store_content($_POST['message']);
			if(strlen($message) > 5 && strlen($message) < 500){
				    //		Set username makeup:
					if($this->user[0]['user_rank'] == 'Admin'){
					    $user = '<span style="color:;font-weight:bold;">'.$this->user[0]['username'].'</span>';
                        $color = '#AA1111';
				    }
				    elseif($this->user[0]['user_rank'] == 'Moderator'){
					    $user = '<span style="color:#006633;font-weight:bold;">'.$this->user[0]['username'].'</span>';
                        $color = '#006633';
				    }
				    elseif($this->user[0]['user_rank'] == 'Supermod'){
					    $user = '<span style="color:#008080;font-weight:bold;">'.$this->user[0]['username'].'</span>';
                        $color = '#008080';
				    }
				    else{
                        $color = '#000000';
				    }
				    //		Check userdata:
				    $user_data .= $this->user[0]['rank'].' / '.$this->user[0]['race'];		

                    
				    $GLOBALS['database']->execute_query(
					    "INSERT INTO `tavern` ( `location` , `user` , `user_data` , `user_id` , `time` , `message`,`color` )
					    VALUES ('".$this->user[0]['rankid']."', '".$this->user[0]['username']."', '".$user_data."', '".$_SESSION['uid']."', '".$insert_time."', '".$message."','".$color."');"
				    );
                     
                    $insert = array(
                                    "user" => "".$this->user[0]['username']."",
                                    "message" => "".$message."",
                                    "user_data" => "".$user_data."",
                                    "time" => "".$insert_time."",
                                    "user_id" => "".$_SESSION['uid']."" ,
                                    "color" => "".$color."" 
                                    
                                 );
                    //      Update the memcache
                    if( $tavern = $GLOBALS['cache2']->get("nrevat:".$this->user[0]['rankid']) ){
                        array_unshift($tavern, $insert);
                        if( count($tavern) > 15 ){
                            array_pop($tavern);
                        }
                        // replace cache
                        $GLOBALS['cache2']->replace("nrevat:".$this->user[0]['rankid'],  $tavern, false, 1000);
                    }
                    else{
                        $cache_copy[0]  = $insert;
                        $GLOBALS['cache2']->add("nrevat:".$this->user[0]['rankid'],  $cache_copy, false, 1000);
                    }
                    
				    header('Location:?id=14');
			}
			else{
				$this->output_buffer .= '<div align="center" style="color:darkred;">Your message does not meet the size requirements of > 5 and < 500 characters</div>';
			}
		}
		else{
			$this->output_buffer .= '<div align="center" style="color:red;">You cannot post blank messages</div>';
		}
	}
	
	function delete_post(){
		if($this->user[0]['user_rank'] == 'Admin' || $this->user[0]['user_rank'] == 'Moderator' || $this->user[0]['user_rank'] == 'Supermod'){
			if(is_numeric($_GET['mt']) && is_numeric($_GET['uid'])){
				//$GLOBALS['database']->execute_query("DELETE FROM `tavern` WHERE `location` = '".$this->user[0]['rankid']."' AND `user_id` = '".$_GET['uid']."' AND `time` = '".$_GET['mt']."' LIMIT 1");
				$this->output_buffer .= '<div align="center" style="color:green;font-weight:bolder;"> The message has been deleted </div>';
                //      Update the memcache
                if( $tavern = $GLOBALS['cache2']->get("nrevat:".$this->user[0]['rankid']) ){
                    $i = 0;
                    foreach ( $tavern as $post ){
                        if( $post['time'] !== $_GET['mt'] ){
                            $new_cache[''.$i.''] = $post;
                            $i++;  
                        }
                        
                    }
                    // replace cache
                    $GLOBALS['cache2']->replace("nrevat:".$this->user[0]['rankid'],  $new_cache, false, 1000);
                }
			}
			else{
				$this->output_buffer = '<div align="center" style="color:red;">The postdata is corrupted, please try again</div>';
			}
		}
		else{
			$this->output_buffer = '<div align="center" style="color:red;">You are not allowed to delete messages</div>';
		}
	}

}
$tavern = new tavern();
?>