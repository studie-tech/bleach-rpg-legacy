<?php
if(include('../global_libs/database.class.php')){

    $GLOBALS['database'] = &new database;
    	
	$GLOBALS['database']->execute_query("DELETE FROM `tavern` WHERE `time` < ".(time()-3600).""); 
	
	$GLOBALS['database']->execute_query("DELETE FROM `users_pm` WHERE (`subject` = 'You\'ve been defeated' OR `subject` = 'You\'ve defeated an opponent') AND `time` < ".(time()-3600*1).""); 
    
    $GLOBALS['database']->execute_query("DELETE FROM `users_pm` WHERE `read` = 'yes' AND `time` < ".(time()-3600*3).""); 
    
    $GLOBALS['database']->execute_query("DELETE FROM `users_pm` WHERE `time` < ".(time()-3600*48).""); 
	
	echo"Angel flew by";
		

}
?>
