<?php
/* 
 *						Training library file
 *		Contains functions to train stats, general stats, and jutsu
 *		included by academy.inc.php, train.inc.php, and sensei.inc.php
 *		Version 0.1							 Last modified: 25-05-2007
 */
class training{
	protected $output_buffer;			// Output buffer for data
	protected $user;					// User data
	
	protected function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	protected function getUserData(){
		$this->user = $GLOBALS['userdata'];
	}	
	
	
    protected function get_technique(){
	 	if($this->user[0]['race'] == "Hollow"){
	 	 	switch($this->user[0]['activity']){
				case "Sword": $extra = $this->user[0]['max_health']*0.2; break;
				case "Shikai": $extra = $this->user[0]['max_health']*0.4; break;
			}
			if(isset($extra)){
				if($this->user[0]['cur_health']+$extra > $this->user[0]['max_health']){
					$temp_query = ", `cur_health` = `max_health`";
                    $GLOBALS['userdata'][0]['cur_health'] = $GLOBALS['userdata'][0]['max_health']; 
				}
				else{
					$temp_query = ", `cur_health` = `cur_health` + ".$extra."";
                    $GLOBALS['userdata'][0]['cur_health'] += $extra; 
				}
			}
		}

		$query = "UPDATE `users_timer`,`users` SET `technique` = '', 
                                                   `technique_timer` = '0', 
                                                   `activity` = NULL, 
                                                   `".strtolower($this->user[0]['activity'])."` = 
                                                   `".strtolower($this->user[0]['activity'])."` + 1, 
                                                   `experience` = `experience` + 50 
                                                   ".$temp_query." 
                WHERE `userid` = '".$_SESSION['uid']."' AND users.id = users_timer.userid";
		$GLOBALS['database']->execute_query($query);
        
        // Update Memcache
        $GLOBALS['userdata'][0]['technique'] = "";
        $GLOBALS['userdata'][0]['technique_timer'] = "0";
        $GLOBALS['userdata'][0]['activity'] = NULL;
        $GLOBALS['userdata'][0][''.strtolower($this->user[0]['activity']).''] += 1;
        $GLOBALS['userdata'][0]['experience'] += 50;
        $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);          
        
		$this->getUserData();
	}
	
	protected function train_stats(){
		//	Fetch data from database
		$this->getUserData();
		if($this->user[0]['rankid'] == 1){
		 
			switch($_GET['train']){
				case "Intelligence": $rei_cost = 30 + $this->user[0]['intelligence']; $activity = "Intelligence"; break;
				case "Strength": $rei_cost = 30 + $this->user[0]['strength']; $activity = "Strength"; break;
				case "Speed": $rei_cost = 30 + $this->user[0]['speed']; $activity = "Speed"; break;
				case "Sword": $rei_cost = 30 + $this->user[0]['sword']; $activity = "Sword"; break;
				case "Shikai": $rei_cost = 30 + $this->user[0]['shikai']; $activity = "Shikai"; break;
				case "Bankai": $rei_cost = 30 + $this->user[0]['bankai']; $activity = "Bankai"; break;
			}
		}else{
			switch($_GET['train']){
				case "Intelligence": $rei_cost = 30 + $this->user[0]['intelligence']; $activity = "Intelligence"; break;
				case "Strength": $rei_cost = 30 + $this->user[0]['strength']; $activity = "Strength"; break;
				case "Speed": $rei_cost = 30 + $this->user[0]['speed']; $activity = "Speed"; break;
				case "Sword": $rei_cost = 30 + $this->user[0]['sword']; $activity = "Search & Devour Human"; break;
				case "Shikai": $rei_cost = 30 + $this->user[0]['shikai']; $activity = "Search & Devour Hollow"; break;
				case "Bankai": $rei_cost = 30 + $this->user[0]['bankai']; $activity = "Ressurection"; break;
			}
		}
		if($this->user[0]['cur_rei'] >= $rei_cost){
		 if(($_GET['train'] == "Shikai" &&  $this->user[0]['shikai']>0)||($_GET['train'] == "Bankai" &&  $this->user[0]['bankai']>0)||($_GET['train'] != "Shikai" && $_GET['train'] != "Bankai")){
			if($this->user[0]['activity']==''){
				$query = "UPDATE `users`,`users_timer` SET `technique` = '".$_GET['train']."', 
                                                           `technique_timer` = '".(time() + 300)."', 
                                                           `cur_rei` = `cur_rei` - '$rei_cost', 
                                                           `activity` = '".$_GET['train']."'
                         WHERE users.id = users_timer.userid AND users.id = '".$_SESSION['uid']."'";
                
                // Update Memcache
                $GLOBALS['userdata'][0]['technique'] = "".$_GET['train']."";
                $GLOBALS['userdata'][0]['technique_timer'] = "".(time() + 300)."";
                $GLOBALS['userdata'][0]['cur_rei'] -= $rei_cost;
                $GLOBALS['userdata'][0]['activity'] = "".$_GET['train']."";
                $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);          
                
				
				$GLOBALS['database']->execute_query($query);
				header('Location:?id=4');
			}
			else{
				$this->output_buffer = '<div align="center" style="padding:15px;"> You are already doing something else! <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
		  }
		  else{
		     $this->output_buffer = '<div align="center">You cannot train this at the moment<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		  }
		}
		else{
			$this->output_buffer = '<div align="center">You do not have enough reiatsu to train this <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}

	

}
?>