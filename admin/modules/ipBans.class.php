<?php

error_reporting(E_ALL);

class admin_ipBans{
	var $output_buffer;
	
	function admin_ipBans(){
		if(!isset($_GET['act'])){
			$this->main_screen();
		}
		elseif($_GET['act'] == 'new'){
			if(!isset($_POST['Submit'])){
				$this->new_item();
			}
			else{
				$this->insert_item();
			}
		}
		elseif($_GET['act'] == 'modify' && is_numeric($_GET['iid'])){
			if(!isset($_POST['Submit'])){
				$this->modify_item();
			}
			else{
				$this->do_modify_item();
			}
		}
		elseif($_GET['act'] == 'delete' && is_numeric($_GET['iid'])){
			if(!isset($_POST['Submit'])){
				echo '1';
				$this->verify_delete();
			}
			elseif($_POST['Submit'] == 'Yes'){
				$this->do_delete();
			}
			else{
				$this->main_screen();
			}
		}
		$this->return_stream();
	}
	
	function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	function main_screen(){
		$this->output_buffer .= '<table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
  		<tr>
            <td colspan="5" align="center" class="mini_header">::IP bans:: </td>
        </tr>
        <tr>
    	    <td colspan="5" align="center" style="padding-bottom:5px;"><a href="?id='.$_GET['id'].'&act=new">Ban New IP</a></td></tr>
  		<tr>
            <td colspan="5" align="center">';

		$this->show_bans();
		$this->output_buffer .= '</td></tr></table>';
	}
	
	function show_bans(){
		$this->output_buffer .= '<table width="100%" border="0" style="border:none;" cellspacing="0" cellpadding="0">';
		$bans = $GLOBALS['database']->fetch_data("SELECT * FROM `banned_ips` LIMIT 100");
		if($bans != '0 rows'){
			$this->output_buffer .= '<tr>
        	<td width="20%" style="padding-left:10px;border-bottom:1px solid #000000;font-weight:bold;">IP</td>
        	<td width="60%" style="border-bottom:1px solid #000000;font-weight:bold;">Ban Reason</td>
            <td width="10%" style="border-bottom:1px solid #000000;font-weight:bold;">&nbsp;</td>
            <td width="10%" style="border-bottom:1px solid #000000;font-weight:bold;">&nbsp;</td>
      		</tr>';
			$i = 0;
			while($i < count($bans)){
				$rowno = $i % 2;
				//	Set rank
      			$this->output_buffer .= '<tr class="row'.$rowno.'">
        		<td style="padding-left:10px;">'.ucfirst(stripslashes($bans[$i]['ip'])).'</td>
        		<td>'.ucfirst(stripslashes($bans[$i]['short_reason'])).'</td>
                <td align="center"><a href="?id='.$_GET['id'].'&act=modify&iid='.$bans[$i]['id'].'">Modify</a></td>
                <td align="center"><a href="?id='.$_GET['id'].'&act=delete&iid='.$bans[$i]['id'].'">Delete</a></td>
      			</tr>';
      			$i++;
			}
		}
		else{
			$this->output_buffer .= '<tr>
       		<td colspan="7" align="center">There are no bans to show </td>
        	</tr>';
		}
      	$this->output_buffer .= '<tr>
        <td colspan="7" style="border-top:1px solid #000000;">&nbsp;</td>
        </tr>
    	</table>';
	}
	
	//		New item
	
	function new_item(){
		$this->output_buffer .= functions::parse_form('banned_ips','New IP Ban',array('id'));
	}
	
	function insert_item(){
		if(functions::insert_data('banned_ips')){
			$this->output_buffer .= 'The IP ban has been added <br /><a href="?id='.$_GET['id'].'">Return</a>';
		}
		else{
			$this->output_buffer .= 'An error occured and the ban has not been added <br /><a href="?id='.$_GET['id'].'">Return</a>';
		}
	}
	
	//		Modify item
	
	function modify_item(){
		$data = $GLOBALS['database']->fetch_data("SELECT * FROM `banned_ips` WHERE `id` = '".$_GET['iid']."'");
		if($data != '0 rows'){
			$this->output_buffer .= functions::parse_form('banned_ips','Update Ban',array('id'),$data);
		}
		else{
			$this->output_buffer .= 'This ban does not exist<br /><a href="?id='.$_GET['id'].'">Return</a>';
		}
	}
	
	function do_modify_item(){
		if(functions::update_data('banned_ips','id',$_GET['iid'])){
			$this->output_buffer .= 'The ban has been updated <br /><a href="?id='.$_GET['id'].'">Return</a>';
		}
		else{
			$this->output_buffer .= 'An error occured while updating the ban <br /><a href="?id='.$_GET['id'].'">Return</a>';
		}
	}
	
	//		Delete item
	
	function verify_delete(){
		$this->output_buffer .= '<form id="form1" name="form1" method="post" action="">
		<table width="350" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
 		<tr><td colspan="2" class="mini_header">::Delete IP ban:: </td></tr><tr>
    	<td colspan="2" align="center" style="padding:2px;">Delete this ban? </td>
  		</tr><tr><td width="224" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" value="Yes" /></td>
  		<td width="224" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" value="No" /></td>
  		</tr></table></form>';
	}
	
	function do_delete(){
		if($GLOBALS['database']->execute_query("DELETE FROM `banned_ips` WHERE `id` = '".$_GET['iid']."' LIMIT 1")){
			$this->output_buffer .= '<div align="center">The ban has been deleted<br />
                                     <a href="?id='.$_GET['id'].'">Return</a></div>';
		}
		else{
			$this->output_buffer .= '<div align="center">An error occured while deleting the ban <br />
                                     <a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
}

$bans = new admin_ipBans();
?>