<?php
if(include('../global_libs/database.class.php')){

    $GLOBALS['database'] = &new database;
    	
	$GLOBALS['database']->execute_query("UPDATE `users` SET `cur_health` = `max_health`, `healed` = 'All have been healed by System!'"); 
	
	echo"Angel flew by";
		

}
?>
