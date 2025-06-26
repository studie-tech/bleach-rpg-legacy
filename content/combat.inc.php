<?php

include('./libs/battle.inc.php');
require_once('./libs/calc.inc.php');

class pvp_battle extends battle{
	protected $output_buffer;
	protected $battle;
	protected $players;
	
	
	/*				Main Thread
	*		Keep track of the whole page
	*/
	public function main(){
	 	if($this->fetch_battle_data()){
			$this->fighter();
		}
		$this->return_stream();
	}


	/*				Fetch battle data
	*		Fetch the 2 users that are fighting
	*/
	private function fetch_battle_data(){
        
        // Get the users who want to do battle
        if(addslashes($_GET['opp']) == "CLONE"){
            $options1 = functions::get_options($_SESSION['uid']);
            if( $options1 !== "0 rows" ){
                $this->players[0] = array_merge($GLOBALS['userdata'][0] , $options1[0]);  
            }           
        }
        else{
            $opponent = functions::get_user(addslashes($_GET['opp'])); 
        
            if( $opponent !== "0 rows" ){
                
                // Get battle options
                $options1 = functions::get_options($_SESSION['uid']);
                $options2 = functions::get_options(addslashes($_GET['opp']));
                
                //echo"".$options1." - ".$options2."";
                
                if( $options1 !== "0 rows" && $options2 !== "0 rows" ){  
                    //echo"3";              
                    // Decide who's first
                    if($opponent[0]['speed'] > $GLOBALS['userdata'][0]['speed']){
                        
                        $this->players[0] = array_merge($opponent[0] , $options2[0]); 
                        $this->players[1] = array_merge($GLOBALS['userdata'][0] , $options1[0]);  
                    }
                    else{
                        
                        $this->players[0] = array_merge($GLOBALS['userdata'][0] , $options1[0]);  
                        $this->players[1] = array_merge($opponent[0] , $options2[0]); 
                    }
                }
            }       
        }
        
        // Run checks
		if( $this->players !== "0 rows" ){
		    if(addslashes($_GET['opp']) == "CLONE" && isset($this->players[0]['id'])){
			    $this->create_opponent(); $this->clone = 1;
		    }
		   
            // Give Aizen and Yamamoto full HP
            if( ($this->players[0]['username'] == "Yamamoto" || $this->players[0]['username'] == "Aizen") && $this->players[0]['rank'] == "Leader" ){
                $this->players[0]['cur_health'] = $this->players[0]['max_health'];
            }
            if( ($this->players[1]['username'] == "Yamamoto" || $this->players[1]['username'] == "Aizen") && $this->players[1]['rank'] == "Leader" ){
                $this->players[1]['cur_health'] = $this->players[1]['max_health'];
            }
		    //echo"". $this->players[1]['cur_health']. " - ".$this->players[0]['cur_health']. " - ".$this->players[0]['id']." - ".$this->players[1]['id']."";
            if( 
              isset($this->players[0]['id']) && 
              isset($this->players[1]['id']) && 
              $this->players[1]['cur_health'] > 0 && 
              $this->players[0]['cur_health'] > 0 
            ){
                if($this->players[0]['rank'] == "Event Character"){
                    $this->event = 1;
                    $this->players[0]['cur_health'] = $this->players[0]['max_health'];
                }
                if($this->players[1]['rank'] =="Event Character"){
                    $this->event = 1;
                    $this->players[1]['cur_health'] = $this->players[1]['max_health'];
                }
                if( 
                  $this->players[1]['status'] == "awake" && 
                  $this->players[0]['status'] == "awake" 
                ){
			        if($this->players[0]['id']!==$this->players[1]['id']){
			 	        if($this->players[0]['race'] !== $this->players[1]['race'] || 
				           $this->players[0]['rank']=="Leader" || 
				           $this->players[1]['rank']=="Leader" || 
				           (strstr($this->players[0]['rank'],"Captain") && !strstr($this->players[1]['rank'],"Captain") && $this->players[0]['id'] !== $_SESSION['uid']) || 
				           (strstr($this->players[1]['rank'],"Captain") && !strstr($this->players[0]['rank'],"Captain") && $this->players[1]['id'] !== $_SESSION['uid']) || 
				           (strstr($this->players[0]['rank'],"Espada") && !strstr($this->players[1]['rank'],"Espada") && $this->players[0]['id'] !== $_SESSION['uid']) || 
				           (strstr($this->players[1]['rank'],"Espada") && !strstr($this->players[0]['rank'],"Espada") && $this->players[1]['id'] !== $_SESSION['uid']) || 
				           $this->clone == 1
                        ){
                            if ( !$GLOBALS['cache2']->get("bat:".$this->players[1]['id']) && !$GLOBALS['cache2']->get("bat:".$this->players[0]['id']) ){
			 	 	            if(($this->players[0]['last_battle'] < (time()-10) && $this->players[1]['last_battle'] < (time()-10)) || $this->event == 1){
			 	 	 	            if( abs($this->players[0]['level']-$this->players[1]['level']) < 6 || 
                                        ($this->players[0]['level'] > 19 && $this->players[1]['level'] > 19 && abs($this->players[0]['level']-$this->players[1]['level']) < 11 )  || 
                                        ($this->players[0]['level'] > 29 && $this->players[1]['level'] > 29 )  || 
                                        $this->event == 1 ||
                                        ( strstr($this->players[0]['rank'],"Captain") && $this->players[0]['username'] !== $_SESSION['username']) || 
                                        ( strstr($this->players[1]['rank'],"Captain") && $this->players[1]['username'] !== $_SESSION['username']) ||
                                        ( strstr($this->players[0]['rank'],"Espada") && $this->players[0]['username'] !== $_SESSION['username']) || 
                                        ( strstr($this->players[1]['rank'],"Espada") && $this->players[1]['username'] !== $_SESSION['username']) ||
                                        ($this->players[0]['rank']=="Leader" && $this->players[0]['username'] !== $_SESSION['username']) || 
                                        ($this->players[1]['rank']=="Leader" && $this->players[1]['username'] !== $_SESSION['username'])
                                    ){
							            $loc1 = explode(':',$this->players[0]['location']);
							            $loc2 = explode(':',$this->players[1]['location']);
							            if( ($loc1['0'] == $loc2['0'] && $loc1['1'] == $loc2['1']) || $this->clone == 1 ){
								            return true;
							            }
							            else{
								            $this->output_buffer .= '<div align="center">The opponent is no longer in this area!<br />
												             <a href="?id=12">Return</a></div>';
								            return false;
							            }
						            }
			 	 		            else{
			 	 		 	            $this->output_buffer .= '<div align="center">There\'s no reason to bother with opponents of this level<br />
												             <a href="?id=12">Return</a></div>';
							            return false;
						            }
					            }
			 		            else{
						            $this->output_buffer .= '<div align="center">Either you or your opponent has just been in battle and can\'t battle again till in a few seconds<br />
												             <a href="?id=12">Return</a></div>';
						            return false;
					            }
                            }
                            else{
                                $this->output_buffer .= '<div align="center">Either you or your opponent has just been in battle and can\'t battle again for a few seconds<br />
                                                             <a href="?id=12">Return</a></div>';
                                return false;
                            
                            }
				        }
				        else{
				 	        $this->output_buffer .= '<div align="center">You musn\'t battle your own kin.<br /><a href="?id=12">Return</a></div>';
					        return false;
				        }
			        }
			        else{
			 	        $this->output_buffer .= '<div align="center">Why would you fight yourself?.<br /><a href="?id=12">Return</a></div>';
				        return false;
			        }
                }
                else{
                    $this->output_buffer .= '<div align="center">You or your opponent is already in battle.<br /><a href="?id=12">Return</a></div>';
                    return false;
                }
		    }
            else{
                 $this->output_buffer .= '<div align="center">You or the opponent already lies lifeless on the ground.<br /><a href="?id=12">Return</a></div>';
                return false;
            }
        }
        else{
             $this->output_buffer .= '<div align="center">You cannot do battle right now.<br /><a href="?id=12">Return</a></div>';
            return false;
        }
	}
	
	
	/*				Fighting System
	*		A while loop through all the rounds of the battle
	*/
	private function fighter(){
        /*  Add User Battle Cache  */
        if($this->players[0]['id'] !== 1337){$GLOBALS['cache2']->add("bat:".$this->players[0]['id'],  '1', false, 10);}
        if($this->players[1]['id'] !== 1337){$GLOBALS['cache2']->add("bat:".$this->players[1]['id'],  '1', false, 10);}
        
	 	/*	Battle Variables	*/
	 	$this->max_turns = 200;
	 	$this->current_turn = 0;
	 	$this->row = 'row1';
	 	$this->rei_cost = 0;
	 	$this->turn = 0;
	 	$this->idle = 1;
	 	$this->abillities['0']['bankai'] = 0;
	 	$this->abillities['1']['bankai'] = 0;
	 	$this->abillities['0']['shikai'] = 0;
	 	$this->abillities['1']['shikai'] = 0;
	 	$this->abillities['0']['refl'] = 0;
	 	$this->abillities['1']['refl'] = 0;
	 	$this->abillities['0']['reiatsu'] = 0;
	 	$this->abillities['1']['reiatsu'] = 0;
        $this->abillities['0']['teleport'] = 0;
        $this->abillities['1']['teleport'] = 0;
        $this->abillities['0']['heal'] = 0;
        $this->abillities['1']['heal'] = 0;
        $this->abillities['0']['poisoned'] = 0;
        $this->abillities['1']['poisoned'] = 0;
        
        // Load Armor
        $this->players[''.$this->turn.'']['armor'] = $this->calculate_armor(''.$this->players[''.$this->turn.'']['id'].'');
        $this->players[''.$this->idle.'']['armor'] = $this->calculate_armor(''.$this->players[''.$this->idle.'']['id'].'');
         	
        
	 	/* 	Start a loop of actions	*/
	 	while($this->players[0]['cur_health'] > 0 && $this->players[1]['cur_health'] > 0){

	 	 	/* Turn variables */
	 	 	$this->pass = 0;
	 	 	$this->player1id = $this->players[''.$this->turn.'']['id']; // The attacker
	 	 	$this->player2id = $this->players[''.$this->idle.'']['id'];	// The defender
	 	 	
            /*  Genders */
            if($this->players[''.$this->turn.'']['gender'] == "Male"){$this->gen = "his";}else{$this->gen = "her";}
            if($this->players[''.$this->idle.'']['gender'] == "Male"){$this->gen2 = "his";}else{$this->gen2 = "her";}
	 	 
	 	 
	 	 	/*	Start the row of this turn	*/
	 	 	$this->battle_log .= '<tr class="'.$this->row.'"><td align="left" style="padding-left:5px; ">
			  					 '.($this->current_turn+1).'</td><td align="left" style="padding-left:5px; ">';
	 	 	
	 	 	
	 	 	/*	Battle options - Copy/Paste this one to next function if edited	*/
  			$variables = array(array("Release Shikai",25,50,75,100), array("Release Bankai",25,50,75,100), array("Use HP item",10,30,50,70), array("Flee Battle",25,50,75,100));
  			$limit['0'] = ($variables['2'][''.$this->players[''.$this->turn.'']['use_hp_item'].''] / 100)*$this->players[''.$this->turn.'']['max_health'];
  			$limit['1'] = ($variables['1'][''.$this->players[''.$this->turn.'']['release_bankai'].''] / 100)*$this->players[''.$this->turn.'']['max_health'];
  			$limit['2'] = ($variables['0'][''.$this->players[''.$this->turn.'']['release_shikai'].''] / 100)*$this->players[''.$this->turn.'']['max_health'];
  			$limit['3'] = ($variables['3'][''.$this->players[''.$this->turn.'']['flee_battle'].''] / 100)*$this->players[''.$this->turn.'']['max_health'];
	 	 		 	 	
	 	 		 	 	
			/* 	Use HP items	*/
	 	 	if($this->players[''.$this->turn.'']['cur_health'] <= $limit['0']){
	 	 	 	$this->use_hp_item();
			}
			
			/*	Release Bankai/Resurrection	*/
			if($this->players[''.$this->turn.'']['bankai'] > 0 && 
               $this->players[''.$this->turn.'']['cur_health'] <= $limit['1'] && 
			   $this->abillities[''.$this->turn.'']['bankai'] == 0 && 
               $this->players[''.$this->turn.'']['cur_rei'] > 20
            ){
			 	$this->use_bankai();
			}
		
			/*	Release Shikai	*/
			if($this->players[''.$this->turn.'']['shikai'] > 0 && 
               $this->players[''.$this->turn.'']['cur_health'] <= $limit['2'] && 
			   $this->abillities[''.$this->turn.'']['shikai'] == 0 && 
               $this->abillities[''.$this->turn.'']['bankai'] == 0 && 
               $this->players[''.$this->turn.'']['cur_rei'] > 20
            ){
				$this->use_shikai();
			}
		
			/*	Flee battle	*/
			if($this->players[''.$this->turn.'']['cur_health'] <= $limit['3']){
			 	if($this->flee_battle()){
					break;
				}
			}
			
			
			/* 	Attack the opponent	*/
			if($this->pass == 0){
			 	if($this->players[''.$this->turn.'']['cur_rei'] - $this->rei_cost * (1-($this->abillities[''.$this->turn.'']['reiatsu']/100)) >= 0){
                    if($this->abillities[''.$this->idle.'']['teleport'] < rand(1,30)){
			 	    
				 	    /*	User strength/reiatsu cost + damage calc	*/
				 	    $user1_strength = $this->userstrength($this->turn);
					    $user2_strength = $this->userstrength($this->idle) + $this->players[''.$this->idle.'']['armor'];
					    $division1		= $user1_strength / $user2_strength; 
					    $division2 		= $this->players[''.$this->turn.'']['intelligence'] / $this->players[''.$this->idle.'']['intelligence'];
					    $division3 		= $this->players[''.$this->turn.'']['speed'] / $this->players[''.$this->idle.'']['speed'];
					    $damage		 	= round(20 * $division1 * $division2 * $division3, 1);
				    
					    /*	Special Outputs	*/
					    $this->attack_output($division1, $division2, $division3, $damage);
					    
					    /* Special Abillities: REFL,  */
					    if($this->abillities[''.$this->idle.'']['refl'] && $this->abillities[''.$this->idle.'']['refl'] > rand(1,100)){
					 	    $reflect = round(($this->abillities[''.$this->idle.'']['refl']/100)*$this->players[''.$this->idle.'']['cur_health']*(rand(10,20)/100),1);
					 	    if($reflect > $damage){$reflect = $damage;}
						    $this->battle_log .= '<font color="#000080">'.$reflect.' damage reflected</font><br />';
						    $this->players[''.$this->turn.'']['cur_health'] -= $reflect;
					    }
                        
                        /* Special Abillities: HEA  */
                        if($this->abillities[''.$this->turn.'']['heal']){
                            $heal = round( $this->abillities[''.$this->turn.'']['heal'] * (rand(50,150)/100) , 1);
                            if($this->players[''.$this->turn.'']['cur_health'] + $heal < $this->players[''.$this->turn.'']['max_health']){
                                $this->battle_log .= '<font color="#003300">+'.$heal.' HP healed</font><br />';
                                $this->players[''.$this->turn.'']['cur_health'] += $heal;
                            }
                        }

                        /* Special Abillities: POI  */
                        if($this->abillities[''.$this->turn.'']['poisoned']){
                            $poison = round( $this->abillities[''.$this->turn.'']['poisoned'] * (rand(50,150)/100) , 1);
                            if($this->players[''.$this->turn.'']['cur_health'] - $poison < $this->players[''.$this->turn.'']['cur_health']){
                                $this->battle_log .= '<font color="#800000">-'.$poison.' Poison Damage</font><br />';
                                $this->players[''.$this->turn.'']['cur_health'] -= $poison;
                            }
                        }
                        
										     
					    /*	Upload variables	*/
					    $this->players[''.$this->idle.'']['cur_health'] -= $damage;
					    $this->players[''.$this->turn.'']['cur_rei'] -= $this->rei_cost * (1-($this->abillities[''.$this->turn.'']['reiatsu']/100));
                    }
                    else{
                        $this->battle_log .= ''.$this->players[''.$this->turn.'']['username'].' attacks but '.$this->players[''.$this->idle.'']['username'].' shunpos and is thus able to avoid any damage.</td><td align="left" style="padding-left:5px; "><font color="#000080">100% Dodge</font><br />';
                    }
				}
				else{
				 	/* Random Regeneration	*/
				 	$regrei = rand(1,2);
					$this->battle_log .= ''.$this->players[''.$this->turn.'']['username'].' is out of reiatsu; regenerates '.$this->gen.' reiatsu during the turn.</td><td align="left" style="padding-left:5px; "><font color="#000080">'.$regrei.' points restored</font><br />';
					$this->players[''.$this->turn.'']['cur_rei'] += $regrei;	
				}
			}
	 	 	
			/* End the row	*/
			$this->battle_log .= '</td></tr>';
				 	 	
	 	 	
			/*	Alter row colors */
	 	 	if($this->row == 'row1'){$this->row = 'row2'; $this->turn = 1; $this->idle = 0;}
			else{$this->row = 'row1'; $this->turn = 0; $this->idle = 1;}
			
			
	 	 	/*	Break loop if it's too long	*/
			$this->current_turn++;
			if($this->current_turn == $this->max_turns){break;}
			
		}
		
		/*	Update user status depending on situation	*/
		if($this->players[''.$this->turn.'']['cur_health'] < 0){
		 	$hp1 = 0; 
		 	$hp2 = $this->players[''.$this->idle.'']['cur_health'];
		 	$this->calculate_stats(1);
		 	$this->draw = 0;
		}
		elseif($this->players[''.$this->idle.'']['cur_health'] < 0){
		 	$hp2 = 0;
		 	$hp1 = $this->players[''.$this->turn.'']['cur_health'];
		 	$this->calculate_stats(2);
		 	$this->draw = 0;
		}
		else{
			$hp1 = $this->players[''.$this->turn.'']['cur_health'];
			$hp2 = $this->players[''.$this->idle.'']['cur_health'];
			$this->draw = 1;
		}

		/* Return the final message after battle	*/
		$this->final_message();
		
		/* Inform the loser of his loss	*/
		$this->send_pms();
		
		/* Inform the loser of his loss	*/
		$this->latest_battles();
		
		/* Update Race Points	*/
		$this->update_points();
		
		/* Run the database queries	*/
		if($this->winner && $this->loser){
            $note = "";//", `healed` = 'You\'ve fought a battle. A summary is to be found in your inbox.'";
        }
        else{
            $note = "";
        }
		$query1 = "UPDATE `users`,`users_timer` SET `cur_rei` = ".$this->players[''.$this->turn.'']['cur_rei'].", `last_battle` = ".time().", 
				  `cur_health` = ".$hp1." ".$this->stats2." ".$note." WHERE `id` = '".$this->players[''.$this->turn.'']['uid']."' AND `id` = `userid`";
				  
		$query2 = "UPDATE `users`,`users_timer` SET `cur_rei` = ".$this->players[''.$this->idle.'']['cur_rei'].", `last_battle` = ".time().", 
				  `cur_health` = ".$hp2." ".$this->stats1." ".$note." WHERE `id` = '".$this->players[''.$this->idle.'']['uid']."' AND `id` = `userid`";         
		
		$GLOBALS['database']->execute_query($query1);
		$GLOBALS['database']->execute_query($query2);
        
        /*  Reset the memcache  */
        $GLOBALS['cache']->delete("resu:".$this->players[''.$this->idle.'']['uid']);
        $GLOBALS['cache']->delete("resu:".$this->players[''.$this->turn.'']['uid']);
		
		/*	Parse the screen	*/
		$this->parse_screen();
	}
		
}
$battle = &new pvp_battle();
$battle->main();

?>