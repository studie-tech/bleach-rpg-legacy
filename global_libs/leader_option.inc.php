<?php
/*
 *			Village panel kage options library
 */
class kage_options{
	private $village;
	private $output_buffer;
	
	//	Return stream to core:
	private function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	//	Get data
	public function setData($villageData, $userData){
		if(($villageData != '0 rows' && $villageData != '')){
			$this->village = $villageData;
		}
		if(($userData != '0 rows' && $userData != '')){
			$this->user = $userData;
		}
	}	
	
	public function parse(){
        if(strtolower($_SESSION['username']) == strtolower($this->village[0]['leader']) || 
		strstr($this->user[0]['rank'],"Captain") || 
		strstr($this->user[0]['rank'],"Espada")){
		 
		    if(!isset($_GET['act'])){
			    $this->kage_menu();
		    }
		    elseif($_GET['act'] == 'edit_orders'){
			    if(!isset($_POST['Submit'])){
				    $this->edit_orders();
			    }
			    else{
				    $this->do_edit();
			    }
		    }
		    elseif($_GET['act'] == 'resign'){
			    $this->resign();
		    }
            elseif($_GET['act'] == 'points' && strtolower($_SESSION['username']) == strtolower($this->village[0]['leader'])){
                if(!isset($_POST['Submit'])){
                    $this->villagePoint_menu();
                }
                else{
                    $this->villagePoint_spend();
                }  
            }
		    if(isset($_GET['act'])){
			    $this->output_buffer .= '<div align="center"><br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		    }
        }
        else{
            $this->output_buffer .= '<div align="center" style="color:darkred;">You do not have permission to access this page.<br /><a href="?id='.$_GET['id'].'&sub=kage">Return</a></div>';   
        }
		$this->return_stream();
	}
	
	private function kage_menu(){
		if(strtolower($_SESSION['username']) == strtolower($this->village[0]['leader'])){
			$points = '<a href="?id='.$_GET['id'].'&sub=kage&act=points">Spend race points</a>';
		}
		$this->output_buffer .= '<div align="center">
		<table width="95%" class="table" border="0" cellspacing="0" cellpadding="0"><tr>
    	<td width="68%" colspan="3" style="text-align:center;border-top:none;" class="subHeader">Options</td></tr>
  		<tr><td align="center" width="33%"><a href="?id='.$_GET['id'].'&sub=kage&act=edit_orders">Edit orders</a></td>
    	<td align="center" width="33%">'.$points.'</td>
		<td align="center" width="33%"><a href="?id='.$_GET['id'].'&sub=kage&act=resign">Resign</a></td>
  		</tr>
		<tr><td align="center">&nbsp;</td><td align="center"></td>
  		<td align="center">&nbsp;</td></tr></table></div>';
	}
	
	/*
	 *								MISC KAGE FUNCTIONS
	 */
	
	//	Resign from Kage:
	
	private function resign(){
	 	switch ($this->village[0]['name']){
	 	 	case "Shinigami": $leader = "Yamamoto"; break;
	 	 	case "Hollow": $leader = "Aizen"; break;
		}
		if(strtolower($_SESSION['username']) == strtolower($this->village[0]['leader'])){
			$GLOBALS['database']->execute_query("UPDATE `races` SET `leader` = '".$leader."' WHERE `name` = '".$this->village[0]['name']."' LIMIT 1");
			$GLOBALS['database']->execute_query("UPDATE `users` SET `rank` = 'Leader' WHERE `username` = '".$leader."' LIMIT 1");
			$GLOBALS['database']->execute_query("UPDATE `users` SET `rank` = 'Ex-Leader' WHERE `username` = '".$this->village[0]['leader']."' LIMIT 1");
			$this->output_buffer .= '<div align="center">You have resigned as the leader of the '.$this->village[0]['name'].'</div>';
		}
		else{
		 	/*	Give back original rank	*/
		 	$level_data = $GLOBALS['database']->fetch_data("SELECT * FROM `levels` WHERE `levelID` = '".$this->user[0]['level']."' LIMIT 1");
		 	$newrank = explode(':', $level_data[0]['rank']);
		    $newrank = $newrank[''.($this->players[''.$this->turn.'']['rankid']-1).''];
			$GLOBALS['database']->execute_query("UPDATE `users` SET `rank` = '".$newrank."' WHERE `username` = '".$this->user[0]['username']."' LIMIT 1");
			
			/*	Update race	*/
			$newstring = str_replace("".$this->user[0]['username']."-".$this->user[0]['id']."","-",$this->village[0]['highlevels']);
			$query17 = "UPDATE `races` SET `highlevels` = '".$newstring."' WHERE `name` = '".$this->user[0]['race']."'";
			$GLOBALS['database']->execute_query($query17);
			
			$this->output_bu