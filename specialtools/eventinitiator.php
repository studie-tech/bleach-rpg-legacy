<?php
if(include('../global_libs/database.class.php')){

    $GLOBALS['database'] = &new database;
    
    /*      Hook up with Memcache server    */
    $GLOBALS['cache'] = new Memcache;
    $server_hostname = '173.203.108.8';
    $GLOBALS['cache']->connect($server_hostname, 11211) or die ("Could not connect");
    
    $rand1 = rand(1,3);
    
    $options = $GLOBALS['database']->fetch_data("SELECT * FROM `event_options` WHERE `option` = 'activate_event' OR `option` = 'total_events'");
    $mod = $options[1]['value']*5;
    if($options[0]['value'] == 0){
    
 	   // Randomize enemy
	    switch($rand1){
			case 1: 
				$GlobalShinigamiM = 'A lone Vaizard has been spotted on the map. Track down and eliminate him!'; 
				$GlobalHollowM = 'A lone Vaizard has been spotted on the map. They are soooo tasty..!';
				$query = "`race` = 'Vaizard', `username` = 'Lone Vaizard', ";
				// Tavern message
					$Tuser = "'<span style=\'color:AA1111;font-weight:bold;\'>Lone Vaizard</span>'";
					$TmessageShinig = "'<span style=\'color:AA1111;font-weight:bold;\'>Shinigami Scum! You know nothing of true power! </span>'";
					$TmessageHollow = "'<span style=\'color:AA1111;font-weight:bold;\'>Damned hollows.. we\'ll rid this world of every one of you! </span>'";
				break;
			case 2: 
				$GlobalShinigamiM = 'A vicious Bounto has been identified. Track down and eliminate him!'; 
				$GlobalHollowM = 'A vicious Bounto has been identified.  Are you not hungry?!'; 
				$query = "`race` = 'Bounto', `username` = 'Vicious Bounto', ";
				// Tavern message
					$Tuser = "'<span style=\'color:AA1111;font-weight:bold;\'>Vicious Bounto</span>'";
					$TmessageShinig = "'<span style=\'color:AA1111;font-weight:bold;\'>We will take revenge on the Shinigami scum! Bow before us!</span>'";
					$TmessageHollow = "'<span style=\'color:AA1111;font-weight:bold;\'>You are all.. worthless!.. We will rid the world of your existence!</span>'";
				break;
			case 3: 
				$GlobalShinigamiM = 'A Quincy is destroying hollow souls. Track him down and stop him!'; 
				$GlobalHollowM = 'A delicious Quincy has been sensed in the area. A free meal!..'; 
				$query = "`race` = 'Quincy', `username` = 'Vengeful Quincy', ";
				// Tavern message
					$Tuser = "'<span style=\'color:AA1111;font-weight:bold;\'>Vengeful Quincy</span>'";
					$TmessageShinig = "'<span style=\'color:AA1111;font-weight:bold;\'>We will take revenge on the Shinigami scum! Bow before us!</span>'";
					$TmessageHollow = "'<span style=\'color:AA1111;font-weight:bold;\'>You are all.. worthless!.. We will rid the world of your existence!</span>'";
				break;
		}
		
		$query .= '`strength` = 5000 + 10*'.$mod.', `intelligence` = 5000 + 10*'.$mod.', `speed` = 10 + 5*'.$mod.', 
				   `sword` = 1000 + 10*'.$mod.', `shikai` = 1000 + 10*'.$mod.', `bankai` = 1000 + 10*'.$mod.',
				   `cur_health` = `max_health`, `cur_rei` = `max_rei` WHERE `id` = 92';
	    	
	    echo"".$query."";	
	    // Fire all the queries
		$GLOBALS['database']->execute_query("UPDATE `users` SET `healed` = '".$GlobalShinigamiM."' WHERE `race` = 'Shinigami'"); 
		$GLOBALS['database']->execute_query("UPDATE `users` SET `healed` = '".$GlobalHollowM."' WHERE `race` = 'Hollow'"); 	
        
		$GLOBALS['database']->execute_query("UPDATE `event_options` SET `value` = `value` + 1  WHERE `option` = 'activate_event' OR `option` = 'total_events'"); 
		$GLOBALS['database']->execute_query("UPDATE `users` SET ".$query.""); 
		$GLOBALS['database']->execute_query("INSERT INTO `tavern` ( `location` , `user` , `user_data` , `user_id` , `time` , `message` )
						  				     VALUES ('1', ".$Tuser.", 'Event Character', '92', '".time()."', ".$TmessageShinig.");");  
		$GLOBALS['database']->execute_query("INSERT INTO `tavern` ( `location` , `user` , `user_data` , `user_id` , `time` , `message` )
						  				     VALUES ('2', ".$Tuser.", 'Event Character', '92', '".(time() + 1)."', ".$TmessageHollow.");");  
        $GLOBALS['cache']->flush();						  				     
		
	    
        
		echo"Event Initiated";
	}
	else{
		echo"Other event already active";
	}	
    
    $GLOBALS['database']->close_connection();

}
?>
