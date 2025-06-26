<?php
class online{
	var $output_buffer;
	
	function online(){
	 	$this->output_buffer .= '<div align="center"><center>
        <table border="0" cellspacing="1" style="border-collapse: collapse" bordercolor="#111111" width="100%" id="AutoNumber1">
		 						 <tr><td width="40%" valign="top">';
		$this->show_user_count();
		$this->output_buffer .= '</td><td width="60%">';
		$this->show_users();
		$this->output_buffer .= '</td></tr></table></center></div>';
		$this->return_stream();
	}
	
	function show_user_count(){
		$users_count = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) AS `users_o` FROM `users`,`users_timer` WHERE `last_regen` >= '".(time() - 120)."' AND `id` = `userid`");
		$mod_count = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) AS `mod_o` FROM `users`,`users_timer` WHERE `last_regen` >= '".(time() - 120)."' AND (`user_rank` = 'Moderator' OR `user_rank` = 'Supermod') AND `id` = `userid`");
		$this->output_buffer .= '<br /><div align="center" valign="top">
		<table width="85%" border="0" class="table" cellspacing="0" cellpadding="0">
        <tr><td colspan="2" align="center" style="border-top:none;" class="subHeader">Statistics:</td></tr>
    	<tr><td width="50%" align="center">Users online: </td>
    	<td width="50%" align="center">'.$users_count[0]['users_o'].'</td>
  	  	</tr><tr><td align="center">Moderators online: </td>
  	    <td align="center">'.$mod_count[0]['mod_o'].'</td>
    	</tr></table><br /></div>';
	}
	
	function show_users(){
		if(!isset($_GET['min']) || !is_numeric($_GET['min']) || $_GET['min'] < 0){
			$min = 0;
			$newmini = 20;
			$newminm = 0;
		}
		else{
			$min = $_GET['min'];
			$newminm = $min - 20;
			if($newminm < 0){
				$newminm = 0;
			}
			$newmini = $min + 20;
		}
		//echo ':'.$min.':'.$max.':';
		$users = $GLOBALS['database']->fetch_data("SELECT `username` FROM `users`,`users_timer` WHERE `last_regen` >= '".(time() - 120)."' AND `id` = `userid` ORDER BY `username` ASC LIMIT ".$min.",20");
		$this->output_buffer .= '<br /><div align="center">
  		<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse; border-style: double; border-width: 3"bordercolor="#111111" width="95%" id="AutoNumber1">
  		<tr><td colspan="2" style="border-top:none;text-align:center;" class="subHeader">Users online</td></tr>';
		if($users != '0 rows'){
			$i = 0;
			while($i < count($users)){
    			$this->output_buffer .= '<tr>
    			<td width="100" colspan="2" align="center"><a href="?id=17&act=profile&name='.$users[$i]['username'].'">'.$users[$i]['username'].'</a></td>
    			</tr>';
    			$i++;
			}
		}
		else{
			$this->output_buffer .= '<tr><td colspan="2" align="center">No users found</td></tr>';
		}
		$this->output_buffer .= '<tr><td align="center"><a href="?id='.$_GET['id'].'&act=users&min='.$newminm.'">&laquo; Previous</a></td><td align="center"><a href="?id='.$_GET['id'].'&act=users&min='.($newmini).'">Next &raquo;</a></td></tr>';
		$this->output_buffer .= '</table></div><br />';
	}
	
	function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
}
$online = new online();
?>