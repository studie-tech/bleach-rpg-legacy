<?php
/*					equip.inc.php
 *				   Equipment pages
 */
class inventory{
	var $output_buffer;

	function inventory(){
		if(!isset($_GET['act'])){
			//	Main inventory screen
			$this->main_page();
		}
		elseif($_GET['act'] == 'sell'){
			if(!isset($_POST['Submit'])){
				//	Confirmation
				$this->confirm_sell_item();
			}
			elseif($_POST['Submit'] == 'Yes'){
				//	Sell item
				$this->do_sell_item();
			}
			elseif($_POST['Submit'] == 'No'){
				$this->output_buffer .= '<div align="center">You did not sell the item <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
		}
		elseif($_GET['act'] == 'equip'){
			$this->equip_item();
		}
		$this->return_stream();
	}
	
	function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	function confirm_sell_item(){
		if(is_numeric($_GET['iid']) && is_numeric($_GET['tc'])){
			$query = "SELECT users_inventory.*, items.type, items.armor_types,items.name,items.price FROM `users_inventory`,`items` WHERE items.id = users_inventory.iid AND users_inventory.uid = '".$_SESSION['uid']."' AND users_inventory.iid = '".$_GET['iid']."' AND `timekey` = '".$_GET['tc']."'";
			$item_data = $GLOBALS['database']->fetch_data($query);
			if($item_data != '0 rows'){
				$this->output_buffer .= '<div align="center">
	  			<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
        		<tr>
        		  <td align="center" style="font-weight:bolder;padding:2px;">Sell Item: </td>
        		</tr>
        		<tr>
        		  <td align="center">Are you sure you wish to sell your <b>'.$item_data[0]['name'].'(s)</b> for <b>'.floor((($item_data[0]['price'] / 2) * $item_data[0]['stack'])).'</b> yen? </td>
        		</tr>
        		<tr>
        		  <td width="100%" align="center"><form name="form1" method="post" action="">
        		    <input type="submit" name="Submit" value="Yes">&nbsp;
        		    <input name="Submit" type="submit" id="Submit" value="No">
        		                      </form>
        		  </td>
        		</tr>
      			</table>
				<br />
  				<br />
				</div>';
			}
			else{
				$this->output_buffer .= '<div align="center">You do not own this item? <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
		}
		else{
			$GLOBALS['error']->handle_error('500','Incorrect item ID: '.$_GET['iid'].'<br />'.$_GET['tc'],'4');
		}
	}
	
	function do_sell_item(){
		if(is_numeric($_GET['iid']) && is_numeric($_GET['tc'])){
			$query = "SELECT users_inventory.*, items.type, items.armor_types,items.name,items.price FROM `users_inventory`,`items` WHERE items.id = users_inventory.iid AND users_inventory.uid = '".$_SESSION['uid']."' AND users_inventory.iid = '".$_GET['iid']."' AND `timekey` = '".$_GET['tc']."'";
			$item_data = $GLOBALS['database']->fetch_data($query);
			if($item_data != '0 rows'){ 
				$sell_price = floor((($item_data[0]['price'] / 2) * $item_data[0]['stack']));
				$query = "DELETE FROM `users_inventory` WHERE `iid` = '".$_GET['iid']."' AND `uid` = '".$_SESSION['uid']."' AND `timekey` = '".$_GET['tc']."' LIMIT 1";
				if($GLOBALS['database']->execute_query($query) !== false){
					$GLOBALS['database']->execute_query("UPDATE `users` SET `bank` = `bank` + '".$sell_price."' WHERE `id` = '".$_SESSION['uid']."' LIMIT 1");
                    // Update Memcache
                    $GLOBALS['userdata'][0]['bank'] += $sell_price;
                    $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
                    
					$this->output_buffer .= '<div align="center">You sold your '.$item_data[0]['name'].' for '.$sell_price.' yen <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
				}
				else{
					$this->output_buffer .= '<div align="center">An error occured selling this item, please try again. <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
				}
			}
			else{
				//	You do not own this item?!?! WTFBBQ
				$this->output_buffer .= '<div align="center">You do not own this item? <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
		}
		else{
			//	Invalid IID:
			$GLOBALS['error']->handle_error('500','Incorrect item ID: '.$_GET['iid'].'<br />'.$_GET['tc'],'4');
		}
	}
	
	function equip_item(){
		if(is_numeric($_GET['iid']) && is_numeric($_GET['tc'])){
			$query = "SELECT users_inventory.*, items.type, items.armor_types,items.name FROM `users_inventory`,`items` WHERE items.id = users_inventory.iid AND users_inventory.uid = '".$_SESSION['uid']."' AND users_inventory.iid = '".$_GET['iid']."' AND `timekey` = '".$_GET['tc']."'";
			$item_data = $GLOBALS['database']->fetch_data($query);
			if($item_data != '0 rows'){ 
				if($item_data[0]['type'] == 'armor' || $item_data[0]['type'] == 'weapon' || $item_data[0]['type'] == 'special'){
					if($item_data[0]['equipped'] == 'yes'){
						//		Unequip item, no need for further checks D:
						$GLOBALS['database']->execute_query("UPDATE `users_inventory` SET `equipped` = 'no' WHERE `uid` = '".$_SESSION['uid']."' AND `timekey` = '".$_GET['tc']."' AND `iid` = '".$_GET['iid']."' LIMIT 1");
						$this->output_buffer .= '<div align="center">You unequipped your '.$item_data[0]['name'].'<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
					}
					else{
						//		All checks: Clear, proceed to final check before equipping
						if($item_data[0]['type'] == 'armor' || $item_data[0]['type'] == 'special'){
							//		Check equipped armor on that part:
							$items_equipped = $GLOBALS['database']->fetch_data("SELECT COUNT(`iid`) AS `armor` FROM `users_inventory`,`items` WHERE `iid` = items.id AND `uid` = '".$_SESSION['uid']."' AND (items.type = 'armor' OR items.type = 'special') AND items.armor_types = '".$item_data[0]['armor_types']."' AND `equipped` = 'yes'");
							if($items_equipped[0]['armor'] == 0){
								$GLOBALS['database']->execute_query("UPDATE `users_inventory` SET `equipped` = 'yes' WHERE `uid` = '".$_SESSION['uid']."' AND `timekey` = '".$_GET['tc']."' AND `iid` = '".$_GET['iid']."' LIMIT 1");
								$this->output_buffer .= '<div align="center">You equipped your '.$item_data[0]['name'].'<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
							}
							else{
								$this->output_buffer .= '<div align="center">You already have this type of armor equipped, please un-equip it first.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
							}
						}
						elseif($item_data[0]['type'] == 'weapon'){
							$items_equipped = $GLOBALS['database']->fetch_data("SELECT COUNT(`iid`) AS `weapons` FROM `users_inventory`,`items` WHERE `iid` = items.id AND `uid` = '".$_SESSION['uid']."' AND items.type = 'weapon' AND `equipped` = 'yes'");
							if($items_equipped['weapons'] < 3){
								//	Still a weapon spot left, set weapon->equipped
								$GLOBALS['database']->execute_query("UPDATE `users_inventory` SET `equipped` = 'yes' WHERE `uid` = '".$_SESSION['uid']."' AND `timekey` = '".$_GET['tc']."' AND `iid` = '".$_GET['iid']."' LIMIT 1");
								$this->output_buffer .= '<div align="center">You equipped your '.$item_data[0]['name'].'<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
							}
							else{
								$this->output_buffer .= '<div align="center">You already have three weapons equipped, please un-equip one first.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
							}
						}
						else{					
							$this->output_buffer .= '<div align="center">how the hell did you get this error? <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
						}
					}
				}
				else{
					//	You cannot equip this item type
					$this->output_buffer .= '<div align="center">You cannot equip items of this item type <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
				}
			}
			else{
				//	You do not own this item?!?! WTFBBQ
				$this->output_buffer .= '<div align="center">You do not own this item? <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
		}
		else{
			//	Invalid IID:
			$GLOBALS['error']->handle_error('500','Incorrect item ID: '.$_GET['iid'].'<br />'.$_GET['tc'],'4');
		}
	}
	
	function main_page(){
		if($_SESSION['user_rank'] != 'user'){
			$maxitm = 40;
		}
		else{
			$maxitm = 20;
		}
		$item_count = $GLOBALS['database']->fetch_data("SELECT COUNT(`iid`) AS `items` FROM `users_inventory` WHERE `uid` = '".$_SESSION['uid']."'");
		if($item_count[0]['items'] < ($maxitm / 3)){
			$items = '<font style="color:darkgreen;font-weight:bolder;">'.$item_count[0]['items'].'</font>';
		}
		elseif($item_count[0]['items'] < ($maxitm / 2)){
			$items = '<font style="color:green;font-weight:bolder;">'.$item_count[0]['items'].'</font>';
		}
		elseif($item_count[0]['items'] >= ($maxitm / 2)){
			$items = '<font style="color:orange;font-weight:bolder;">'.$item_count[0]['items'].'</font>';
		}
		elseif($item_count[0]['items'] >= $maxitm){
			$items = '<font style="color:red;font-weight:bolder;">'.$item_count[0]['items'].'</font>';
		}
		$this->output_buffer .= '<div align="center">
	  	<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
        <tr>
          <td colspan="4" align="center" style="padding:2px;border-top:none;" class="subHeader">Inventory: </td>
        </tr>
        <tr>
        	<td colspan="4" align="center" style="border-bottom:1px solid #000000;"> Your inventory currently holds '.$items.' / '.$maxitm.' items</td>
        </tr>
        <tr>
          <td width="35%" align="left" style="padding-left:5px;border-bottom:1px solid #000000;">Name:</td>
          <td width="25%" align="left" style="border-bottom:1px solid #000000;">Type:</td>
          <td width="20%" align="left" style="border-bottom:1px solid #000000;">&nbsp;</td>
          <td width="20%" align="left" style="border-bottom:1px solid #000000;">&nbsp;</td>
        </tr>';
		if($_SESSION['race']=="Shinigami"){
			$this->output_buffer .= '<tr class="row2">
   			 		 <td align="left" style="padding-left:5px;">Zanpaktou</td>
	   				 <td align="left">Unique</td>
   					 <td align="center">-</td>
   				  	<td align="center">-</td>
		        </tr>';
		}
		
		$inventory = $GLOBALS['database']->fetch_data("SELECT users_inventory.*,items.name,items.price,items.type,items.armor_types FROM `users_inventory`,`items` WHERE `uid` = '".$_SESSION['uid']."' AND `iid` = items.id ORDER BY `type`,`name` ASC");
		if($inventory != '0 rows'){
			$i = 0;
			$row = 'row1';
			while($i < count($inventory)){
				if($inventory[$i]['type'] == 'armor'){
					$type = $inventory[$i]['armor_types'];
				}
				else{
					$type = $inventory[$i]['type'];
				}
				if($inventory[$i]['stack'] > 1){
					$name = $inventory[$i]['name'].' <i>('.$inventory[$i]['stack'].')</i>';
				}
				else{
					$name = $inventory[$i]['name'];	
				}
				if($inventory[$i]['equipped'] == 'yes'){				
					$equip = '<a href="?id='.$_GET['id'].'&act=equip&iid='.$inventory[$i]['iid'].'&tc='.$inventory[$i]['timekey'].'">Unequip</a>';				
				}
				elseif($inventory[$i]['type'] == 'armor' || $inventory[$i]['type'] == 'weapon' || $inventory[$i]['type'] == 'special'){
					$equip = '<a href="?id='.$_GET['id'].'&act=equip&iid='.$inventory[$i]['iid'].'&tc='.$inventory[$i]['timekey'].'">Equip</a>';
				}
				else{
					$equip = '-';
				}
				$this->output_buffer .= '<tr class="'.$row.'">
        		  <td align="left" style="padding-left:5px;"><a href="?id=5&act=detail&iid='.$inventory[$i]['iid'].'">'.$name.'</a></td>
        		  <td align="left">'.ucwords($type).'</td>
        		  <td align="center">'.$equip.'</td>
        		  <td align="center"><a href="?id='.$_GET['id'].'&act=sell&iid='.$inventory[$i]['iid'].'&tc='.$inventory[$i]['timekey'].'">Sell</a></td>
        		</tr>';
				if($row == 'row1'){$row = 'row2';}
				else{$row = 'row1';}
				$i++;
			}
		}
        $this->output_buffer .= '<tr>
          <td colspan="4" align="center" style="border-top:1px solid #000000;">&nbsp;</td>
        </tr>
      	</table>
  		<br />
  		<br />
		</div>';
	}
	
}

$inventory = &new inventory();
?>