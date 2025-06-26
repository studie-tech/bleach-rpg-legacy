<?php
if(include('./global_libs/database.class.php')){

    $GLOBALS['database'] = &new database;

	$subid = addslashes($_GET['SubId']);
	
	$earn = $_GET['Earn'];
	
	$GLOBALS['database']->execute_query("UPDATE `users` SET `rep_now` = `rep_now` + '$earn', `rep_ever` = `rep_ever` + '$earn' WHERE `id` = '".$subid."' LIMIT 1"); 
	
	$GLOBALS['database']->execute_query("INSERT INTO `lead_notifications` ( `id` , `time` , `money` , `userID` ) VALUES ('', '".time()."', '".$earn."', '".$subid."')"); 
		

}
?>
