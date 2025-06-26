<?php
/*				Profile.inc.php
*			Prints profiles to screen
*/
class profile{
	var $output_buffer;
	var $char_data;
	
	function profile(){
		$this->char_data = $GLOBALS['userdata'];
        
		if(!isset($_GET['act'])){
			$this->main_profile();
		}
		elseif($_GET['act'] == 'do_exam'){

				if($this->char_data[0]['status'] == 'awake'){
					if($this->char_data[0]['level'] == 9){
						$this->shikai_exam();
					}
					elseif($this->char_data[0]['level'] == 19){
						$this->bankai_exam();
					}
					else{
						$this->output_buffer .= '<div align="center">You cannot rank up at this point.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
					}
				}
				else{
					$this->output_buffer .= '<div align="center">You must be awake to rank up.<a href="?id='.$_GET['id'].'">Return</a></div>';
				}
		}
		elseif($_GET['act'] == 'shards'){
			$this->exchange_shards();
		}
		elseif($_GET['act'] == 'gathershards'){
			$this->gather_shards();
		}
		elseif($_GET['act'] == 'exchange'){
			$this->doexchange_shards();
		}
		elseif($_GET['act'] == 'referal'){
			$this->refer();
		}
		elseif($_GET['act'] == 'abillity'){
			$this->abillity();
		}
		$this->return_stream();
	}
	
	function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
		
	/*	Generate a random type	*/
	function typegen(){
		$types = rand(1,301);
		if($types < 101){$types = "S";}
		elseif($types > 100 && $types < 201){$types = "I";}
		elseif($types > 200 && $types < 301){$types = "D";}
		elseif($types == 301){$types = "SID";}
		return $types;
	}
		
	/*	Generate a tag	*/
	function tagger(){
		$rand = rand(1,9);
		while(ereg($rand,$this->choices)){
			$rand = rand(1,9);
		}
		$int = rand(5,10);
		$types = $this->typegen();
		switch($rand){
			case 1: $tag = "DINC:$types:PERC:$int"; $this->choices .= "1"; break;
			case 2: $tag = "DSDEC:$types:PERC:".rand(4,6).""; $this->choices .= "2"; break;
			case 3: $tag = "CHAD:$types:PERC:$int"; $this->choices .= "3"; break;
			case 4: $tag = "REFL:$types:PERC:$int"; $this->choices .= "4"; break;
			case 5: $tag = "DSTA:$types:PERC:$int"; $this->choices .= "5"; break;
            case 6: $tag = "REG:NONE:PERC:".($int / 2).""; $this->choices .= "6"; break;
            case 7: $tag = "TEL:NONE:PERC:".(round($int / 5,1)).""; $this->choices .= "7"; break;
            case 8: $tag = "HEA:NONE:PERC:".(round($int / 5,1)).""; $this->choices .= "8"; break;
            case 9: $tag = "POI:NONE:PERC:".(round($int / 5,1)).""; $this->choices .= "9"; break;
		}
		return $tag;
	}
	
	//Decipher Description
	function decipher($tag){
		$tag = explode(':',$tag);
		switch($tag[1]){
			case "S": $tag['1'] = "strength"; break;
			case "I": $tag['1'] = "intelligence"; break;
			case "D": $tag['1'] = "speed"; break;
			case "SID": $tag['1'] = "strength, speed and intelligence"; break;
		}
		switch($tag[0]){
			case "DINC": $abillities .= "Damage based on $tag[1] increased by $tag[3]%"; break;
			case "DSDEC": $abillities .= "Damage sustained by opponents $tag[1] decreased by $tag[3]%"; break;
			case "CHAD": $abillities .= "Reiatsu used decreased by $tag[3]%"; break;
			case "REFL": $abillities .= "A $tag[3]% chance to reflect damage during battle"; break;
			case "DSTA": $abillities .= "The abillity to decrease the opponents $tag[1] by $tag[3]%"; break;
            case "REG": $abillities .= "Increased regeneration rate by $tag[3]%"; break;
            case "TEL": $abillities .= "The ability to shunpo a distance of $tag[3] m"; break;
            case "HEA":
                $abillities .= "The ability to rapidly heal around $tag[3] HP during each turn in battle";
                break;
            case "POI":
                $abillities .= "Poison opponents for around $tag[3] HP during each turn in battle";
                break;
		}
		return $abillities;
	}
	
	/* Generate a new abillity	*/
	function create_technique(){
	 			#############################################
	 			##	Start of Technique Creation, v. 0.90   ##
	 			#############################################
				$tag1 = $this->tagger();
				$tag2 = $this->tagger();
				$tag3 = $this->tagger();
				$tags = "$tag1|$tag2|$tag3";
				
				$abillities = '<i>'.$this->decipher($tag1).'<br />'.$this->decipher($tag2).'<br />'.$this->decipher($tag3).'</i>';
	 			
	 			#############################################
	 			##	End of Technique Creation, v. 0.90     ##
	 			#############################################
	 			$level_data = $GLOBALS['database']->fetch_data("SELECT * FROM `levels` WHERE `levelID` = '".$this->char_data[0]['level']."' LIMIT 1");
			    $regen_increase = $this->char_data[0]['level'] + 1;
			    $query = "UPDATE `users` SET `regen_rate` = `regen_rate` + '".$regen_increase."', `level` = `level` + 1, `max_health` = `max_health` + '".$level_data[0]['health_gain']."', `max_rei` = `max_rei` + '".$level_data[0]['reiatsu_gain']."', `cur_rei` = `max_rei` , `cur_health` = `max_health`, `abillity` = '".$tags."', `shikai` = 1  WHERE `id` = '".$_SESSION['uid']."'";
                $GLOBALS['cache']->delete("resu:".$_SESSION['uid']);
			    $GLOBALS['database']->execute_query($query);
			    if($this->char_data[0]['level'] == 9){
				    $result = 'Your newly acquired skill has following attributes:<br /><br />'.$abillities.'';
			    }
			    return ''.$result.'';
	}
	
	
	function upgrade_technique(){	 
	 	//Decipher tag and upgrade with random multiplier
		$tag = explode('|',$this->char_data[0]['abillity']);
		$rand1 = rand(5,7); $tag1 = explode(':',$tag[0]); $tag1[3] = $tag1[3]*$rand1;
		$rand2 = rand(5,7); $tag2 = explode(':',$tag[1]); $tag2[3] = $tag2[3]*$rand2;
		$rand3 = rand(5,7); $tag3 = explode(':',$tag[2]); $tag3[3] = $tag3[3]*$rand3;
		$newtag = ''.$tag1[0].':'.$tag1[1].':'.$tag1[2].':'.$tag1[3].'|'.$tag2[0].':'.$tag2[1].':'.$tag2[2].':'.$tag2[3].'|'.$tag3[0].':'.$tag3[1].':'.$tag3[2].':'.$tag3[3].'';
		//Convert to informative string
		function decipher($tag, $rand){
			$tag = explode(':',$tag);
			switch($tag[0]){
				case "DINC": $abillities .= "Abillity to deal increased damage has become $rand times better"; break;
				case "DSDEC": $abillities .= "Abillity to sustain less damage has become $rand times better"; break;
				case "CHAD": $abillities .= "Abillity to spend less reiatsu has become $rand times better"; break;
				case "REFL": $abillities .= "Abillity to reflect damage has become $rand times better"; break;
				case "DSTA": $abillities .= "Abillity to decrease opponents stats has become $rand times better"; break;
                case "REG": $abillities .= "Your increased healing powers have become $rand times better"; break;
                case "TEL": $abillities .= "Your ability to shunpo has become $rand times better"; break;
                case "HEA": $abillities .= "Your ability to heal has become $rand times better"; break;
                case "POI": $abillities .= "Your poison ability has become $rand times better"; break;
			}
			return $abillities;
		}
		$abillities = '<i>'.decipher($tag[0], $rand1).'<br />'.decipher($tag[1], $rand2).'<br />'.decipher($tag[2], $rand3).'</i>';

		$level_data = $GLOBALS['database']->fetch_data("SELECT * FROM `levels` WHERE `levelID` = '".$this->char_data[0]['level']."' LIMIT 1");
		$regen_increase = $this->char_data[0]['level'] + 1;
		$query = "UPDATE `users` SET `regen_rate` = `regen_rate` + '".$regen_increase."', `level` = `level` + 1, 
									 `max_health` = `max_health` + '".$level_data[0]['health_gain']."', 
									 `max_rei` = `max_rei` + '".$level_data[0]['reiatsu_gain']."', `cur_rei` = `max_rei` , 
									 `cur_health` = `max_health`, `abillity` = '".$newtag."', 
									 `bankai` = 1  WHERE `id` = '".$_SESSION['uid']."'";
        // Delete Memcache record
        $GLOBALS['cache']->delete("resu:".$_SESSION['uid']);
		$GLOBALS['database']->execute_query($query);
		if($this->char_data[0]['level'] == 19){
			$result = 'Your newly acquired skill has following attributes:<br /><br />'.$abillities.'';
		}
		return ''.$result.'';
	}
		
	function shikai_exam(){
	 		//Race Dependent Variables:
	 		if($this->char_data[0]['rankid'] == 1){
				$title = "Learning Shikai";
				$text = "In order to release your shikai you must be able to communicate with your zanpaktou effectively, which takes skills!";
				$denied = "<b>You are not able to obtain the name of your Zanpaktou yet. You need to train your generel skills to a total of 100!</b>"; 
				$req = 100;
				$accept = "With your amount of skill you are able to fully communicate with your Zanpaktou, and you learn its name.<br />";
				$skill = $this->char_data[0]['intelligence'] + $this->char_data[0]['speed'] + $this->char_data[0]['strength'];
			}else{
				$title = "Turning Adjuchas";
				$text = "In order to rip off your mask and gain the powers of the Adjuchas you need to devour 50 humans"; $req = 50;
				$denied = "<b>You have not devoured enough humans for this transformation</b>";
				$accept = "In a explosive burst you rip off your mask and transform into a adjuchas.<br />";
				$skill = $this->char_data[0]['sword'];
			}
			$this->output_buffer = '<div align="center">
  			<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
  			<tr><td colspan="2" align="center" style="border-top:none;" class="subHeader">'.$title.'</td></tr>
    		<tr><td align="center" style="padding:5px;">'.$text.'</td></tr>
    		<tr><td align="center">&nbsp;</td></tr>';
    		$level_data = $GLOBALS['database']->fetch_data("SELECT * FROM `levels` WHERE `levelID` = '".$this->char_data[0]['level']."' LIMIT 1");
    		if($level_data[0]['experience_required'] <= $this->char_data[0]['experience']){
				if($skill >= $req){
					$test = $accept; $passed = true;
					$test .= $this->create_technique();
				}else{
					$test = $denied; $passed = false;
				}
			}   
		    else{   
		        $this->output_buffer .= '<div align="center">You do not have enough experience<br /> <a href="?id='.$_GET['id'].'">Return</a></div>';
	        }
			$this->output_buffer .= '<tr><td align="center" style="padding:5px;">'.$test.'</td></tr>';

    		$this->output_buffer .= '<tr>
      		<td align="center"><a href="?id='.$_GET['id'].'">Click here to return to the profile</a></td>
    		</tr><tr><td width="100%" align="center">&nbsp;</td></tr></table><br /><br /></div>';
	}
	
	function bankai_exam(){
	 		//Race Dependent Variables:
	 		if($this->char_data[0]['rankid'] == 1){
				$title = "Learning Bankai";
				$text = "In order to release your bankai an enourmous amount of skill is required! Train your shikai to level 150";
				$denied = "<b>You are not able to obtain the full power of your Zanpaktou! You need to train more!</b>";
				$accept = "With your amount of skill you are able to fully release your Zanpaktou, and hence release your bankai.<br />";
				$skill = $this->char_data[0]['shikai'];
			}else{
				$title = "Turning into a Vasto Lord";
				$text = "In order to return to your human form and seal your powers inside a zanpaktou, you need to devour 150 hollows!";
				$denied = "<b>You have not devoured enough hollows for this transformation</b>";
				$accept = "Your hollow abillities are reduced to the form of a zanpaktou in a flash and you return to your long forgotten human form.<br />";
				$skill = $this->char_data[0]['shikai'];
			}
			$this->output_buffer = '<div align="center">
  			<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
  			<tr><td colspan="2" align="center" style="border-top:none;" class="subHeader">'.$title.'</td></tr>
    		<tr><td align="center" style="padding:5px;">'.$text.'</td></tr>
    		<tr><td align="center">&nbsp;</td></tr>';
    		$level_data = $GLOBALS['database']->fetch_data("SELECT * FROM `levels` WHERE `levelID` = '".$this->char_data[0]['level']."' LIMIT 1");
    		if($level_data[0]['experience_required'] <= $this->char_data[0]['experience']){
				if($skill >= 150){
					$test = $accept; $passed = true;
					$test .= $this->upgrade_technique();
				}else{
					$test = $denied; $passed = false;
				}
			}   
		    else{   
		        $this->output_buffer .= '<div align="center">You do not have enough experience<br /> <a href="?id='.$_GET['id'].'">Return</a></div>';
	        }
			$this->output_buffer .= '<tr><td align="center" style="padding:5px;">'.$test.'</td></tr>';

    		$this->output_buffer .= '<tr>
      		<td align="center"><a href="?id='.$_GET['id'].'">Click here to return to the profile</a></td>
    		</tr><tr><td width="100%" align="center">&nbsp;</td></tr></table><br /><br /></div>';
	}
	
	/*				Levelup functions				*/
	function claim_level(){
        if($this->char_data[0]['status'] == 'awake'){
		    $level_data = $GLOBALS['database']->fetch_data("SELECT * FROM `levels` WHERE `levelID` = '".$this->char_data[0]['level']."' LIMIT 1");
		    if($level_data[0]['experience_required'] <= $this->char_data[0]['experience']){
			    if($char_data[0]['level'] != 9 && $char_data[0]['level'] != 19){
			     	/*		Referal System - Start		*/
			     	If($this->char_data[0]['level'] == 1){
						$refdata = $GLOBALS['database']->fetch_data("SELECT * FROM `referals` WHERE `uid` = '".$this->char_data[0]['id']."' LIMIT 1");
						if($refdata != '0 rows'){
							$query = "UPDATE `users` SET `referals` = `referals` + 1, 
														 `strength` = `strength` + 4, 
														 `intelligence` = `intelligence` + 4, 
														 `speed` = `speed` + 4 
									  WHERE `username` = '".$refdata[0]['referrer']."' AND `referals` < 10";
                            $GLOBALS['database']->execute_query($query);
                            
                            // Delete Memcache record
                            $GLOBALS['cache']->delete("resu:".$_SESSION['uid']);
						}
					}
			     	/*		Referal System - End		*/
					if(strstr($this->char_data[0]['rank'],"Captain") || strstr($this->char_data[0]['rank'],"Espada") || $this->char_data[0]['rank'] == "Leader"){
						$newrank = $this->char_data[0]['rank'];
					}
					else{
						$newrank = explode(':', $level_data[0]['rank']);
						$newrank = $newrank[''.($this->char_data[0]['rankid']-1).''];
					}
				    $query = "UPDATE `users` SET `level` = `level` + 1, 
												 `max_health` = `max_health` + '".$level_data[0]['health_gain']."', 
												 `max_rei` = `max_rei` + '".$level_data[0]['reiatsu_gain']."', 
												 `cur_rei` = `max_rei`, 
												 `cur_health` = `max_health`,
												 `rank` = '".$newrank."'  
							 WHERE `id` = '".$_SESSION['uid']."'";
				    $GLOBALS['database']->execute_query($query);
                    
                    // Delete Memcache record
                    $GLOBALS['cache']->delete("resu:".$_SESSION['uid']);

				    return '<div align="center"><table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
    			    <tr><td align="center" style="font-weight:bolder;padding:2px;"><font size="+2">You\'ve been promoted to level '.($this->char_data[0]['level'] + 1).' '.$newrank.'!</font> </td>
    			    </tr><tr><td align="center">This has gained you <b>'.$level_data[0]['health_gain'].'</b> Health Points and <b>'.$level_data[0]['reiatsu_gain'].'</b> Reiatsu Points<br /></td>
    			    </tr></table></div>';
			    }
			    else{
				    $this->output_buffer .= '<div align="center">You are not ready to level up yet <br /> <a href="?id='.$_GET['id'].'">Return</a></div>';
			    }
		    }   
		    else{   
		        $this->output_buffer .= '<div align="center">You do not have enough experience to claim your next level <br /> <a href="?id='.$_GET['id'].'">Return</a></div>';
	        }
        }
        else{
            $this->output_buffer .= '<div align="center">You must be awake / out of battle to claim your level<br /> <a href="?id='.$_GET['id'].'">Return</a></div>';
        }
	}
	
	function check_levelup($level_data){
		if($level_data[0]['experience_required'] <= $this->char_data[0]['experience'] && $this->char_data[0]['status'] == 'awake'){
			if($this->char_data[0]['level'] == 9){
				if($this->char_data[0]['rankid'] == 1){
				 	return '<div align="center"><a href="?id='.$_GET['id'].'&act=do_exam"><font size="+1"><b>Learn the name of your Zanpaktou</b></font></a></div>';
				}
				else{
				 	return '<div align="center"><a href="?id='.$_GET['id'].'&act=do_exam"><font size="+1"><b>Take off your mask</b></font></a></div>';
				}
			}elseif($this->char_data[0]['level'] == 19){
			 	if($this->char_data[0]['rankid'] == 1){
				  	return '<div align="center"><a href="?id='.$_GET['id'].'&act=do_exam"><font size="+1"><b>Try to release your bankai</b></font></a></div>';
				}
				else{
				 	return '<div align="center"><a href="?id='.$_GET['id'].'&act=do_exam"><font size="+1"><b>Transform to Vasto Lord</b></font></a></div>';
				}
			}else{
				return '<div align="center" style="font-size:24px;font-weight:bold;">'.$this->claim_level().'</div>';
			}
		}
	}
	
	function exchange_shards(){
		$this->output_buffer = '<div align="center">
	 	<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
	 	<tr>
        <td colspan="3" align="center" style="border-top:none;" class="subHeader">Absorb Shards</td>
        </tr>
        <tr>
        <td colspan="3" align="center" style="padding:2px;">You can choose to absorb the spirit shards you\ve gathered into your own body, thereby increasing the strength of your own spirit. You can also absorb the shards in order to try to change your special ability.</td>
        </tr>
        <tr>
        	<td colspan="3" align="center" style="border-top:none;"> &nbsp;</td>
        </tr>
		<tr>
            <td align="center" ><b><a href="?id=3&act=exchange&option=1">Absorb 5 spirit shard</a></b>  <br /><i><font size="-1">+0.25 regeneration rate</font></i></td>
              <td align="center" ><b><a href="?id=3&act=exchange&option=2">Absorb 25 spirit shards</a></b><br /><i><font size="-1">+2 regeneration rate</font></i></td>
            <td align="center" ><b><a href="?id=3&act=exchange&option=3">Absorb 50 spirit shards</a></b><br /><i><font size="-1">+5 regeneration rate</font></i> </td>
          </tr>
        <tr>
            <td colspan="3" align="center" style="border-top:none;"> &nbsp;</td>
        </tr>
        <tr>
            <td align="center" > &nbsp; </td>
              <td align="center" ><b><a href="?id=3&act=exchange&option=4">Absorb 50 spirit shards</a></b><br /><i><font size="-1">Re-roll special ability</font></i></td>
            <td align="center" > &nbsp; </td>
          </tr>
        <tr>
            <td colspan="3" align="center" style="border-top:none;"> &nbsp;</td>
        </tr>
		</table>
    	<br />
    	<a href="?id='.$_GET['id'].'">Return</a><br />
  		<br />
		</div>';

	}
	
	function gather_shards(){
		$this->output_buffer = '<div align="center"><br />
    <table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
      <tr>
        <td colspan="3" align="left" style="border-top:none;" class="subHeader">
        <p style="margin-left: 5"><b>Earn spirit shards by doing surveys!</b></td>
      </tr>
      <tr>
        <td colspan="3" align="left"><p style="margin-left: 5"><b>Bleach-RPG Notice:</b> Below service is provided by blvd-media.com. Most offers <b>do</b> pay out spirit shards provided you\'re providing valid information etc. Though many pay out instantly, some take up to hours or even a few days to pay out. If no offers pay out, please don\'t bug us but go directly to the source; blvd-media.com.<br /><br />
		<b>Good advice:</b> In most cases it\'s a good idea to make sure the person filling out the form is at least 18 years old. If you\'re not, have your parents fill out the forms or ask them if you can use their personal information to fill it out for them.<br><br />
		<b>WARNING: </b> It is highly un-recommended to pay money to any of below services in order to receive spirit shards! This game is a low maintenence project, and as such any loss of spirit shards due to an eventual mass reset or glitches, <b>will not be refunded!</b><br />-------------------------------------------<br />
      
	    <div align="center">
  <center>
  <table border="0" cellspacing="1" style="border-collapse: collapse" bordercolor="#111111" width="80%" id="AutoNumber1">
    <tr>
      <td width="95%">
	  <center>
	  <p style="margin-left: 5">
      <iframe frameborder="0" width="460" height="310" scrolling="no" src="./blvdX/RewardTool.html?pubid=285&subid='.$this->char_data[0]['id'].'"></iframe>
      <br /><br />
	  <br /><br />
	  </center>
	  </td>
    </tr>
  </table>
  </center>
</div>

		</td>
      </tr>

    	</table><br /><br /></div>';

	}
	
	function refer(){
		$this->output_buffer = '<div align="center">
	 	<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
	 	<tr>
        <td colspan="1" align="center" style="border-top:none;" class="subHeader">Refer New Members</td>
        </tr>
        <tr>
        <td colspan="1" align="center" style="padding:2px;">By using the following link, you can refer new users to bleach-rpg.net. <br />For each user you refer, you will gain 4 points of skill in both strength, speed and intelligence. <br />Points will not be awarded till the user reaches level 2!</td>
        </tr>
        <tr>
        	<td colspan="1" align="center" style="border-top:none;"> &nbsp;</td>
        </tr>
		<tr>
    		<td align="center" style="font-weight:bold;">http://www.bleach-rpg.net/?ref='.$this->char_data[0]['username'].'</td>
      	</tr>
        <tr>
        	<td colspan="3" align="center" style="border-top:none;"> &nbsp;</td>
        </tr>
		</table>
    	<br />
    	<a href="?id='.$_GET['id'].'">Return</a><br />
  		<br />
		</div>';

	}
	
	function doexchange_shards(){
	 	switch ($_GET['option']){
			case 1: $price = 5; $regen = 0.25; break;
			case 2: $price = 25; $regen = 2.0; break;
			case 3: $price = 50; $regen = 5.0; break;
            case 4: $price = 50; $regen = "reroll"; break;
			default: $price = "NO"; break;
		}

	 	if($this->char_data[0]['rep_now'] >= $price && $price !== "NO"){
	 	 	if($regen !== "reroll"){
                $query = "UPDATE `users` SET `regen_rate` = `regen_rate` + ".$regen.", `rep_now` = `rep_now` - ".$price." WHERE `id` = '".$_SESSION['uid']."'";
                $GLOBALS['database']->execute_query($query);
                
                // Delete Memcache record
                $GLOBALS['cache']->delete("resu:".$_SESSION['uid']);
                $this->output_buffer .= '<div align="center">You have absorbed the spirit shards.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
            }
            else{
                if($this->char_data[0]['level'] > 9){
                    #############################################
                    ##  Start of Technique Creation, v. 0.90   ##
                    #############################################
                    $tag1 = $this->tagger();
                    $tag2 = $this->tagger();
                    $tag3 = $this->tagger();
                    $newtag = "$tag1|$tag2|$tag3";
                    
                    if($this->char_data[0]['level'] > 19){
                        // Upgrade
                        $tag = explode('|',$newtag);
                        $rand1 = rand(5,7); $tag1 = explode(':',$tag[0]); $tag1[3] = $tag1[3]*$rand1;
                        $rand2 = rand(5,7); $tag2 = explode(':',$tag[1]); $tag2[3] = $tag2[3]*$rand2;
                        $rand3 = rand(5,7); $tag3 = explode(':',$tag[2]); $tag3[3] = $tag3[3]*$rand3;
                        $newtag = ''.$tag1[0].':'.$tag1[1].':'.$tag1[2].':'.$tag1[3].'|'.$tag2[0].':'.$tag2[1].':'.$tag2[2].':'.$tag2[3].'|'.$tag3[0].':'.$tag3[1].':'.$tag3[2].':'.$tag3[3].'';
                    }
                    $GLOBALS['database']->execute_query("UPDATE `users` SET `abillity` = '".$newtag."', `rep_now` = `rep_now` - ".$price." WHERE `id` = '".$_SESSION['uid']."'");
                    // Update Memcache
                    $GLOBALS['userdata'][0]['abillity'] = "".$newtag."";
                    $GLOBALS['userdata'][0]['rep_now'] -= $price;
                    $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);          
                    
                    // Show new ability:
                    $this->abillity();
                }
                else{
                    $this->output_buffer .= '<div align="center">You are still too low-level.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
                }
            }
		}
		else{
			$this->output_buffer .= '<div align="center">You do not have enough spirit shards.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
	/*				Check if rankup is allowed		*/
	function allow_rankup(){
		return 0;
	}
	
	function abillity(){
	 	switch($this->char_data[0]['race']){
			case "Shinigami": 
				$title = "Your Zanpaktou's Abillity"; 
				if($this->char_data[0]['level']>19){
					$info = "Upon releasing your bankai you acquire following abilities:";
				}
				elseif($this->char_data[0]['level']>9){
					$info = "Upon releasing your shikai you acquire following abilities:";
				}
				else{
					$info = "You currently have no special abilities.";
				}
				break;
			case "Hollow": 
				$title = "The powers sealed in you"; 
				if($this->char_data[0]['level']>19){
					$info = "Upon releasing the power sealed in your zanpaktou you acquire following abilities:";
				}
                elseif($this->char_data[0]['level']>9){
                    $info = "Your special ability:";
                }
				else{
					$info = "You currently have no special abilities.";
				}
				break;
		}
		$tags = explode("|",$GLOBALS['userdata'][0]['abillity']);
		$this->output_buffer = '<div align="center">
	 	<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
	 	<tr>
        <td colspan="3" align="center" style="border-top:none;" class="subHeader">'.$title.'</td>
        </tr>
        <tr>
        <td colspan="3" align="center" style="padding:2px;">'.$info.'</td>
        </tr>
        <tr>
        	<td colspan="3" align="center" style="border-top:none;"> &nbsp;</td>
        </tr>
        <tr>
        	<td colspan="3" align="center" style="border-top:none;"><i>'.$this->decipher($tags[0]).'<br />'.$this->decipher($tags[1]).'<br />'.$this->decipher($tags[2]).'</i></td>
        </tr>
        <tr>
        	<td colspan="3" align="center" style="border-top:none;"> &nbsp;</td>
        </tr>
		</table>
    	<br />
    	<a href="?id='.$_GET['id'].'">Return</a><br />
  		<br />
		</div>';

	}
	
	/*					Main Profile				*/
	function main_profile(){
        // Fix pools if neccesary
        if(!isset($this->char_data[0]['cur_rei']) || $this->char_data[0]['cur_rei'] == "" ){ $this->char_data[0]['cur_rei'] = 100; }
        if(!isset($this->char_data[0]['cur_health']) || $this->char_data[0]['cur_health'] == ""){ $this->char_data[0]['cur_health'] = 100; }
        
		$level_data = $GLOBALS['database']->fetch_data("SELECT * FROM `levels` WHERE `levelID` = '".$this->char_data[0]['level']."' LIMIT 1");
		$levelup = $this->check_levelup($level_data);
		//		set exp required
		$required_exp = $level_data[0]['experience_required'] - $this->char_data[0]['experience'];
		if($required_exp < 0){$required_exp = 0;}
		//		Set Percentages
		$life_perc = ($this->char_data[0]['cur_health'] / $this->char_data[0]['max_health']) * 100;
		$rei_perc = ($this->char_data[0]['cur_rei'] / $this->char_data[0]['max_rei']) * 100;
		
		//		Set regen
        $race = $GLOBALS['database']->fetch_data("SELECT `regen` FROM `race_vars` WHERE `name` = '".$this->char_data[0]['race']."' LIMIT 1");             
		$regen = $this->char_data[0]['regen_rate'] + $this->char_data[0]['regen_boost'] + $race[0]['regen'];
        
        // Ability Regen
        if(strstr($this->char_data[0]['abillity'], "REG")){
            $tempo = explode("|",$this->char_data[0]['abillity']);
            if(strstr($tempo[0],"REG")){
                $regger = explode(":", $tempo[0]); $abillityreg = $regger[3];
            }
            elseif(strstr($tempo[1],"REG")){
                $regger = explode(":", $tempo[1]); $abillityreg = $regger[3];
            }
            elseif(strstr($tempo[2],"REG")){
                $regger = explode(":", $tempo[2]); $abillityreg = $regger[3];
            }
        }
        else{
            $abillityreg = 0;
        }
        $regen = round($regen*(1 + $abillityreg / 100),1);
		
		//		Set Squad
		if(
            $this->char_data[0]['squad'] == '_none' || 
            $this->char_data[0]['squad'] == '' || 
		    strstr($this->char_data[0]['rank'],"Captain") || 
            strstr($this->char_data[0]['rank'],"Espada")|| 
		    $this->char_data[0]['rank'] == "Leader")
        {
			switch($this->char_data[0]['race']){
				case "Shinigami": $newsquad = rand(1,13); break;
				case "Hollow": $newsquad = rand(1,10); break;
			}
			$GLOBALS['database']->execute_query("UPDATE `users` SET `squad` = '".$newsquad."'  WHERE `id` = '".$_SESSION['uid']."'");
            $clan = '';
            // Update Memcache
            $GLOBALS['userdata'][0]['squad'] = "".$newsquad.""; 
            $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
            
		}
		else{
			switch($this->char_data[0]['race']){
				case "Shinigami": $clan = "Squad#: ".$this->char_data[0]['squad'].""; break;
				case "Hollow": $clan = "Commander: Espada ".$this->char_data[0]['squad'].""; break;
			}
		}
		
		// 		Set ability link
		if(($this->char_data[0]['race'] == "Hollow" && $this->char_data[0]['shikai'] > 0) || ($this->char_data[0]['race'] == "Shinigami" && $this->char_data[0]['shikai'] > 0)){
			$abillity = '<br /><a href="?id='.$_GET['id'].'&act=abillity"><b>- Ability Information -</b></a>';
		}
		            
		//		Create page
		$this->output_buffer = $levelup.'<div align="center"><table width="98%" border="0" cellspacing="0" cellpadding="0">
		
        
  		<tr>
    		<td style="padding:10px;">
            
            <table class="table" width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td colspan="2" align="center" style="border-top:none;" class="subHeader">Profile</td>
                </tr>
                <tr>
                    <td style="padding-left:5px;" width="50%">
                        <b>Character status:</b> <br />
                         Level '.$this->char_data[0]['level'].', '.$this->char_data[0]['rank'].' <br />
                         Race: '.$this->char_data[0]['race'].' <br />
                         Banked: '.$this->char_data[0]['bank'].'<br />
                         '.$clan.' <br /><br /> 
                         Experience: '.$this->char_data[0]['experience'].' <br />
                         Exp needed: '.$required_exp.'  <br />
                         Referrals: '.$this->char_data[0]['referals'].$ref_max.'  <b>(<a href="?id='.$_GET['id'].'&act=referal"><font size="1" color="#C53628">Refer People</font></a>)</b>
                    </td>
                    <td align="center" style="border-left:1px solid #000000;padding-left:5px;padding-top:5px;padding-bottom:5px;" width="50%">
                        ';
                        if(file_exists('./images/avatars/'.$this->char_data[0]['id'].'.gif')){
                            $this->output_buffer .= '<img src="./images/avatars/'.$this->char_data[0]['id'].'.gif" style="border:1px solid #000000;">';
                        }
                        else{
                            $this->output_buffer .= '<img src="./images/default_avatar.gif" style="border:1px solid #000000;">
                                                    ';
                        }
                        if($this->char_data[0]['rep_now'] > 0){
                            $shards = ''.$this->char_data[0]['rep_now'].' <font size="1"><b>(<a href="?id=3&act=shards">absorb</a>, <a href="?id=3&act=gathershards">gather</a>)</b></font>';
                        }
                        else{
                            $shards = '0 <font size="1"><b>(<a href="?id=3&act=gathershards">gather</a>)</b></font>';
                        }
                        if( $this->char_data[0]['referals'] >= 10 ){
                            $ref_max = ", (max)";
                        }
                        else{
                            $ref_max = "";
                        }
                        $battles = explode(':',''.$this->char_data[0]['battles'].'');
                        $this->output_buffer .= '<br /><font size="1"><a href="?id=7"><b>- Edit Preferences -</b></a>
                        <br /><a href="?id=17"><b>- Race Information -</b></a>
                        '.$abillity.'</font>
                    </td>
                </tr>
            </table>
        
        </td>
  		</tr>
  		<tr>
    		<td height="39" style="padding:10px;"><table width="100%" class="table" border="0" cellspacing="0" cellpadding="0">
      	<tr>
        <td width="50%" style="padding-left:5px;"><b>General Info:</b></td>
        <td style="padding-left:5px;border-left:1px solid #000000;"><b>Statistics:</b></td>
      </tr>
      <tr>
        <td style="padding-left:5px;">Name: '.$this->char_data[0]['username'].' </td>
        <td style="padding-left:5px;border-left:1px solid #000000;">Health: '.$this->char_data[0]['cur_health'].' / '.$this->char_data[0]['max_health'].' </td>
      </tr>
      <tr>
        <td style="padding-left:5px;">Gender: '.$this->char_data[0]['gender'].' </td>
        <td style="padding-left:5px;border-left:1px solid #000000;"><div align="left" style="height:5px; width:200px; border: 1px solid #000000;"><img src="./images/life_bar.jpg" style="border-right:1px solid #000000;" height="5px" width="'.$life_perc.'%" /></div></td>
      </tr>
      <tr>
        <td style="padding-left:5px;">E-mail: '.$this->char_data[0]['mail'].' </td>
        <td style="padding-left:5px;border-left:1px solid #000000;">Reiatsu: '.$this->char_data[0]['cur_rei'].' / '.$this->char_data[0]['max_rei'].' </td>
      </tr>
      <tr>
        <td style="padding-left:5px;">Spirit Shards: '.$shards.' </td>
        <td style="padding-left:5px;border-left:1px solid #000000;"><div align="left" style="height:5px; width:200px; border: 1px solid #000000;"><img src="./images/rei_bar.jpg" style="border-right:1px solid #000000;" height="5px" width="'.$rei_perc.'%" /></div></td>
      </tr>
      <tr>
        <td style="padding-left:5px;">&nbsp;</td>
        <td style="padding-left:5px;border-left:1px solid #000000;">&nbsp;</td>
      </tr>
      <tr>
        <td style="padding-left:5px;"><b>Character Activity</b></td>
        <td style="padding-left:5px;border-left:1px solid #000000;">Regeneration rate: '.$regen.' '.$sleep.' </td>
      </tr>
      <tr>
        <td style="padding-left:5px;">Battles won: '.$battles['0'].' </td>
        <td style="padding-left:5px;border-left:1px solid #000000;">Strength: '.$this->char_data[0]['strength'].'</td>
      </tr>
      <tr>
        <td style="padding-left:5px;">
        Battles lost: '.$battles['1'].' </td>
        <td style="padding-left:5px;border-left:1px solid #000000;">Intelligence: '.$this->char_data[0]['intelligence'].' </td>
      </tr>
      <tr>
        <td style="padding-left:5px;">Battles fought: '.$battles['2'].' </td>
        <td style="padding-left:5px;border-left:1px solid #000000;">Speed: '.$this->char_data[0]['speed'].' </td>
      </tr>
      <tr>
        <td style="padding-left:5px;">Win Percentage: ';
        if($battles['2'] > 0){
            $percentage = floor($battles['0']*100/$battles['2']);
        }
        else{
            $percentage = 0;
        }
        if( $percentage > 75 ){
            $this->output_buffer .= '<font color="#005800">'.$percentage.' %</font>';
        }
        elseif( $percentage > 50 ){
             $this->output_buffer .= '<font color="#C66300">'.$percentage.' %</font>';
        }
        else{
            $this->output_buffer .= '<font color="#800000">'.$percentage.' %</font>';
        }                                                 
      
        
      $this->output_buffer .= ' </td>
        <td style="padding-left:5px;border-left:1px solid #000000;">User id: '.$this->char_data[0]['id'].'</td>
      </tr>

      	</table></td>
  		</tr>
		</table></div>';
	}
}

$profile = new profile();
?>