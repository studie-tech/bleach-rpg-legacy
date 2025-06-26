<?php

class battle{

	/*								BattleCounter
	*		Returns an array with the new battle-stats of the loser and winner
	*/
	public function battlecounter($loser, $winner){
			$loserbattles = explode(':',''.$this->players[''.$loser.'']['battles'].'');
			$loserbattles = ''.$loserbattles['0'].':'.($loserbattles['1']+1).':'.($loserbattles['2']+1).'';
			$winnerbattles = explode(':',''.$this->players[''.$winner.'']['battles'].'');
			$winnerbattles = ''.($winnerbattles['0']+1).':'.($winnerbattles['1']).':'.($winnerbattles['2']+1).'';
			return array($loserbattles,$winnerbattles);
	}


	/*								decodetag
	*		Decipher Special Abillities and enable them: DINC, DSDEC, DSTA, REFL, CHAD
	*/
	public function decodetag($tag,$level1,$row,$level2){
		$tag = explode('|',$tag);
		foreach ($tag as $value){
			$tagC = explode(':',$value);
            // If user has bankai, translate bankai to shikai strength
			if($level1 == "Shikai" && $level2 > 19){
				$tagC['3'] /= 5; 
			}
            // If user has bankai, but already released shikai, modify values
            if($level1 == "Bankai" && $level2 > 19 && $this->abillities[''.$this->turn.'']['shikai'] == 1){
                $tagC['3'] = floor((3/4)*$tagC['3']); 
            }
			if($tagC['0'] == "DINC"){
				switch($tagC['1']){
					case "S": $this->players[''.$this->turn.'']['strength'] *= (1 + $tagC['3']/100); $type = "strength";			break;
					case "I": $this->players[''.$this->turn.'']['intelligence'] *= (1 + $tagC['3']/100); $type = "intelligence"; 	break;
					case "D": $this->players[''.$this->turn.'']['speed'] *= (1 + $tagC['3']/100); $type = "speed";	 				break;
					case "SID": 
						$this->players[''.$this->turn.'']['strength'] *= (1 + $tagC['3']/100); 
						$this->players[''.$this->turn.'']['intelligence'] *= (1 + $tagC['3']/100); 
						$this->players[''.$this->turn.'']['speed'] *= (1 + $tagC['3']/100);
						$type = "strength, intelligence and speed"; 																break;
				}
				$this->battle_log .= '</td></tr>
									 <tr class="'.$this->row.'">
									 	<td align="left" style="padding-left:5px; "></td>
									 	<td align="left" style="padding-left:5px; ">
									 	'.$this->players[''.$this->turn.'']['username'].'\'s '.$type.' increases significantly.</td>
									 	<td align="left" style="padding-left:5px; ">
										<font color="#003300">+'.$tagC['3'].'% '.$type.'</font><br />';
			}
			elseif($tagC['0'] == "DSDEC" || $tagC['0'] == "DSTA"){
				switch($tagC['1']){
					case "S": $this->players[''.$this->idle.'']['strength'] *= (1 - $tagC['3']/100); $type = "strength";			break;
					case "I": $this->players[''.$this->idle.'']['intelligence'] *= (1 - $tagC['3']/100); $type = "intelligence"; 	break;
					case "D": $this->players[''.$this->idle.'']['speed'] *= (1 - $tagC['3']/100); $type = "speed";			 		break;
					case "SID": 
						$this->players[''.$this->idle.'']['strength'] *= (1 - $tagC['3']/100); 
						$this->players[''.$this->idle.'']['intelligence'] *= (1 - $tagC['3']/100); 
						$this->players[''.$this->idle.'']['speed'] *= (1 - $tagC['3']/100);
						$type = "strength, intelligence and speed"; 												 				break;
				}
				$this->battle_log .= '</td></tr><tr class="'.$this->row.'"><td align="left" style="padding-left:5px; "></td>
									 <td align="left" style="padding-left:5px; ">'.$this->players[''.$this->idle.'']['username'].'\'s '.$type.' is drained.</td>
									 <td align="left" style="padding-left:5px; "><font color="#800000">-'.$tagC['3'].'% '.$type.'</font><br />';
			}
			elseif($tagC['0'] == "REFL"){
				$this->abillities[''.$this->turn.'']['refl'] = $tagC['3']; 
			}
			elseif($tagC['0'] == "CHAD"){
				$this->abillities[''.$this->turn.'']['reiatsu'] = $tagC['3'];
				$this->battle_log .= '</td></tr><tr class="'.$this->row.'"><td align="left" style="padding-left:5px; "></td>
									 <td align="left" style="padding-left:5px; ">'.$this->players[''.$this->turn.'']['username'].' regains some of the reiatsu just used.</td>
									 <td align="left" style="padding-left:5px; "><font color="#008000">+'.$tagC['3'].'% of consumed reiatsu</font><br />';
			}
            elseif($tagC['0'] == "TEL"){
                $this->abillities[''.$this->turn.'']['teleport'] = $tagC['3'];
            }
            elseif($tagC['0'] == "HEA"){
                $this->abillities[''.$this->turn.'']['heal'] = $tagC['3'];
            }
            elseif($tagC['0'] == "POI"){
                $this->abillities[''.$this->idle.'']['poisoned'] = $tagC['3'];
            }
		}
	}
	
	
	/*				userstrength
	*		Calculate the strength of a given user
	*/
	public function userstrength($userID){
		if($this->abillities[''.$userID.'']['bankai']==1){
			$strength = ($this->players[''.$userID.'']['strength']    + 
						 $this->players[''.$userID.'']['bankai'] * 12 + 
						 $this->players[''.$userID.'']['shikai'] * 6  + 
						 $this->players[''.$userID.'']['sword'] * 2)  * 
						 (rand(9,11) / 10);
			$this->rei_cost = 3 + (rand(0,5)-2.5) / 10;
		}
		elseif($this->abillities[''.$userID.'']['shikai']==1){
			$strength = ($this->players[''.$userID.'']['strength']   + 
						 $this->players[''.$userID.'']['shikai'] * 6 + 
						 $this->players[''.$userID.'']['sword'] * 2) * 
						 (rand(9,11) / 10);
			$this->rei_cost = 2 + (rand(0,5)-2.5) / 10;
		}
		else{
		 	if($this->players[''.$userID.'']['race'] == "Shinigami"){
			  	$temp = $this->players[''.$userID.'']['sword'] * 2;
			}
			else{
			 	$temp = $this->players[''.$userID.'']['shikai'] * 6 + $this->players[''.$userID.'']['sword'] * 2;
			}
			$strength = ($this->players[''.$userID.'']['strength'] + $temp) * (rand(9,11) / 10);
			$this->rei_cost = 1 + (rand(0,5)-2.5) / 10;
		}
		return $strength;
	}
	
	
	/*				return stream
	*		Returns the stream to the browser
	*/
	protected function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	
	/*				Battle Options
	*		The functions used for hp items, shikai, bankai etc.
	*/
	public function use_hp_item(){
	 	$query = "SELECT * FROM `users_inventory`, `items` WHERE `uid` = '".$this->player1id."' AND `itemtype` = 'item' AND items.id = users_inventory.iid ORDER BY `strength` LIMIT 1";
	 	$this->item = $GLOBALS['database']->fetch_data($query);
	 	if($this->item != '0 rows'){
		 	$this->battle_log .= ''.$this->players[''.$this->turn.'']['username'].' consumes '.$this->item[0]['name'].' and instantly feels more alive. </td>
					   					<td align="left" style="padding-left:5px; "><font color="#008000"> +'.$this->item[0]['strength'].'% health</font><br />';
		 	if($this->item[0]['stack'] > 1){
		 	 	$query = "UPDATE `users_inventory` SET `stack` = `stack` - 1 WHERE `uid` = '".$this->item[0]['uid']."' AND `iid` = '".$this->item[0]['iid']."' AND `stack` > 1 LIMIT 1";
			   	$GLOBALS['database']->execute_query($query);
			}
			else{
				$query = "DELETE FROM `users_inventory` WHERE `uid` = '".$this->item[0]['uid']."' AND `iid` = '".$this->item[0]['iid']."' AND `stack` = 1 LIMIT 1";
		    	$GLOBALS['database']->execute_query($query);
			}
			if($this->players[''.$this->turn.'']['cur_health'] + $this->players[''.$this->turn.'']['max_health']*($this->item[0]['strength']/100) > $this->players[''.$this->turn.'']['max_health']){
				$this->players[''.$this->turn.'']['cur_health'] = $this->players[''.$this->turn.'']['max_health'];
			}
			else{
				$this->players[''.$this->turn.'']['cur_health'] += $this->players[''.$this->turn.'']['max_health']*($this->item[0]['strength']/100);	
			}
		 	$this->pass = 1;
	 	}
	}
	
	
	public function use_bankai(){
        
		if($this->players[''.$this->turn.'']['race'] == "Shinigami"){
			$this->battle_log .= ''.$this->players[''.$this->turn.'']['username'].' furiouly unleashes '.$this->gen.' bankai whilst releasing large amounts of reiatsu. '; 
		}
		else{
			$this->battle_log .= ''.$this->players[''.$this->turn.'']['username'].' uses Resurrection in order to release the power sealed within '.$this->gen.' Zanpaktou. '; 
		}
		        
		$this->battle_log .= '</td><td align="left" style="padding-left:5px; "><font color="#800000">-30% Reiatsu</font><br />';
		$this->decodetag(''.$this->players[''.$this->turn.'']['abillity'].'',"Bankai",$this->row,$this->players[''.$this->turn.'']['level']);
        
        // Adjust Variables
        $rei_percentage = 0.3 * (1 - $this->abillities[''.$this->turn.'']['reiatsu'] / 100);
		$this->players[''.$this->turn.'']['cur_rei'] *= (1 - $rei_percentage) ;
		$this->abillities[''.$this->turn.'']['bankai'] = 1;
		$this->pass = 1;
        
	}	
	
	
	public function use_shikai(){
		if($this->players[''.$this->turn.'']['race'] == "Shinigami"){
			$this->battle_log .= ''.$this->players[''.$this->turn.'']['username'].' releases '.$this->gen.' zanpaktou and gains increased strength. '; 
        }
        else{
            $this->battle_log .= ''.$this->players[''.$this->turn.'']['username'].' rips off '.$this->gen.' mask and releases '.$this->gen.' special ability.'; 
        } 
        /*  Reduce Reiatsu      */
		$this->battle_log .= '</td><td align="left" style="padding-left:5px; "><font color="#800000">-20% Reiatsu</font><br />';
		$this->decodetag(''.$this->players[''.$this->turn.'']['abillity'].'',"Shikai", $this->row,$this->players[''.$this->turn.'']['level']);
		/*	Modify Variables	*/
        $rei_percentage = 0.2 * (1 - $this->abillities[''.$this->turn.'']['reiatsu'] / 100);
        $this->players[''.$this->turn.'']['cur_rei'] *= (1 - $rei_percentage) ;
		$this->abillities[''.$this->turn.'']['shikai'] = 1;
		$this->pass = 1;
		
	}
	
	public function flee_battle(){
		$user1speed = $this->players[''.$this->turn.'']['speed']*(rand(5,10)/10);
		$user2speed = $this->players[''.$this->idle.'']['speed']*(rand(5,10)/10);
	 	if($user1speed > $user2speed){
			$this->battle_log .= ''.$this->players[''.$this->turn.'']['username'].' successfully manages to outrun '.$this->players[''.$this->idle.'']['username'].' and flees the battle. </td><td align="center" style="padding-left:5px; "><br />'; 
			return true;
		}
		else{
			$this->battle_log .= ''.$this->players[''.$this->turn.'']['username'].' tries to outrun '.$this->players[''.$this->idle.'']['username'].' and flee but fails to do so. <br />'; 
			return false;
		}
		$this->battle_log 	  .= '</td><td align="center" style="padding-left:5px; ">';
	 	$this->pass = 1;
	}
	
	
	/*				Calculate Stats
	*		Calculates the new stats of the users
	*/	
	public function calculate_stats($number){
	 	if($number == 1){
			$battles = $this->battlecounter($this->turn, $this->idle);
			$this->loser = $this->players[''.$this->turn.'']['username'];
			$this->winner = $this->players[''.$this->idle.'']['username'];
            
            // Send PMs?
            $this->loserpm = 0;
            $this->winnerpm = 0;
            if($this->players[''.$this->turn.'']['bpm_block'] > 0){
                $this->loserpm = 1;
            } 
            if($this->players[''.$this->idle.'']['bpm_block'] > 0){
                $this->winnerpm = 1;
            } 
            
			$this->race = $this->players[''.$this->idle.'']['race'];
			$this->stats1 .= ", `battles` = '".$battles['1']."'";
			$this->stats2 .= ", `battles` = '".$battles['0']."'";
			if($this->current_turn >= 5){
			 	$rand = rand(1,3);
			 	switch($rand){
					case 1: $this->stats1 .= ", `strength` = `strength` + 0.25"; break;
					case 2: $this->stats1 .= ", `intelligence` = `intelligence` + 0.25"; break;
					case 3: $this->stats1 .= ", `speed` = `speed` + 0.25"; break;
				}
				$this->stats1 .= ", `experience` = `experience` + 75, `bank` = `bank` + 25";
			}
			/*	Event Character Win	*/
			if($this->players[''.$this->idle.'']['rank'] == "Event Character"){
                $this->eventactivator = $GLOBALS['database']->fetch_data("SELECT * FROM `event_options` WHERE `option` = 'activate_event' LIMIT 1");
                if($this->eventactivator[0]['value'] > 0){
			 	    $Emessage = "The ".$this->players[''.$this->turn.'']['rank']." ".$this->players[''.$this->turn.'']['username']." was slain 
				 			     by ".$this->players[''.$this->idle.'']['username']." at position ".$this->players[''.$this->idle.'']['location']."";
			 	    $GLOBALS['cache2']->add("event:",  "".$Emessage."", false, 5);
                    //$GLOBALS['database']->execute_query("UPDATE `users` SET `healed` = '".$Emessage."' WHERE `race` = '".$this->players[''.$this->turn.'']['race']."'");
                    //$GLOBALS['cache']->flush();
			 	    if(rand(1,5) == 1){
					    $position = explode(':',$this->players[''.$this->idle.'']['location']);
					    $movex = ((rand(1,3)-2) + $position[0]);
			 	 	    $movey = ((rand(1,3)-2) + $position[1]);
			 	 	    if($movex > 4){$movex = 3;}
			 	 	    if($movex < 1){$movex = 2;}
			 	 	    if($movey > 4){$movey = 3;}
			 	 	    if($movey < 1){$movey = 2;}
			 	 	    $this->Eventlocation = ''.$movex.':'.$movey.'';
					    $GLOBALS['database']->execute_query("UPDATE `users` SET `location` = '".$this->Eventlocation."' WHERE `id` = '".$this->players[''.$this->idle.'']['id']."' LIMIT 1");
				    }
                }
			}
            
			/*	Event Character Defeat	*/
			if($this->players[''.$this->turn.'']['rank'] == "Event Character"){
                $this->eventactivator = $GLOBALS['database']->fetch_data("SELECT * FROM `event_options` WHERE `option` = 'activate_event' LIMIT 1");
                if($this->eventactivator[0]['value'] > 0){
                    $check = $GLOBALS['database']->fetch_data("SELECT `cur_health` FROM `users` WHERE `id` = '".$this->players[''.$this->turn.'']['id']."' AND `cur_health` > 0 LIMIT 1");
				    if($this->check != '0 rows'){
			 		    $Emessage = "The ".$this->players[''.$this->idle.'']['rank']." ".$this->players[''.$this->idle.'']['username']." has defeated 
					 			    ".$this->players[''.$this->turn.'']['username']." thereby acquiring an artifact along with 1000 race points for the ".$this->players[''.$this->idle.'']['race']."!";
				 	    
                        $GLOBALS['cache2']->add("event:",  "".$Emessage."", false, 30);
                        //$GLOBALS['database']->execute_query("UPDATE `users` SET `healed` = '".$Emessage."'");
                        //$GLOBALS['cache']->flush();
				 	    
                        
                        $GLOBALS['database']->execute_query("UPDATE `races` SET `points` = `points` + 1000 WHERE `name` = '".$this->players[''.$this->idle.'']['race']."'");
				 	    /*	New item for user	*/
				 	    $item_data = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `type` = 'special' ORDER BY RAND() LIMIT 1");
				 	    $GLOBALS['database']->execute_query("INSERT INTO `users_inventory` ( `uid` , `iid` , `equipped` , `stack` , `timekey` , `itemtype`) VALUES 
					 									    ('".$this->players[''.$this->idle.'']['id']."', '".$item_data[0]['id']."', 'no', '1', '".time()."', '".$item_data[0]['type']."');");
					    /*	End the event	*/
					    $GLOBALS['database']->execute_query("UPDATE `event_options` SET `value` = 0  WHERE `option` = 'activate_event'");  	
				    }
                }
			}
            
			/*	Leader Defeat	*/                                                                                                                                     
			if($this->players[''.$this->turn.'']['rank'] == "Leader" && $this->players[''.$this->idle.'']['race'] == $this->players[''.$this->turn.'']['race'] && $this->clone !== 1){
				
                $this->stats1 .= ", `rank` = 'Leader'"; $this->stats2 .= ", `rank` = 'Ex-Leader'";
				$query17 = "UPDATE `races` SET `leader` = '".$this->winner."' WHERE `name` = '".$this->players[''.$this->turn.'']['race']."'";
				$GLOBALS['database']->execute_query($query17);
				/*	If the challenger was a captain-level, then edit string	*/
				if(strstr($this->players[''.$this->idle.'']['rank'],"Captain") || strstr($this->players[''.$this->idle.'']['rank'],"Espada")){
					$this->village = $GLOBALS['database']->fetch_data("SELECT * FROM `races` WHERE `name` = '".$this->players[''.$this->idle.'']['race']."'");
					$newstring = str_replace("".$this->players[''.$this->idle.'']['username']."-".$this->players[''.$this->idle.'']['id']."","-",$this->village[0]['highlevels']);
					$query17 = "UPDATE `races` SET `highlevels` = '".$newstring."' WHERE `name` = '".$this->players[''.$this->turn.'']['race']."'";
					$GLOBALS['database']->execute_query($query17);
				}
			}
            
			/*	Captain/Espada Defeat	*/
			if(
                (strstr($this->players[''.$this->turn.'']['rank'],"Captain") || strstr($this->players[''.$this->turn.'']['rank'],"Espada")) && 
			    (!strstr($this->players[''.$this->idle.'']['rank'],"Captain") && !strstr($this->players[''.$this->idle.'']['rank'],"Espada")) &&
			    $this->players[''.$this->idle.'']['race'] == $this->players[''.$this->turn.'']['race'] &&
			    $this->players[''.$this->idle.'']['rank'] !== "Leader" && 
			    $this->clone !== 1
            ){
			 	/*	Find rank for beaten captain/espada	*/
			 	$level_data = $GLOBALS['database']->fetch_data("SELECT * FROM `levels` WHERE `levelID` = '".$this->players[''.$this->turn.'']['level']."' LIMIT 1");
			 	$newrank = explode(':', $level_data[0]['rank']);
			    $newrank = $newrank[''.($this->players[''.$this->turn.'']['rankid']-1).''];
			    /*	Update queries	*/
				$this->stats1 .= ", `rank` = '".$this->players[''.$this->turn.'']['rank']."'"; 
				$this->stats2 .= ", `rank` = '".$newrank."'";
				/*	New string for race	*/
				$this->village = $GLOBALS['database']->fetch_data("SELECT * FROM `races` WHERE `name` = '".$this->players[''.$this->idle.'']['race']."'");
				$newstring = str_replace("".$this->players[''.$this->turn.'']['username']."-".$this->players[''.$this->turn.'']['id']."",
										 "".$this->players[''.$this->idle.'']['username']."-".$this->players[''.$this->idle.'']['id']."",
										 $this->village[0]['highlevels']);
				$query17 = "UPDATE `races` SET `highlevels` = '".$newstring."' WHERE `name` = '".$this->players[''.$this->turn.'']['race']."'";
				$GLOBALS['database']->execute_query($query17);
			}
		}
		elseif($number == 2){
			$battles = $this->battlecounter($this->idle, $this->turn);
			$this->loser = $this->players[''.$this->idle.'']['username'];
			$this->winner = $this->players[''.$this->turn.'']['username'];
            
            // Send PMs?
            $this->loserpm = 0;
            $this->winnerpm = 0;
            if($this->players[''.$this->idle.'']['bpm_block'] > 0){
                $this->loserpm = 1;
            } 
            if($this->players[''.$this->turn.'']['bpm_block'] > 0){
                $this->winnerpm = 1;
            }
            
			$this->race = $this->players[''.$this->turn.'']['race'];
			$this->stats2 .= ", `battles` = '".$battles['1']."'";
			$this->stats1 .= ", `battles` = '".$battles['0']."'";
			if($this->current_turn >= 5){
			 	$rand = rand(1,3);
			 	switch($rand){
					case 1: $this->stats2 .= ", `strength` = `strength` + 0.25"; break;
					case 2: $this->stats2 .= ", `intelligence` = `intelligence` + 0.25"; break;
					case 3: $this->stats2 .= ", `speed` = `speed` + 0.25"; break;
				}
			 	$this->stats2 .= ", `experience` = `experience` + 75, `bank` = `bank` + 25";
			}
			if($this->players[''.$this->idle.'']['rank'] == "Leader" && $this->players[''.$this->idle.'']['race'] == $this->players[''.$this->turn.'']['race'] && $this->clone !== 1){
				$this->stats2 .= ", `rank` = 'Leader'"; $this->stats1 .= ", `rank` = 'Ex-Leader'";
				$query17 = "UPDATE `races` SET `leader` = '".$this->winner."' WHERE `name` = '".$this->players[''.$this->turn.'']['race']."'";
				$GLOBALS['database']->execute_query($query17);
			}
		}
	}
	

	/*				Final Message
	*		Determine what final messages to be returned
	*/
	public function final_message(){
	 	if($this->winner && $this->loser){
	 	 	$this->battle_log .= '<tr><td align="center" style="padding-left:5px; " colspan="3"><center>'.$this->winner.' has beaten '.$this->loser.'</center></td></tr>';
			if($this->current_turn < 5){
				$this->battle_log .= '<tr><td align="center" style="padding-left:5px; " colspan="3"><center>However, because this match was so easy, no experience was gained.</center></td></tr>';
			}	
		}
		elseif($this->current_turn == $this->max_turns){
			$this->battle_log .= '<tr><td align="center" style="padding-left:5px; " colspan="3"><center>Both opponents realize the futility of this match, and retreat</center></td></tr>';
		}
		else{
			// One has fled, no message to be shown
		}	
	}
		

	/*				Final Message
	*		Determine what final messages to be returned
	*/
	public function send_pms(){
	 	$LoseMessage = 'You have been defeated in battle. Summary:<br /><br />
			<div align="center">
				<table width="95%" class="table" border="0" cellspacing="0" cellpadding="0"><tr>
	            	<td align="center" style="border-top:none;" colspan="2" class="subHeader" >Battle Summary</td>
	            	<td align="center" style="border-top:none;"  width="30%" class="subHeader" >Action</td>
	            </tr>'.addslashes($this->battle_log).'
				</table>';
		$WinMessage = 'You have won a battle. Summary:<br /><br />
			<div align="center">
				<table width="95%" class="table" border="0" cellspacing="0" cellpadding="0"><tr>
	            	<td align="center" style="border-top:none;" colspan="2" class="subHeader" >Battle Summary</td>
	            	<td align="center" style="border-top:none;"  width="30%" class="subHeader" >Action</td>
	            </tr>'.addslashes($this->battle_log).'
				</table>'; 
		$query1 = "INSERT INTO `users_pm` (`sender` ,`reciever` ,`time` ,`message` ,`subject` ,`read`)VALUES
										  ('N00b System', '".$this->loser."', '".time()."', '".$LoseMessage."', 'You\'ve been defeated', 'no');";
		$query2 = "INSERT INTO `users_pm` (`sender` ,`reciever` ,`time` ,`message` ,`subject` ,`read`)VALUES
										  ('Pwner System', '".$this->winner."', '".time()."', '".$WinMessage."', 'You\'ve defeated an opponent', 'no');";
		if($this->loser && $this->winner){
            if( $this->winnerpm == 1 ){
                $GLOBALS['database']->execute_query($query2);
            }
            if( $this->loserpm == 1 ){
                $GLOBALS['database']->execute_query($query1);
            }
		}	
	}
	
	
	/*				Latest Battles
	*		Updates the "latest battles" thing in travel
	*/
	public function latest_battles(){
	 	if($this->draw == 0){
		 	$this->eventactivator = $GLOBALS['database']->fetch_data("SELECT * FROM `event_options` WHERE `option` = 'latest_battles' LIMIT 1");
		 	$rand = rand(1,5);
		 	switch($rand){
				case 1: $txt = "killed"; break;
				case 2: $txt = "destroyed"; break;
				case 3: $txt = "eliminated"; break;
				case 4: $txt = "crushed"; break;
				case 5: $txt = "disposed of"; break;
			}
		 	$arr = explode(":::",$this->eventactivator[0]['related_text']);
		 	$newstring = "".$this->winner." ".$txt." ".$this->loser."";
		 	$i = 1;
		 	foreach ($arr as &$value) {
		 	 	if($i < 5){
					$newstring .= ":::".$value."";
				}
				$i++;	
			}
			$GLOBALS['database']->execute_query("UPDATE `event_options` SET `related_text` = '".$newstring."' WHERE `option` = 'latest_battles' LIMIT 1");	
		}
	}
	
	
	/*				Armor
	*		Calculate armor of user
	*/
	public function calculate_armor($target){
        $armor_itm = $GLOBALS['database']->fetch_data("SELECT SUM(`strength`) AS `armor` FROM `items`,`users_inventory` 
					 WHERE users_inventory.uid = '".$target."' AND users_inventory.iid = items.id AND (items.type = 'armor' OR items.type = 'special') and users_inventory.equipped = 'yes'");
        if($armor_itm !== "0 rows"){
            return $armor_itm[0]['armor'];
        }
        else{
            return 0;
        }
    }
    
    
    /*				attack_output
	*		Figure out what to post during attacks
	*/
    public function attack_output($division1, $division2, $division3, $damage){
 		
 		/*	Main output	*/
		$rand = rand(1,3);
		if($this->abillities[''.$this->turn.'']['bankai'] == 1){
			switch($rand){
				case 1: $this->battle_log .= ''.$this->players[''.$this->turn.'']['username'].' unleashes '.$this->gen.' enourmous power and attacks in a flash.'; break;
				case 2: $this->battle_log .= 'In a flash '.$this->players[''.$this->turn.'']['username'].' launches an enourmously powerfull attack using '.$this->gen.' Zanpaktou.'; break;
				case 3: $this->battle_log .= ''.$this->players[''.$this->turn.'']['username'].' strikes '.$this->gen.' opponent using '.$this->gen.' zanpaktou.'; break;
			}
		}
		elseif($this->abillities[''.$this->turn.'']['shikai'] == 1){
			switch($rand){
				case 1: $this->battle_log .= ''.$this->players[''.$this->turn.'']['username'].' attacks furiously using '.$this->gen.' Zanpaktou.'; break;
				case 2: $this->battle_log .= ''.$this->players[''.$this->turn.'']['username'].' uses '.$this->gen.' Zanpaktou\'s special abillity to attack. '; break;
				case 3: $this->battle_log .= ''.$this->players[''.$this->turn.'']['username'].' concentrates '.$this->gen.' reiatsu and attacks using '.$this->gen.' Zanpaktou'; break;
			}
		}
		else{
			switch($rand){
				case 1: $this->battle_log .= ''.$this->players[''.$this->turn.'']['username'].' attacks '.$this->players[''.$this->idle.'']['username'].' using brute strength.'; break;
				case 2: $this->battle_log .= ''.$this->players[''.$this->turn.'']['username'].' rushes the opponent and attacks furiously'; break;
				case 3: $this->battle_log .= ''.$this->players[''.$this->turn.'']['username'].' concentrates '.$this->gen.' strength and attacks '.$this->players[''.$this->idle.'']['username'].''; break;
			}
		}
		/*	Special Notes	*/
		if($division1 < 0.50 && $division2 < 0.5){
		 	$special = ''.$this->players[''.$this->idle.'']['username'].' is able to avoid much damage due to '.$this->gen2.' superior fighting experience and intelligence.'; 
		}
		elseif($division1 < 0.50){
			$special = ''.$this->players[''.$this->idle.'']['username'].' is able to avoid much damage due to '.$this->gen2.' superior fighting experience.'; 
		}
		elseif($division2 < 0.5){
			$special = ''.$this->players[''.$this->idle.'']['username'].' is able to avoid much damage due to '.$this->gen2.' superior intelligence.'; 
		}
		/*	Final Output	*/
		$this->battle_log .= '<br />'.$special.'</td><td align="left" style="padding-left:5px; "><font color="#000000">'.$damage.' damage points</font><br />'; 
		unset($special);
	}
	
	
	
    /*				parse_screen
	*		Gather the screen to be parsed to the browser
	*/
	public function parse_screen(){
		$this->output_buffer .= '<div align="center">
          <table width="95%" class="table" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td align="center" style="border-top:none;" class="subHeader" >Battle Start</td>
            </tr>
            <tr>
              <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td align="center" valign="middle" style="padding:10px;"><img src="';
                    
        /*		User Avatar		*/
		if(file_exists("./images/avatars/".$this->players[0]['id'].'.gif')){
			$this->output_buffer .= './images/avatars/'.$this->players[0]['id'].'.gif';
		}
		else{
			$this->output_buffer .= './images/default_avatar.gif';
		}
		$this->output_buffer .='" style="border:1px solid #000000;"/></td>
                    <td width="8%" rowspan="3" align="center" valign="middle" style="border-bottom:0px solid #000000;"><b><font size=10>VS.</font></b></td>
                    <td align="center" valign="top" style="padding-top:10px;"><img src="';
		
		/*		Opponent Avatar		*/
		if(file_exists("./images/avatars/".$this->players[1]['id'].'.gif')){
			$this->output_buffer .= './images/avatars/'.$this->players[1]['id'].'.gif';
		}
		else{
			$this->output_buffer .= './images/default_avatar.gif';
		}
		

		$this->output_buffer .= '" style="border:1px solid #000000;" /></td>
                  </tr>
                  <tr>
                    <td width="46%" align="center"><b>'.$this->players[0]['username'].'</b></td>
                    <td width="46%" align="center"><b>'.$this->players[1]['username'].'</b></td>
                  </tr>

                </table></td>
            </tr>
            <tr>
              <td><table width="100%" border="0" cellspacing="0" cellpadding="0">

                </table></td>
            </tr>
 
          </table>
          <br />
      </div>';
      
      $this->output_buffer .= '<div align="center">
          <table width="95%" class="table" border="1" cellspacing="0" cellpadding="0">
            <tr>
            	<td align="center" style="border-top:none;" colspan="2" class="subHeader" >Battle Summary</td>
            	<td align="center" style="border-top:none;"  width="30%" class="subHeader" >Action</td>
            </tr>
            '.$this->battle_log.'
          </table>
          <br />
          <br />
      </div>';
	}
	
	
    /*				Create clone for battle
	*		Create a new character based on a already given character
	*/
	public function create_opponent(){
	 	$clone_modifier = 0.85;
		$this->players[1] = array (
			"id"  		  		=> 1337,
			"username"    		=> 'Random Fella',
		    "gender"      		=> 'Male',
		    "level"  			=> $this->players[0]['level'],
		    "cur_health"  		=> $this->players[0]['max_health'] * $clone_modifier,
	    	"cur_rei"     		=> $this->players[0]['max_rei'] * $clone_modifier,
	    	"max_health"  		=> $this->players[0]['max_health'] * $clone_modifier,
	    	"max_rei"     		=> $this->players[0]['max_rei'] * $clone_modifier,
	    	"race"     			=> $this->players[0]['race'],
	    	"bankai"     		=> $this->players[0]['bankai'],
	    	"shikai"     		=> $this->players[0]['shikai'],
	    	"sword"     		=> $this->players[0]['sword'],		    
	    	"strength"     		=> $this->players[0]['strength'] * $clone_modifier,		    
	    	"speed"     		=> $this->players[0]['speed'] * $clone_modifier,		    
	    	"intelligence"     	=> $this->players[0]['intelligence'] * $clone_modifier,		    
	    	"abillity"     		=> $this->players[0]['abillity'],		    
		    "use_hp_item"     	=> 0,
            "status"            => 'awake',
		    "release_bankai"    => 2,
		    "release_shikai"    => 3,
		    "flee_battle"     	=> 0,
		    "armor"				=> $this->calculate_armor($this->players[0]['id'])
		    
		);
		//echo"".$this->show_array($this->players[1])."";
	}
	
    /* 	Function to echo an array - used for debugging	*/
	public function show_array($array) {
	    foreach ($array as $value) {
	        if (is_array($value)) {
	            show_array($value);
	        } else {
	            echo $value . "<br>";
	        }
	    }
	} 
	
	
	
    /*				Update Race Points
	*		Update the points for the race
	*/
	public function update_points(){
	 	if($this->clone !== 1){
			$query3 = "UPDATE `races` SET `points` = `points` + 1 WHERE `name` = '".$this->race."'";
			$GLOBALS['database']->execute_query($query3);
		}
	}
}

?>