<?php
/*							Travel.inc.php
 *			Travelling, getting attacked, enter villages
 */

class travel{
	//			SETTINGS:
	var $MAX_X = 4; //Global maximum Y coordinate (latitude)
	var $MAX_Y = 4; //Global maximum X coordinate (longitude)
	var $MIN_X = 1;
	var $MIN_Y = 1;
	//			VARIABLES:
	var $output_buffer;
	var $user;
	var $latitude;
	var $longitude;
	var $location;
    var $region;
	
	function travel(){
		$this->user = $GLOBALS['database']->fetch_data("SELECT `race`,`location`,`level` FROM `users` WHERE `id` = '".$_SESSION['uid']."'");
		$this->location = $this->user[0]['location'];
		$temp = explode(':',$this->user[0]['location']);
		$this->longitude = $temp[0];
		$this->latitude = $temp[1];
		$this->target = $temp[2];
		if(isset($_GET['move']) && ($_GET['move'] == 'north' || $_GET['move'] == 'south' || $_GET['move'] == 'west' || $_GET['move'] == 'east')){
			$this->do_move();
		}
		$this->show_map();
		$this->return_stream();
	}
    
    function show_dots( $x , $y ){
        $print = "";
        // User Dot
        if( $x == $this->longitude && $y == $this->latitude){
            $print .= '<img src="./images/dots/red.png" border="0" />';
        }
        // Target Dot
        if( $x == $this->Elongitude && $y == $this->Elatitude){
            $print .= '<img src="./images/dots/blue.png" border="0" />';
        }
        // Event Dot
        if( $x == $this->Evemtlongitude && $y == $this->Evemtlatitude){
            $print .= '<img src="./images/dots/yellow.png" border="0" />';
        }
        
        // Return result
        return $print;
    }
    
	
	function show_map(){
	 	/* 						Tracking Feature				
	 	 *		This whole thing contains the tracking function and the event mapper
	 	 */
	 	
	 	if(isset($_POST['target']) && $_POST['target'] !== $this->target ){
	 	 	$this->enemytest = $GLOBALS['database']->fetch_data("SELECT `location` FROM `users` WHERE `username` = '".$_POST['target']."' LIMIT 1");
	 	 	if($this->enemytest != '0 rows'){
	 	 	 	/* 	New Target Acquired	*/
	 	 		$this->target = $_POST['target'];
	 	 		$this->location = ''.$this->longitude.':'.$this->latitude.':'.$this->target.'';
				$GLOBALS['database']->execute_query("UPDATE `users` SET `location` = '".$this->location."' WHERE `id` = '".$_SESSION['uid']."' LIMIT 1");
                
                // Update Memcache
                $GLOBALS['userdata'][0]['location'] = "".$this->location."";
                $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
				
                	// Map the enemy
					$temp = explode(':',$this->enemytest[0]['location']);
					$this->Elongitude = $temp[0];
					$this->Elatitude = $temp[1];
					$mapper = "&Elo=".$this->Elongitude."&Ela=".$this->Elatitude."";
			}
			else{
				$this->target = "Invalid User";
			}

		}
		elseif($this->target == ""){
			$this->target = "None";
		}
		else{
			$this->enemytest = $GLOBALS['database']->fetch_data("SELECT `location` FROM `users` WHERE `username` = '".$this->target."' LIMIT 1");
			if($this->enemytest != '0 rows'){
	 	 	 	$temp = explode(':',$this->enemytest[0]['location']);
				$this->Elongitude = $temp[0];
				$this->Elatitude = $temp[1];
				$mapper = "&Elo=".$this->Elongitude."&Ela=".$this->Elatitude."";
			}
			else{
				$this->target = "Invalid User";
			}
			
		}
		$this->eventactivator = $GLOBALS['database']->fetch_data("SELECT * FROM `event_options` WHERE `option` = 'activate_event' OR `option` = 'latest_battles' LIMIT 2");
        
		if($this->eventactivator[1]['value'] > 0){
			$this->eventchar = $GLOBALS['database']->fetch_data("SELECT `location` FROM `users` WHERE `rank` = 'Event Character' LIMIT 1");
			if($this->eventchar != '0 rows'){
				$temp = explode(':',$this->eventchar[0]['location']);
				$this->Evemtlongitude = $temp[0];
				$this->Evemtlatitude = $temp[1];
				$Eventmapper = "&Evlo=".$this->Evemtlongitude."&Evla=".$this->Evemtlatitude."";
			}
		}
		
         // <img src="./libs/map.inc.php?lo='.$this->longitude.'&la='.$this->latitude.''.$mapper.''.$Eventmapper.'" border="0" /> <br />
		//			Output Map screen:
        $this->output_buffer .= '<div align="center">
        <table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
          <tr>
            <td align="center" colspan="2" style="border-top:none;" class="subHeader" colspan="6">World Map</td>
        </tr>

        <tr>
            <td width="50%" align="center" valign="top" style="padding-top:10px;padding-bottom:10px;">Your location: <b>'.$this->longitude.':'.$this->latitude.'</b>, <br />Karakura Town<br />
			<a href="?id=11"><b> - Online Users - </b></a><br /><br />
            
                    <table border="0" width="300" cellspacing="0" cellpadding="0" height="350" background="./images/map2.gif">
                        <tr>
                            <td width="29" height="30">&nbsp;</td>
                            <td width="66" height="30">&nbsp;</td>
                            <td width="75" height="30">&nbsp;</td>
                            <td height="30">&nbsp;</td>
                            <td width="62" height="30">&nbsp;</td>
                        </tr>
                        <tr>
                            <td width="29" height="69">&nbsp;</td>
                            <td width="66" height="69" align="center">'.$this->show_dots( 1 , 1 ).'</td>
                            <td width="75" height="69" align="center">'.$this->show_dots( 2 , 1 ).'</td>
                            <td height="69" align="center">'.$this->show_dots( 3 , 1 ).'</td>
                            <td width="62" height="69" align="center">'.$this->show_dots( 4 , 1 ).'</td>
                        </tr>
                        <tr>
                            <td width="29" height="72">&nbsp;</td>
                            <td width="66" height="72" align="center">'.$this->show_dots( 1 , 2 ).'</td>
                            <td width="75" height="72" align="center">'.$this->show_dots( 2 , 2 ).'</td>
                            <td height="72"  align="center">'.$this->show_dots( 3 , 2 ).'</td>
                            <td width="62" height="72"  align="center">'.$this->show_dots( 4 , 2 ).'</td>
                        </tr>
                        <tr>
                            <td width="29" height="70">&nbsp;</td>
                            <td width="66" height="70"  align="center">'.$this->show_dots( 1 , 3 ).'</td>
                            <td width="75" height="70"  align="center">'.$this->show_dots( 2 , 3 ).'</td>
                            <td height="70"  align="center">'.$this->show_dots( 3 , 3 ).'</td>
                            <td width="62" height="70"  align="center">'.$this->show_dots( 4 , 3 ).'</td>
                        </tr>
                        <tr>
                            <td width="29" >&nbsp;</td>
                            <td width="66"  align="center">'.$this->show_dots( 1 , 4 ).'</td>
                            <td width="75"  align="center">'.$this->show_dots( 2 , 4 ).'</td>
                            <td  align="center">'.$this->show_dots( 3 , 4 ).'</td>
                            <td width="62" align="center">'.$this->show_dots( 4 , 4 ).'</td>
                        </tr>
                        <tr>
                            <td width="29" height="47">&nbsp;</td>
                            <td width="66" height="47">&nbsp;</td>
                            <td width="75" height="47">&nbsp;</td>
                            <td height="47">&nbsp;</td>
                            <td width="62" height="47">&nbsp;</td>
                        </tr>
                    </table>
            
            
            
			<b>Move:</b><br />';
	        if($this->latitude > $this->MIN_Y ){
	            $this->output_buffer .= '<a href="?id='.$_GET['id'].'&move=north">North </a>';
	        }
	        if($this->longitude < $this->MAX_X){
	            $this->output_buffer .= '<a href="?id='.$_GET['id'].'&move=east">East </a>';
	        }
	        if($this->longitude > $this->MIN_X){
	            $this->output_buffer .= '<a href="?id='.$_GET['id'].'&move=west">West </a>';
	        }
	        if($this->latitude < $this->MAX_Y){
	            $this->output_buffer .= '<a href="?id='.$_GET['id'].'&move=south">South </a>';
	        }
	        $this->output_buffer .= '</td>
			<td valign="top" align="center" style="padding-top:10px;padding-bottom:10px;"><br />';
			
			if($this->user[0]['level'] < 20){
				$levelrestrict = "`level` < '".$this->user[0]['level']."' + 5 AND `level` >= '".$this->user[0]['level']."'";
			}
			else{
				$levelrestrict = "`level` > 19";
			}
			
			/* 		Show Users in this area		*/
			$query = "SELECT `username`, `id`, `level`, `race` FROM `users` WHERE (".$levelrestrict." OR `rank` = 'Event Character') AND `race` != '".$this->user[0]['race']."' AND `cur_health` != 0 AND `location` LIKE '".$this->longitude.":".$this->latitude."%' ORDER BY `level`, `experience` ASC LIMIT 10";
			
			$this->output_buffer .= '<table width="95%" border="0" cellspacing="0" cellpadding="0">
            	<tr class="subHeader">
	              <td align="center" style="border-bottom:1px solid #000000;" colspan="3">Enemies in this area</td>
	            </tr>
				<tr class="row2">
	              <td align="center" style="border-bottom:1px solid #000000;">Username</td>
	              <td align="center" style="border-bottom:1px solid #000000;">Level</td>
	              <td align="center" style="border-bottom:1px solid #000000;">Action</td>
	            </tr>';
			$users = $GLOBALS['database']->fetch_data($query);
			$row = 'row1';
			if($users != '0 rows'){
				$i = 0;
				while($i < count($users)){
					$this->output_buffer .= '<tr class="'.$row.'">
	        	       <td style="padding-left:5px;" align="center"><a href="?id=17&act=profile&name='.$users[$i]['username'].'">'.$users[$i]['username'].'</a></td>
	       		       <td align="center">'.$users[$i]['level'].'</td>
	       		       <td align="center"><a href="?id=18&opp='.$users[$i]['id'].'">Attack</a></td>
	        	    </tr>';
					if($row == 'row1'){$row = 'row2';}
					else{$row = 'row1';}
					$i++;
				}
			}
			else{
				$this->output_buffer .= '<tr class="'.$row.'"><td colspan="4" style="font-weight:bold;text-align:center;">There are no enemies fit for you</td></tr>';
			}
			
    	    $this->output_buffer .= '</table><p>
			<table width="95%" border="0" cellspacing="0" cellpadding="0">
            	<tr class="subHeader">
	              <td align="center" style="border-bottom:1px solid #000000;" colspan="3">Training Matches</td>
	            </tr>
				<tr class="row2">
	              <td align="center" style="border-bottom:1px solid #000000;">Username</td>
	              <td align="center" style="border-bottom:1px solid #000000;">Level</td>
	              <td align="center" style="border-bottom:1px solid #000000;">Action</td>
	            </tr>
	            <tr class="row1">
	              <td align="center" style="border-bottom:1px solid #000000;">Random Fella</td>
	              <td align="center" style="border-bottom:1px solid #000000;">'.$this->user[0]['level'].'</td>
	              <td align="center" style="border-bottom:1px solid #000000;"><a href="?id=18&opp=CLONE">Attack</a></td>
	            </tr>
	            </table>
	            <p>
	            <table width="95%" border="0" cellspacing="0" cellpadding="0">
            	<tr class="subHeader">
	              <td align="center" style="border-bottom:1px solid #000000;" colspan="3">Latest Battles</td>
	            </tr>
	            ';
				  $arr = explode(":::",$this->eventactivator[0]['related_text']);
				  foreach ($arr as &$value) {
				     $this->output_buffer .= '<tr class="'.$row.'" valign="center">
	              								<td align="center" style="border-bottom:0px solid #000000;" colspan="3">
							 				    '.$value.'</td>
											  </tr>';
					 if($row == 'row1'){$row = 'row2';}
					 else{$row = 'row1';}
				  }
				  $this->output_buffer .= '
	            </table>
<p>
	            	        <table width="95%" border="0" cellspacing="0" cellpadding="0">
            	<tr class="subHeader">
	              <td align="center" style="border-bottom:1px solid #000000;" colspan="3">Enemy Targeting</td>
	            </tr>
				<tr class="row2">
	              <td align="center" style="border-bottom:1px solid #000000;" colspan="3">Track down an enemy on the map</td>
	            </tr>
	            <tr class="row1" valign="center">
	              <td align="center" style="border-bottom:1px solid #000000;" colspan="3">
				  		<form name="form1" method="post" action=""><p>
				  		<input name="target" value="'.$this->target.'" type="text" size="20">
				  		<input type="submit" name="Track" value="Track">
						</form>
				  </td>
	            </tr>
	            </table>
	        </tr>
	        </table>
	        </div>';
	}

	
	function do_move(){
		//		movement!
		$temp = explode(':',$this->user[0]['location']);
		if($_GET['move'] == 'west'){
			if($temp[0] > $this->MIN_X){
				$this->latitude = $temp[1];
				$this->longitude = $temp[0] - 1;
			}
			else{
				$this->output_buffer = '<div align="center" style="color:red;">You cannot move this way</div>';
			}
		}
		elseif($_GET['move'] == 'east'){
			if($temp[0] < $this->MAX_X){
				$this->latitude = $temp[1];
				$this->longitude = $temp[0] + 1;
			}
			else{
				$this->output_buffer = '<div align="center" style="color:red;">You cannot move this way</div>';
			}
		}
		elseif($_GET['move'] == 'north'){
			if($temp[1] > $this->MIN_Y){
				$this->latitude = $temp[1] - 1;
				$this->longitude = $temp[0];
			}
			else{
				$this->output_buffer = '<div align="center" style="color:red;">You cannot move this way</div>';
			}
		}
		elseif($_GET['move'] == 'south'){
			if($temp[1] < $this->MAX_Y){	
				$this->latitude = $temp[1] + 1;
			    $this->longitude = $temp[0];
			}
			else{
				$this->output_buffer = '<div align="center" style="color:red;">You cannot move this way</div>';
			}
		}
		
		
        //  Check event and attacks
        //$this->check_events();
        //  Update user
        $this->location = ''.$this->longitude.':'.$this->latitude.':'.$this->target.'';
		$GLOBALS['database']->execute_query("UPDATE `users` SET `location` = '".$this->location."' WHERE `id` = '".$_SESSION['uid']."' LIMIT 1");
        
        // Update Memcache
        $GLOBALS['userdata'][0]['location'] = "".$this->location."";
        $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
	}
    
	function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
}

$travel = new travel();
?>