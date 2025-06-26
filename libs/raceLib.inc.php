<?php
/*
 *			Village CP functions library
 *					normal user
 */
class village{
	var $user;
	var $village;
	var $output_buffer;
	
	//	Return stream to core:
	function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
    function setData(){
        $this->user = $GLOBALS['database']->fetch_data("SELECT `id`,`race`,`rank`,`level`,`rankid`,`squad`,`experience`,`bank`,`username` FROM `users` WHERE `id` = '".$_SESSION['uid']."'");
        $this->village = $GLOBALS['database']->fetch_data("SELECT * FROM `races` LEFT JOIN (`race_vars`) ON races.name = race_vars.name WHERE races.`name` = '".$this->user[0]['race']."'");
    }
	
	//	Page constructor:
	function village(){
		$this->setData();
		if(!isset($_GET['sub'])){
			if(!isset($_GET['act'])){
				$this->main_screen();	
			}
			elseif($_GET['act'] == 'status'){
				$this->status();
			}
			elseif($_GET['act'] == 'orders'){
				$this->orders();
			}
			elseif($_GET['act'] == 'users'){
				$this->users();
			}
			elseif($_GET['act'] == 'check'){
				$this->check_kage();
			}
			elseif($_GET['act'] == 'challenge'){
				$this->challenge();
			}
			elseif($_GET['act'] == 'profile'){
				$this->view_profile();
			}
			elseif($_GET['act'] == 'highlevels'){
				$this->view_highups();
			}
			elseif($_GET['act'] == 'claimsquadleader'){
				$this->claim_squadleader();
			}
			elseif($_GET['act'] == 'squad'){
				$this->squad();
			}
			if(isset($_GET['act'])){
				$this->returnlink();
			}
			$this->return_stream();
		}
		else{
			//			captain/leader submenu
			if($this->village[0]['leader'] == $this->user[0]['username'] || strstr($this->user[0]['rank'],"Captain") || strstr($this->user[0]['rank'],"Espada")){
				require_once('./libs/leader_option.inc.php');
				$submenu = new kage_options();
				$submenu->setData($this->village,$this->user);
				$submenu->parse();
			}
			else{
				$this->output_buffer .= '<div align="center">You are not the leader of this race.</div>';
			}
		}
	}
	
	function view_highups(){
	 	if($this->user[0]['race'] == "Hollow"){
			$title = 'The Espada';
			$info = 'Only the strongest hollows earn themselves the right to be in the Espada Elite; the ultimate bringers of destruction and chaos.';
		}else{
			$title = 'Protection Squads';
			$info = 'The captains of the 13 protection squads are here to help and guide the shinigamis in their search to purge the world of hollows.';
		}
			$this->output_buffer .= '<div align="center">
  		<table width="90%" border="0" class="table" cellspacing="0" cellpadding="0">
  			<tr>
    			<td colspan="3" align="center" style="border-top:none;padding-top:2px;padding-bottom:2px;" class="subHeader">'.$title.'</td>
    		</tr>
			<tr>
		      	<td colspan="3" align="center" style="padding-top:2px;">'.$info.'</td>
	    	</tr>
			<tr>
				<td colspan="3" align="center">&nbsp;</td>
			</tr>
			<tr class="subHeader">
              <td width="8%" align="center" style="border-bottom:1px solid #000000;">ID</td>
              <td width="63%" align="center" style="border-bottom:1px solid #000000;">Username</td>
              <td align="center">Status</td>
            </tr>';
			$highlevels = explode(":",$this->village[0]['highlevels']);
			$row = 'row1';
			$i = 1;
			foreach ($highlevels as &$value) {
			 	/*	Check if the user is there	*/
			 	if($value == "-"){
					$leader[0] = "-";
				}
				else{
					$leader = explode("-",$value);
				}
				
				/*	Determine additional messages	*/
			 	if(strstr($this->user[0]['rank'],"Captain") || strstr($this->user[0]['rank'],"Espada")){
				  	$challenge = '';
				}
				elseif($leader[0] == "-"){
				  	$challenge = ' - <a href="?id='.$_GET['id'].'&act=claimsquadleader&i='.$i.'">Claim Leadership</a> - ';
					$leader[0] = "";
				}
				elseif($this->user[0]['level'] > 19 && $leader['0'] !== $this->user[0]['username']){
					$challenge = ' <font size="1">(<a href="?id=18&opp='.$leader['1'].'">Challenge</a>) </font>';
				}
				else{
					$challenge = '';
				}
				if($leader[0] == $this->user[0]['username']){
				 	$challenge .= ' <font size="1">(<a href="?id='.$_GET['id'].'&sub=squadleader">Options</a>) </font>';
				}
				
				/*	Output	*/
				$tempo = '<a href="?id='.$_GET['id'].'&act=squad&i='.$i.'">Subordinates</a>';
			    $this->output_buffer .= '<tr class="'.$row.'">
			    	<td width="8%" align="center">'.$i.'</td>
		      		<td width="63%" align="center"><a href="?id='.$_GET['id'].'&act=profile&name='.$leader['0'].'"><b>'.$leader['0'].'</b></a>'.$challenge.'</td>
	    			<td align="center">'.$tempo.'</td>';
					  
				$this->output_buffer .= '</tr>';
	    		if($row == 'row1'){$row = 'row2';}
				else{$row = 'row1';}
				$i++;
			}

		$this->output_buffer .= '</table></div>';       
	}
	
	
	function squad(){
	 	if(!isset($_GET['min']) || !is_numeric($_GET['min']) || $_GET['min'] < 0){
			$min = 0;
			$newmini = 10;
			$newminm = 0;
		}
		else{
			$min = $_GET['min'];
			$newminm = $min - 10;
			if($newminm < 0){
				$newminm = 0;
			}
			$newmini = $min + 10;
		}
		if(!isset($_GET['max']) || !is_numeric($_GET['max']) || $_GET['max'] < 0){
			$max = 10;
			$newmaxi = 20;
			$newmaxm = 10;
		}
		if(!is_numeric($_GET['i'])){
		 	$_GET['i'] = 1;
		}	 
	 	$orders = explode("|||:::|||:::|||;;;",$this->village[0]['orders']);
	 	$this->output_buffer = '<div align="center">
  		<table width="90%" border="0" class="table" cellspacing="0" cellpadding="0">
    	<tr>
      	<td width="99%" align="center" style="padding-top:2px;padding-bottom:2px;border-top:none;" class="subHeader"><b>Commander Orders :</b></td>
    	</tr>
    	<tr>
    	<td align="center" style="padding-top:2px;">'.functions::parse_BB($orders[$_GET['i']]).'</td>
    	</tr>
    	<tr>
    	<td align="center">&nbsp;</td>
    	</tr>
  		</table>
		</div><br />';
		
		$users = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `squad` = '".$_GET['i']."' AND `rankid` = '".$this->user[0]['rankid']."' ORDER BY `experience` DESC LIMIT ".$min.",20");
		$this->output_buffer .= '<div align="center">
  		<table border="0" cellpadding="0" cellspacing="0" class="table" width="95%" id="AutoNumber1">
    	<tr>
      	<td width="100" style="border-top:none;" class="subHeader" align="center"><b>Username</b></td>
      	<td width="100" style="border-top:none;" class="subHeader" align="center"><b>Rank</b></td>
      	<td width="100" style="border-top:none;" class="subHeader" align="center"><b>Experience</b></td>
      	<td width="100" style="border-top:none;" class="subHeader" align="center"><b>Profile</b></td>
    	</tr>';
		if($users != '0 rows'){
			$i = 0;
			while($i < count($users)){
			 	if(!strstr($users[$i]['rank'],"Captain")&&!strstr($users[$i]['rank'],"Espada")){
					$this->output_buffer .= '<tr class="row'.(($i % 2) + 1).'">
	    	  		<td width="100" align="center">'.$users[$i]['username'].'</td>
	    	  		<td width="100" align="center">'.$users[$i]['rank'].'</td>
	    	 		<td width="100" align="center">'.$users[$i]['experience'].'</td>
	    			<td width="100" align="center"><a href="?id=17&act=profile&name='.$users[$i]['username'].'">Public Profile</a></td>
	    			</tr>';
				}
    			$i++;
			}
		}
		else{
			$this->output_buffer .= '<tr><td colspan="4" align="center">No users found</td></tr>';
		}
		$this->output_buffer .= '<tr><td colspan="2" style="border-top:1px solid #000000;" align="center"><a href="?id='.$_GET['id'].'&i='.$_GET['i'].'&act=users&min='.($newminm).'">&laquo; Previous</a></td>
                                     <td colspan="2" style="border-top:1px solid #000000;" align="center"><a href="?id='.$_GET['id'].'&i='.$_GET['i'].'&act=squad&min='.($newmini).'">Next &raquo;</a></td></tr>';
		$this->output_buffer .= '</table></div>';
	}
	
	function claim_squadleader(){
	 	if($this->user[0]['race'] == "Hollow"){
			$Nrank = 'Espada';
		}else{
			$Nrank = 'Captain';
		}
	 	$index = $_GET['i']-1;
		$highlevels = explode(":",$this->village[0]['highlevels']);
		if($highlevels[$index] == "-"){
			if($this->user[0]['level'] > 19){
                if($this->user[0]['rank'] !== "Leader"){
				    if($this->user[0]['rank'] !== "Leader" && 
				       !strstr($this->user[0]['rank'],"Captain") && 
				       !strstr($this->user[0]['rank'],"Espada")){
					    /*	Ready to upload status	*/
					    $newstring = ""; $i = 1;
					    foreach ($highlevels as &$value) {
					 	    if($i !== 1){$newstring .= ":";}
					 	    if($i == $_GET['i']){
						  	    $newstring .= "".$this->user[0]['username']."-".$_SESSION['uid']."";
						    }
						    else{
							    $newstring .= "".$value."";	
						    }
						    $i++;
					    }
					    $query1 = "UPDATE `races` SET `highlevels` = '".$newstring."' WHERE `name` = '".$this->user[0]['race']."' LIMIT 1";
					    $query2 = "UPDATE `users` SET `rank` = '".$Nrank." ".$_GET['i']."' WHERE `id` = '".$_SESSION['uid']."' LIMIT 1";

                        // Update Memcache
                        $GLOBALS['userdata'][0]['rank'] = "".$Nrank." ".$_GET['i']."";
                        $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);          
                        
					    $GLOBALS['database']->execute_query($query1);
					    $GLOBALS['database']->execute_query($query2);
					    $this->output_buffer.='<div align="center" style="color:black;">
										       You have claimed this position<br /></div>';
				    }
                    else{
                        $this->output_buffer.='<div align="center" style="color:red;">
                                               Your current position makes you unable to do this<br /></div>';
                    }
                }
                else{
                    $this->output_buffer.='<div align="center" style="color:red;">
                                           You are already leader<br /></div>';
                }
			}        
			else{
				$this->output_buffer.='<div align="center" style="color:red;">
									   You\'re not strong enough for this position!<br /></div>';
			}
		}
		else{
			$this->output_buffer.='<div align="center" style="color:red;">
								   You cannot claim this position without a fight!<br /></div>';
		}
	}
	
	
	
	/*				Main functions				*/
	function main_screen(){
		if($this->village[0]['leader'] == $this->user[0]['username']){
			$kage = '<a href="?id='.$_GET['id'].'&sub=kage">Leader options</a>';
		}
		else{
			$kage = '&nbsp;';
		}
		if($this->user[0]['race'] == "Hollow"){
			$highlevels = '<a href="?id='.$_GET['id'].'&act=highlevels">Espada</a>';
			//$highlevels = '<a href="http://www.youfail.org">Espada</a>'; // Temporary disable link
		}
		else{
			$highlevels = '<a href="?id='.$_GET['id'].'&act=highlevels">Captains</a>';
			//$highlevels = '<a href="http://www.youfail.org">Captains</a>'; // Temporary disable link
		}
		$this->output_buffer .= '<div align="center">
  		<table width="90%" border="0" class="table" cellspacing="0" cellpadding="0">
  		<tr>
    	<td colspan="3" align="center" style="border-top:none;padding-top:2px;padding-bottom:2px;" class="subHeader">Race Control Panel</td>
    	</tr><tr>
      	<td colspan="3" align="center" style="padding-top:2px;">This is where you can see your race status,<br />
        and if you are strong enough, challenge the leader for his or her position. </td>
    	</tr><tr><td colspan="3" align="center">&nbsp;</td></tr><tr>
      	<td width="33%" align="center"><a href="?id='.$_GET['id'].'&act=orders">Leader orders </a></td>
      	<td width="33%" align="center"><a href="?id='.$_GET['id'].'&act=users">Members</a> </td>
      	<td width="33%" align="center"><a href="?id='.$_GET['id'].'&act=check">Check Leader </a></td>
    	</tr>
		<tr>
			<td colspan="1" align="center"><br />&nbsp;<br /><br /></td>
			<td colspan="1" align="center"><br />'.$highlevels.'<br /><br /></td>
			<td colspan="1" align="center"><br />'.$kage.'<br /><br /></td>
		</tr></table></div>';
	}
	
	
	function view_profile(){
		if(isset($_GET['name'])){
			$search = $_GET['name'];
		}
		elseif(isset($_POST['name'])){
			$search = $_POST['name'];
		}
		$char_data = $GLOBALS['database']->fetch_data("SELECT * FROM `users`, `users_timer` WHERE users.username = '".$search."' AND users_timer.userid = users.id LIMIT 1");
        if($char_data == '0 rows'){
			//			User does not exist
			$this->output_buffer = '<div align="center">This user does not exist</div>';
		}
		else{

			//		Set Clan
			if($char_data[0]['clan'] == '_none' || $char_data[0]['clan'] == ''){
				$clan = 'None';
			}
			else{
				$clan = $char_data[0]['clan'];
			}
            // Set squad
            if($char_data[0]['race'] == "Shinigami"){
                $clan .= "<br />Squad#: ".$char_data[0]['squad'];   
            }
            
			//		Login?
			if($char_data[0]['last_regen'] > (time()-120)){
  				$this->output_buffer .= '<div align="center"><font size="5" color="#008000"><b>.::Online::.</b></font></div>';
			}else{
  				$this->output_buffer .= '<div align="center"><font size="5" color="#FF0000"><b>.::Offline::.</b></font></div>';
			}
            if( $char_data[0]['race'] == $this->user[0]['race'] ){
                $pools = '<tr>
                             <td style="padding-left:5px;">Health: '.$char_data[0]['cur_health'].' / '.$char_data[0]['max_health'].' </td></tr><tr>
                             <td style="padding-left:5px;">Reiatsu: '.$char_data[0]['cur_rei'].' / '.$char_data[0]['max_rei'].'</td></tr>
                          <tr>';
            }
			//			Output Profile
			$this->output_buffer .= '
            <div align="center">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
  			        <tr>
    		            <td style="padding:10px;">
                            <table class="table" width="100%" border="0" cellspacing="0" cellpadding="0">
    		                    <tr>
        	                        <td style="padding-left:5px;" width="50%"><b>Character status:</b></td>
    	                            <td rowspan="9" valign="top" align="center" style="padding-top:10px; padding-bottom:10px;">
                                        <b>::User picture::</b><br>';
			                            if(file_exists('./images/avatars/'.$char_data[0]['id'].'.gif')){
				                            $this->output_buffer .= '<img src="./images/avatars/'.$char_data[0]['id'].'.gif" style="border:1px solid #000000;">';
			                            }
			                            else{
				                            $this->output_buffer .= '<img src="./images/default_avatar.gif" style="border:1px solid #000000;">';
			                            }
                                        $this->output_buffer .= '   <br /><a href="?id=8&act=newpm&user='.$char_data[0]['username'].'">Send PM</a> 
			                        </td>
                                </tr>
                                <tr>
   		                            <td style="padding-left:5px;">Name: '.$char_data[0]['username'].' </td>
			                    </tr>
			                    <tr>
			                        <td style="padding-left:5px;">Gender: '.$char_data[0]['gender'].' </td>
			                    </tr>
			                    <tr>
  		                            <td style="padding-left:5px;">Level '.$char_data[0]['level'].', '.$char_data[0]['rank'].'</td></tr><tr>
    	                            <td style="padding-left:5px;">Race: '.$char_data[0]['race'].'</td></tr><tr>
  		                            <td style="padding-left:5px;">Clan: '.$clan.'</td></tr>
                                    '.$pools.'
                                    ';

            $this->output_buffer .= '
  		    <td style="padding-left:5px;">&nbsp;</td></tr></table></td></tr></table></div>';
            

		}
	}
	
    function returnlink(){
		$this->output_buffer .= '<div align="center"><a href="?id='.$_GET['id'].'">Return</a></div>';
	}
	

	function orders(){
	 	$orders = explode("|||:::|||:::|||;;;",$this->village[0]['orders']);
		$this->output_buffer = '<div align="center">
  		<table width="90%" border="0" class="table" cellspacing="0" cellpadding="0">
    	<tr>
      	<td width="99%" align="center" style="padding-top:2px;padding-bottom:2px;border-top:none;" class="subHeader"><b>Leader orders :</b></td>
    	</tr>
    	<tr>
    	<td align="center" style="padding-top:2px;">'.functions::parse_BB($orders[0]).'</td>
    	</tr>
    	<tr>
    	<td align="center">&nbsp;</td>
    	</tr>
  		</table>
		</div>';
	}
	
	function users(){
		if(!isset($_GET['min']) || !is_numeric($_GET['min']) || $_GET['min'] < 0){
			$min = 0;
			$newmini = 10;
			$newminm = 0;
		}
		else{
			$min = $_GET['min'];
			$newminm = $min - 10;
			if($newminm < 0){
				$newminm = 0;
			}
			$newmini = $min + 10;
		}
		if(!isset($_GET['max']) || !is_numeric($_GET['max']) || $_GET['max'] < 0){
			$max = 10;
			$newmaxi = 20;
			$newmaxm = 10;
		}
		//echo ':'.$min.':'.$max.':';
		$users = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `rankid` = '".$this->village[0]['rankid']."' ORDER BY `experience` DESC LIMIT ".$min.",20");
		$this->output_buffer .= '<div align="center">
  		<table border="0" cellpadding="0" cellspacing="0" class="table" width="95%" id="AutoNumber1">
    	<tr>
      	<td width="100" style="border-top:none;" class="subHeader" align="center"><b>Username</b></td>
      	<td width="100" style="border-top:none;" class="subHeader" align="center"><b>Rank</b></td>
      	<td width="100" style="border-top:none;" class="subHeader" align="center"><b>Experience</b></td>
      	<td width="100" style="border-top:none;" class="subHeader" align="center"><b>Profile</b></td>
    	</tr>';
		if($users != '0 rows'){
			$i = 0;
			while($i < count($users)){
    			$this->output_buffer .= '<tr class="row'.(($i % 2) + 1).'">
    	  		<td width="100" align="center">'.$users[$i]['username'].'</td>
    	  		<td width="100" align="center">'.$users[$i]['rank'].'</td>
    	 		<td width="100" align="center">'.$users[$i]['experience'].'</td>
    			<td width="100" align="center"><a href="?id=17&act=profile&name='.$users[$i]['username'].'">Public Profile</a></td>
    			</tr>';
    			$i++;
			}
		}
		else{
			$this->output_buffer .= '<tr><td colspan="4" align="center">No users found</td></tr>';
		}
		$this->output_buffer .= '<tr><td colspan="2" style="border-top:1px solid #000000;" align="center"><a href="?id='.$_GET['id'].'&act=users&min='.($newminm).'">&laquo; Previous</a></td>
                                     <td colspan="2" style="border-top:1px solid #000000;" align="center"><a href="?id='.$_GET['id'].'&act=users&min='.($newmini).'">Next &raquo;</a></td></tr>';
		$this->output_buffer .= '</table></div>';
	}
	
	function check_kage(){
			//	Real leader
			$kage_data = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `username` = '".$this->village[0]['leader']."' LIMIT 1");
			if($kage_data != '0 rows'){
				if(file_exists("./images/avatars/".$kage_data[0]['id'].'.gif')){
					$avatar = '<img src="./images/avatars/'.$kage_data[0]['id'].'.gif" />';
				}
				else{
					$avatar = '<img src="./images/default_avatar.gif" />';
				}
				if($kage_data[0]['id'] != $_SESSION['uid']){
					$challenge = '<a href="?id=18&opp='.$kage_data[0]['id'].'">Challenge</a>';
				}
				else{
					$challenge = '&nbsp;';
				}
				$this->output_buffer .= '<div align="center"><br />
				<table width="95%" class="table" border="0" cellspacing="0" cellpadding="0"><tr>
    			<td colspan="3" style="text-align:center;border-top:none;" class="subHeader">Check leader: </td>
    			</tr><tr><td width="20%" align="left" style="padding-left:5px;font-weight:bold;"><br />Name:</td>
    			<td width="30%" align="left"><br />'.$kage_data[0]['username'].'</td>
    			<td width="50%" rowspan="6" align="center" valign="middle" style="padding:5px;">'.$avatar.'</td></tr><tr>
    			<td align="left" style="padding-left:5px;font-weight:bold;">Rank:</td>
    			<td align="left">'.$kage_data[0]['rank'].'</td></tr><tr>
    			<td align="left" style="padding-left:5px;font-weight:bold;">&nbsp;</td>
    			<td align="left">&nbsp;</td></tr><tr>
    			<td colspan="2" align="center" style="padding-left:5px;"><a href="?id=17&act=profile&name='.$kage_data[0]['username'].'">View profile</a></td></tr><tr>
    			<td colspan="2" align="center" style="padding-left:5px;">'.$challenge.'</td>
    			</tr><tr><td colspan="3" align="center" style="padding-left:5px;">&nbsp;</td></tr></table></div>';
			}
			else{
				$GLOBALS['error']->handle_error('500','The kage data for '.$this->village[0]['name'].' is corrupted',8);
			}
		
	}
}