<?php
class itemshop{
	var $output_buffer;
	var $user;
    var $village;
	
	function itemshop(){
		$this->user = $GLOBALS['database']->fetch_data("SELECT `bank`,`rankid`,`race` FROM `users` WHERE `id` = '".$_SESSION['uid']."' LIMIT 1");
		if(!isset($_GET['act'])){
			$this->main_page();	
		}
		elseif($_GET['act'] == 'buy'){
			$this->buy_item();
		}
		elseif($_GET['act'] == 'detail'){
			$this->show_details();
		}

		$this->return_stream();
	}
	
	function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	function buy_item(){
		$maxitm = 40;
		if(is_numeric($_GET['iid'])){
			$item_data = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `id` = '".$_GET['iid']."' LIMIT 1");
			if($item_data != '0 rows'){
				if($item_data[0]['in_shop'] == 'normal'){
					$user_item = $GLOBALS['database']->fetch_data("SELECT COUNT(`iid`) AS `items` FROM `users_inventory` WHERE `uid` = '".$_SESSION['uid']."'");
					if($item_data[0]['price'] <= $this->user[0]['bank']){
					 	if($item_data[0]['required_rank'] == $this->user[0]['rankid'] || $item_data[0]['required_rank'] == 0){

						    //			Buy item:
						    if($item_data[0]['stack_size'] > 1){  
							    //		Check if item already exists and add one to stack
							    $current = $GLOBALS['database']->fetch_data("SELECT * FROM `users_inventory` WHERE `iid` = '".$_GET['iid']."' AND `uid` = '".$_SESSION['uid']."' LIMIT 1");
							    if($current == '0 rows'){
								    //		No incomplete stack found
								    if($user_item[0]['items'] < $maxitm){
									    $query = "INSERT INTO `users_inventory` ( `uid` , `iid` , `equipped` , `stack` , `timekey` , `itemtype`) VALUES ('".$_SESSION['uid']."', '".$_GET['iid']."', 'no', '1', '".time()."', '".$item_data[0]['type']."');";
									    $GLOBALS['database']->execute_query($query);
									    $GLOBALS['database']->execute_query("UPDATE `users` SET `bank` = `bank` - '".$item_data[0]['price']."' WHERE `id` = '".$_SESSION['uid']."' LIMIT 1");
                                        
                                        // Update Memcache
                                        $GLOBALS['userdata'][0]['bank'] -= $item_data[0]['price'];
                                        $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
                                        
									    $this->output_buffer .= '<div align="center">You have bought one '.$item_data[0]['name'].' for '.$item_data[0]['price'].' yen <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
								    }
								    else{
									    $this->output_buffer .= '<div align="center">Your inventory is full <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
								    }
							    }
							    else{
                                    if($current[0]['stack'] < $item_data[0]['stack_size']){
                                        //        Incomplete stack found, increment
                                        $query = "UPDATE `users_inventory` SET `stack` = `stack` + 1 WHERE `iid` = '".$_GET['iid']."' AND `uid` = '".$_SESSION['uid']."' AND `stack` < '".$item_data[0]['stack_size']."' LIMIT 1";
                                        $GLOBALS['database']->execute_query($query);
                                        $GLOBALS['database']->execute_query("UPDATE `users` SET `bank` = `bank` - '".$item_data[0]['price']."' WHERE `id` = '".$_SESSION['uid']."' LIMIT 1");
                                        
                                        // Update Memcache
                                        $GLOBALS['userdata'][0]['bank'] -= $item_data[0]['price'];
                                        $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
                                        
                                        $this->output_buffer .= '<div align="center">You have bought one '.$item_data[0]['name'].' for '.$item_data[0]['price'].' yen <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
                                    }
                                    else{
                                        //      No incomplete stack found, deny
                                        $this->output_buffer .= '<div align="center">Your inventory cannot hold more of this item. <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
                                    }
								    
							    }
						    }
						    else{
							    //		Insert new item unstackable item
							    if($user_item[0]['items'] < $maxitm){
								    $query = "INSERT INTO `users_inventory` ( `uid` , `iid` , `equipped` , `stack` , `timekey` , `itemtype` ) VALUES ('".$_SESSION['uid']."', '".$_GET['iid']."', 'no', '1', '".time()."', '".$item_data[0]['type']."');";
								    $GLOBALS['database']->execute_query($query);
								    $GLOBALS['database']->execute_query("UPDATE `users` SET `bank` = `bank` - '".$item_data[0]['price']."' WHERE `id` = '".$_SESSION['uid']."' LIMIT 1");
                                    
                                    // Update Memcache
                                    $GLOBALS['userdata'][0]['bank'] -= $item_data[0]['price'];
                                    $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
                                    
								    $this->output_buffer .= '<div align="center">You have bought one '.$item_data[0]['name'].' for '.$item_data[0]['price'].' yen <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
							    }
							    else{
								    $this->output_buffer .= '<div align="center">Your inventory is full <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
							    }
						    }
						}
						else{
							$this->output_buffer .= '<div align="center">Your race cannot buy this equipment<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
						}
					}
					else{
						$this->output_buffer .= '<div align="center">You cannot afford this item <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
					}
				}
				else{
					$this->output_buffer .= '<div align="center">This item cannot be bought in shops <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
				}
			}
			else{
				$this->output_buffer .= '<div align="center">This item does not exist <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
		}
		else{
			$GLOBALS['error']->handle_error('500','Incorrect item ID: '.$_GET['iid'],'4');
		}
	}
	
	function show_details(){
		if(is_numeric($_GET['iid'])){
			$item_data = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `id` = '".$_GET['iid']."' LIMIT 1");
			if($item_data != '0 rows'){
				if($item_data[0]['in_shop'] == 'normal' || $item_data[0]['type'] == 'special'){
					$this->output_buffer = '<div align="center">
	 				<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
        			<tr>
          			<td colspan="4" align="center" style="padding:2px;font-weight:bolder;">Item Details: : </td>
        			</tr>
        			<tr>
        			<td width="25%" height="21" align="center" style="font-weight:bolder;">Name:</td>
          			<td colspan="2" align="left">'.$item_data[0]['name'].'</td>
          			<td width="25%" align="center">&nbsp;</td>
        			</tr>
        			<tr>
          			<td align="center" style="font-weight:bolder;">Stackable: </td>
          			<td width="25%" align="left">';
					if($item_data[0]['stack_size'] == 1){
						$this->output_buffer .= 'No';
					}
					else{
						$this->output_buffer .= 'Yes ('.$item_data[0]['stack_size'].')';
					}
					$this->output_buffer .= '</td>
          			<td width="25%" style="font-weight:bolder;" align="center">Required Race: </td>
          			<td align="left">';
					if($item_data[0]['required_rank'] == '1'){
						$this->output_buffer .= 'Shinigami';
					}
					elseif($item_data[0]['required_rank'] == '2'){
						$this->output_buffer .= 'Hollow';
					}
					else{
						$this->output_buffer .= 'None';
					}
					
					$this->output_buffer .= '</td>
        			</tr>
        			<tr>
          			<td align="center" style="font-weight:bolder;">Type:</td>
          			<td align="left">';
                    if($item_data[0]['type'] == 'armor'){
                        $this->output_buffer .= $item_data[0]['armor_types'];
                    }
                    else{
                        $this->output_buffer .= $item_data[0]['type'];
                    }
                    $this->output_buffer .= '</td>
          			<td align="center" style="font-weight:bolder;">Price:</td>
          			<td align="left">'.$item_data[0]['price'].' yen</td>
        			</tr>
        			<tr>
          			<td align="center">&nbsp;</td>
          			<td align="center">&nbsp;</td>
          			<td align="center">&nbsp;</td>
          			<td align="center">&nbsp;</td>
        			</tr>
        			<tr>
          			<td colspan="4" align="center" style="font-weight:bolder;padding:2px;">Description:</td>
        			</tr>
        			<tr>
          			<td colspan="4" align="center">'.functions::parse_BB($item_data[0]['description']).'</td>
          			</tr>
        			<tr>
          			<td colspan="4" align="center">&nbsp;</td>
        			</tr>
      				</table>
      				<br />
      				<a href="?id='.$_GET['id'].'">Return</a><br />
  					<br />
					</div>';
				}
				else{
					$this->output_buffer .= '<div align="center">This item is not buyable in shops <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
				}
			}
			else{
				$this->output_buffer .= '<div align="center">This item does not exist <br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
		}
		else{
			$GLOBALS['error']->handle_error('500','Incorrect item ID: '.$_GET['iid'],'4');
		}
	}
	

	
	function main_page(){
		$this->output_buffer .= '<div align="center">
	  	<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
        <tr>
        <td colspan="2" align="center" style="border-top:none;" class="subHeader">Item shop</td>
        </tr>
        <tr>
        <td colspan="2" align="center"><b>Order shop by:</b>
		<font size="-2"> 
		<a href="?id='.$_GET['id'].'&type=armor">Armor</a> - 
		<a href="?id='.$_GET['id'].'&type=item">Items</a>
		</font>
		</td>
        </tr>

        <tr>
        <td colspan="2" align="center">';
		$this->parse_shop();
		$this->output_buffer .= '</td>
        </tr>
        <tr>
        <td colspan="2" style="border-top:1px solid #000000;" align="center">&nbsp;</td>
        </tr>
     	</table>
  		<br />
  		<br />
		</div>';
	}
	
	function parse_shop(){
		if(!isset($_GET['type']) || ($_GET['type'] != 'armor' && $_GET['type'] != 'item')){
			$query = "SELECT * FROM `items` WHERE (`required_rank` = '".$this->user[0]['rankid']."' || `required_rank` = 0) AND `in_shop` = 'normal' ORDER BY `id` DESC LIMIT 10";
		}
		else{
			$query = "SELECT * FROM `items` WHERE (`required_rank` = '".$this->user[0]['rankid']."' || `required_rank` = 0) AND `in_shop` = 'normal' AND `type` = '".$_GET['type']."' ORDER BY `price` ASC";
		}
		$this->output_buffer .= '<table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr class="subHeader">
              <td width="46%" align="center" style="border-bottom:1px solid #000000;">Name (maximum stack size)</td>
              <td width="22%" align="center" style="border-bottom:1px solid #000000;">Price</td>
              <td width="16%" align="center" style="border-bottom:1px solid #000000;">&nbsp;</td>
              <td width="16%" align="center" style="border-bottom:1px solid #000000;">&nbsp;</td>
            </tr>';
		$item_data = $GLOBALS['database']->fetch_data($query);
		if($item_data != '0 rows'){
			$i = 0;
			$row = 'row1';
			while($i < count($item_data)){
				$this->output_buffer .= '<tr class="'.$row.'">
        	      <td style="padding-left:5px;">'.$item_data[$i]['name'];
				if($item_data[$i]['stack_size'] > 1){
					$this->output_buffer .= '<i> ('.$item_data[$i]['stack_size'].')</i>';
				}
				$this->output_buffer .= '</td>
       		       <td align="center">'.$item_data[$i]['price'].' yen</td>
       		       <td align="center"><a href="?id='.$_GET['id'].'&act=detail&iid='.$item_data[$i]['id'].'">Details</a></td>
        	      <td align="center"><a href="?id='.$_GET['id'].'&act=buy&iid='.$item_data[$i]['id'].'">Buy</a></td>
        	    </tr>';
				if($row == 'row1'){$row = 'row2';}
				else{$row = 'row1';}
				$i++;
			}
		}
		else{
			$this->output_buffer .= '<tr><td colspan="4" style="font-weight:bold;text-align:center;">There are no items for your race</td></tr>';
		}
        $this->output_buffer .= '</table>';
	}
}

$shop = &new itemshop();
?>