<?php
if(include('../global_libs/database.class.php')){

    $GLOBALS['database'] = &new database;
    
    /*      Hook up with Memcache server    */
    $GLOBALS['cache'] = new Memcache;
    $server_hostname = '173.203.109.251';
    $GLOBALS['cache']->connect($server_hostname, 11211) or die ("Could not connect to MemCache Server");
    	
	$GLOBALS['database']->execute_query("UPDATE `users` SET `cur_health` = `max_health` WHERE `rank` != 'Event Character'"); 
    $GLOBALS['cache']->flush();
    
    $GLOBALS['database']->execute_query("OPTIMIZE TABLE  `admin_edits` ,  `admin_notes` ,  `battle_options` ,  `event_options` ,  `items` ,  `levels` ,  `moderator_log` ,  `pages` ,  `races` ,  `race_vars` ,  `referals` ,  `tavern` ,  `users` ,  `users_events` , `users_inventory` ,   `users_pm` ,  `users_pm_settings` ,  `users_timer` ,  `user_reports`"); 
	
	echo"Angel flew by";
		

}
?>
