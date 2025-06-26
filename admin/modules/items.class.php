<?php
/*
 *				Item administration
 *		add, remove, browse, and edit items
 */
class admin_item{
	var $output_buffer;
	
	function admin_item(){
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
		elseif($_GET['act'] == 'search'){
			$this->search();
		}
		$this->return_stream();
	}
	
	function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	function main_screen(){
		$this->output_buffer .= '<table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
  		<tr><td colspan="5" align="center" class="mini_header">::Item admin:: </td></tr><tr>
    	<td colspan="5" align="center" style="padding-bottom:5px;"><a href="?id='.$_GET['id'].'&act=new">New item</a></td></tr>
  		<tr>
    	<td width="20%" align="center" style="padding-bottom:5px;"><a href="?id='.$_GET['id'].'&type=arm">Armors</a></td>
    	<td width="20%" align="center" style="padding-bottom:5px;"><a href="?id='.$_GET['id'].'&type=spc">Special</a></td>
    	<td width="20%" align="center" style="padding-bottom:5px;"><a href="?id='.$_GET['id'].'&type=ite">Items</a></td>
    	<td width="20%" align="center" style="padding-bottom:5px;"><a href="?id='.$_GET['id'].'&type=art">Artifact</a></td>
  		</tr><tr><td colspan="5" align="center" class="sub_header">&nbsp;</td></tr>
  		<tr><td colspan="5" align="center">';
		if($_GET['type'] == 'arm'){
			$type = 'armor';
		}
		elseif($_GET['type'] == 'wea'){
			$type = 'weapon';
		}
		elseif($_GET['type'] == 'spc'){
			$type = 'special';
		}
		elseif($_GET['type'] == 'ite'){
			$type = 'item';
		}
		else{
			$type = 'artifact';
		}
		$query = "SELECT `name`,`required_rank`,`type`,`in_shop`,`id` FROM `items` WHERE `type` = '".$type."'";
		$this->show_items($query);
		$this->output_buffer .= '</td></tr></table>';
	}
	
	function show_items($query){
		$this->output_buffer .= '<table width="100%" border="0" style="border:none;" cellspacing="0" cellpadding="0">';
		$items = $GLOBALS['database']->fetch_data($query);
		if($items != '0 rows'){
			$this->output_buffer .= '<tr>
        	<td width="25%" style="padding-left:10px;border-bottom:1px solid #000000;font-weight:bold;">Name</td>
        	<td width="15%" style="border-bottom:1px solid #000000;font-weight:bold;">Rank</td>
        	<td width="15%" style="border-bottom:1px solid #000000;font-weight:bold;">Type</td>
        	<td width="15%" style="border-bottom:1px solid #000000;font-weight:bold;">Shop</td>
        	<td width="10%" style="border-bottom:1px solid #000000;font-weight:bold;">&nbsp;</td>
        	<td width="10%" style="border-bottom:1px solid #000000;font-weight:bold;">&nbsp;</td>
      		</tr>';
			$i = 0;
			while($i < count($items)){
				$rowno = $i % 2;
				//	Set rank
				$ranks = array('','Shinigami','Hollow');
      			$this->output_buffer .= '<tr class="row'.$rowno.'">
        		<td style="padding-left:10px;">'.ucfirst(stripslashes($items[$i]['name'])).'</td>
        		<td>'.$ranks[$items[$i]['required_rank']].'</td>
        		<td>'.ucfirst($items[$i]['type']).'</td>
        		<td>'.ucfirst($items[$i]['in_shop']).'</td>
        		<td align="center"><a href="?id='.$_GET['id'].'&act=modify&iid='.$items[$i]['id'].'">Modify</a></td>
        		<td align="center"><a href="?id='.$_GET['id'].'&act=delete&iid='.$items[$i]['id'].'">Delete</a></td>
      			</tr>';
      			$i++;
			}
		}
		else{
			$this->output_buffer .= '<tr>
       		<td colspan="7" align="center">There are no items to show </td>
        	</tr>';
		}
      	$this->output_buffer .= '<tr>
        <td colspan="7" style="border-top:1px solid #000000;">&nbsp;</td>
        </tr>
    	</table>';
	}
	
	//		New item
	
	function new_item(){
		$this->output_buffer .= functions::parse_form('items','New item',array('id'));
	}
	
	function insert_item(){
		if(functions::insert_data('items')){
			$this->output_buffer .= 'The item has been added <br /><a href="?id='.$_GET['id'].'">Return</a>';
		}
		else{
			$this->output_buffer .= 'An error occured and the item has not been added <br /><a href="?id='.$_GET['id'].'">Return</a>';
		}
	}
	
	//		Modify item
	
	function modify_item(){
		$data = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `id` = '".$_GET['iid']."'");
		if($data != '0 rows'){
			$this->output_buffer .= functions::parse_form('items','Update Item',array('id'),$data);
		}
		else{
			$this->output_buffer .= 'This item does not exist<br /><a href="?id='.$_GET['id'].'">Return</a>';
		}
	}
	
	function do_modify_item(){
		if(functions::update_data('items','id',$_GET['iid'])){
			$this->output_buffer .= 'The item has been updated <br /><a href="?id='.$_GET['id'].'">Return</a>';
		}
		else{
			$this->output_buffer .= 'An error occured while updating the item <br /><a href="?id='.$_GET['id'].'">Return</a>';
		}
	}
	
	//		Delete item
	
	function verify_delete(){
		$this->output_buffer .= '<form id="form1" name="form1" method="post" action="">
		<table width="350" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
 		<tr><td colspan="2" class="mini_header">::Delete item:: </td></tr><tr>
    	<td colspan="2" align="center" style="padding:2px;">Delete this item? </td>
  		</tr><tr><td width="224" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" value="Yes" /></td>
  		<td width="224" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" value="No" /></td>
  		</tr></table></form>';
	}
	
	function do_delete(){
		if($GLOBALS['database']->execute_query("DELETE FROM `items` WHERE `id` = '".$_GET['iid']."' LIMIT 1")){
			if($GLOBALS['database']->execute_query("DELETE FROM `users_inventory` WHERE `iid` = '".$_GET['jid']."'")){
				$this->output_buffer .= '<div align="center">the item was deleted from the item table, and all user inventories <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
			else{
				$this->output_buffer .= '<div align="center">The item has been deleted from the item table but a problem occured when deleting the item from all the users, user inventory data is probably broken, contact an administrator with PMA access<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
		}
		else{
			$this->output_buffer .= '<div align="center">An error occured while deleting the item <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
	//		Search
	
	function search(){
		$this->output_buffer .= '<form id="form1" name="form1" method="post" action="">
		<table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
  		<tr><td colspan="4" align="center" class="mini_header">::Item admin:: </td></tr>
  		<tr><td colspan="4" align="center">Search for item: </td></tr><tr>
    	<td width="22%" align="center" style="padding-top:4px;">Name:</td>
    	<td width="30%" align="left" style="padding-top:4px;"><input name="name" type="text" class="textfield" id="name" /></td>
    	<td width="11%" align="left" style="padding-left:5px;padding-top:4px;">In shop: </td>
    	<td width="37%" align="left" style="padding-top:4px;"><select name="in_shop" class="listbox" id="in_shop">
      	<option>yes</option><option>no</option><option selected="selected">any</option>
    	</select></td></tr><tr><td align="center" style="padding-top:4px;">Type:</td>
    	<td align="left" style="padding-top:4px;"><select name="item_type" class="listbox" id="item_type">
      	<option>item</option><option>artifact</option><option>armor</option><option>special</option>
      	<option selected="selected">any</option></select></td><td align="left" style="padding-top:4px;">Rank: </td>
    	<td align="left" style="padding-top:4px;"><select name="rank_type" class="listbox" id="rank_type">
      	<option>&lt;</option><option>&gt;</option><option>&lt;=</option><option selected="selected">&gt;=</option>
      	<option>=</option></select><select name="rank_id" class="listbox" id="rank_id">
        <option value="1" selected="selected">Academy Student</option><option value="2">Genin</option>
        <option value="3">Chuunin</option><option value="4">Jounin</option><option value="5">Special Jounin</option>
        </select></td></tr><tr><td align="center" style="padding-top:4px;">Armor type: </td>
    	<td align="left" style="padding-top:4px;"><select name="armor_type" class="listbox" id="armor_type">
      	<option>armor</option><option>helmet</option><option>gloves</option><option>belt</option>
      	<option>shoes</option><option>pants</option><option selected="selected">any</option>
    	</select></td><td align="left" style="padding-top:4px;">Price</td>
    	<td align="left" style="padding-top:4px;"><select name="price_type" class="listbox" id="price_type">
      	<option>&lt;</option><option>&gt;</option><option>&lt;=</option><option>&gt;=</option>
      	<option>=</option></select>
      	<input name="price_int" type="text" class="textfield" id="price_int" size="15" /></td>
  		</tr><tr><td colspan="4" align="left">&nbsp;</td></tr><tr>
    	<td colspan="4" align="center" style="padding:5px;"><input name="Submit" type="submit" class="button" value="Submit" /></td>
  		</tr></table></form><br />';
		if(isset($_POST['Submit'])){
			$this->print_search();
		}
	}
	
	function print_search(){
		/*				Generate Query			*/
		$query = "SELECT `name`,`required_rank`,`type`,`in_shop`,`id` FROM `items` ";
		if($_POST['name'] != ''){
			$query .= "WHERE `name` LIKE '%".$_POST['name']."%'";
			$preset = 1;
		}
		if($_POST['in_shop'] != 'any'){
			if($preset == 1){
				$query .= " AND ";
			}
			else{
				$query .= "WHERE";
			}
			$query .= "`in_shop` = '".$_POST['in_shop']."'";
		}
		if($_POST['armor_type'] != 'any'){
			if($preset == 1){
				$query .= " AND ";
			}
			else{
				$query .= "WHERE";
			}
			$query .= "`armor_types` = '".$_POST['armor_type']."'";
		}
		if($_POST['item_type'] != 'any'){
			if($preset == 1){
				$query .= " AND ";
			}
			else{
				$query .= "WHERE";
			}
			$query .= "`item_type` = '".$_POST['item_type']."'";
		}
		if($_POST['price_int'] != ''){
			if($preset == 1){
				$query .= " AND ";
			}
			else{
				$query .= "WHERE";
			}
			$query .= "`price` ".$_POST['price_type']." '".$_POST['price_int']."'";
		}
		if($_POST['rank_id'] != '1' || $_POST['rank_type'] != '>='){
			if($preset == 1){
				$query .= " AND ";
			}
			else{
				$query .= "WHERE";
			}
			$query .= "`required_rank` ".$_POST['rank_type']." '".$_POST['rank_id']."'";
		}
		/*		DEBUG QUERY		*/
		//echo $query;
		/*				Output result			*/
		$this->output_buffer .= '<div align="center"><table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
  		<tr><td width="100%" align="center" class="mini_header">::Search results:: </td></tr><tr>
    	<td align="center">';
		$this->show_items($query);
		$this->output_buffer .= '</td>
  		</tr></table></div><br /><br />';
	}
}

$item = new admin_item();
?>