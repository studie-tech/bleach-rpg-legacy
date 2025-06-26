<?php
/*
 *			Village panel kage options library
 */
class kage_options{
	private $village;
	private $output_buffer;
	
	//	Return stream to core:
	private function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	//	Get data
	public function setData($villageData, $userData){
		if(($villageData != '0 rows' && $villageData != '')){
			$this->village = $villageData;
		}
		if(($userData != '0 rows' && $userData != '')){
			$this->user = $userData;
		}
	}	
	
	public function parse(){
        if(strtolower($_SESSION['username']) == strtolower($this->village[0]['leader']) || 
		strstr($this->user[0]['rank'],"Captain") || 
		strstr($this->user[0]['rank'],"Espada")){
		 
		    if(!isset($_GET['act'])){
			    $this->kage_menu();
		    }
		    elseif($_GET['act'] == 'edit_orders'){
			    if(!isset($_POST['Submit'])){
				    $this->edit_orders();
			    }
			    else{
				    $this->do_edit();
			    }
		    }
		    elseif($_GET['act'] == 'resign'){
			    $this->resign();
		    }
            elseif($_GET['act'] == 'points' && strtolower($_SESSION['username']) == strtolower($this->village[0]['leader'])){
                if(!isset($_POST['Submit'])){
                    $this->villagePoint_menu();
                }
                else{
                    $this->villagePoint_spend();
                }  
            }
		    if(isset($_GET['act'])){
			    $this->output_buffer .= '<div align="center"><br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		    }
        }
        else{
            $this->output_buffer .= '<div align="center" style="color:darkred;">You do not have permission to access this page.<br /><a href="?id='.$_GET['id'].'&sub=kage">Return</a></div>';   
        }
		$this->return_stream();
	}
	
	private function kage_menu(){
		if(strtolower($_SESSION['username']) == strtolower($this->village[0]['leader'])){
			$points = '<a href="?id='.$_GET['id'].'&sub=kage&act=points">Spend race points</a>';
		}
		$this->output_buffer .= '<div align="center">
		<table width="95%" class="table" border="0" cellspacing="0" cellpadding="0"><tr>
    	<td width="68%" colspan="3" style="text-align:center;border-top:none;" class="subHeader">Options</td></tr>
  		<tr><td align="center" width="33%"><a href="?id='.$_GET['id'].'&sub=kage&act=edit_orders">Edit orders</a></td>
    	<td align="center" width="33%">'.$points.'</td>
		<td align="center" width="33%"><a href="?id='.$_GET['id'].'&sub=kage&act=resign">Resign</a></td>
  		</tr>
		<tr><td align="center">&nbsp;</td><td align="center"></td>
  		<td align="center">&nbsp;</td></tr></table></div>';
	}
	
	/*
	 *								MISC KAGE FUNCTIONS
	 */
	
	//	Resign from Kage:
	
	private function resign(){
	 	switch ($this->village[0]['name']){
	 	 	case "Shinigami": $leader = "Yamamoto"; break;
	 	 	case "Hollow": $leader = "Aizen"; break;
		}
		if(strtolower($_SESSION['username']) == strtolower($this->village[0]['leader'])){
			$GLOBALS['database']->execute_query("UPDATE `races` SET `leader` = '".$leader."' WHERE `name` = '".$this->village[0]['name']."' LIMIT 1");
			$GLOBALS['database']->execute_query("UPDATE `users` SET `rank` = 'Leader' WHERE `username` = '".$leader."' LIMIT 1");
			$GLOBALS['database']->execute_query("UPDATE `users` SET `rank` = 'Ex-Leader' WHERE `username` = '".$this->village[0]['leader']."' LIMIT 1");
            
            // Update Memcache
            $GLOBALS['userdata'][0]['rank'] = "Ex-Leader";
            $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
            
			$this->output_buffer .= '<div align="center">You have resigned as the leader of the '.$this->village[0]['name'].'</div>';
		}
		else{
		 	/*	Give back original rank	*/
		 	$level_data = $GLOBALS['database']->fetch_data("SELECT * FROM `levels` WHERE `levelID` = '".$this->user[0]['level']."' LIMIT 1");
		 	$newrank = explode(':', $level_data[0]['rank']);
		    $newrank = $newrank[''.($this->user[0]['rankid']-1).''];
			$GLOBALS['database']->execute_query("UPDATE `users` SET `rank` = '".$newrank."' WHERE `username` = '".$this->user[0]['username']."' LIMIT 1");
            
            // Update Memcache
            $GLOBALS['userdata'][0]['rank'] = "".$newrank."";
            $GLOBALS['cache']->replace("resu:".$_SESSION['uid'],$GLOBALS['userdata'],false,43200);
			
			/*	Update race	*/
			$newstring = str_replace("".$this->user[0]['username']."-".$this->user[0]['id']."","-",$this->village[0]['highlevels']);
			$query17 = "UPDATE `races` SET `highlevels` = '".$newstring."' WHERE `name` = '".$this->user[0]['race']."'";
			$GLOBALS['database']->execute_query($query17);
			
			$this->output_buffer .= '<div align="center">You have resigned as commander for the '.$this->village[0]['name'].'</div>';
		}

	}
	
	//	Edit kage orders:
	
	private function edit_orders(){
	 	$orders = explode("|||:::|||:::|||;;;",$this->village[0]['orders']);
   	    if(strstr($this->user[0]['rank'],"Captain") || strstr($this->user[0]['rank'],"Espada")){
			$captid = explode(" ",$this->user[0]['rank']);
			$captid = $captid[1];
		}
		else{
			$captid = 0;
		}
	 	$text = $orders[''.$captid.''];
		$this->output_buffer .= '<div align="center"><br /><form name="form1" method="post" action="">
		<table width="95%" class="table" border="0" cellspacing="0" cellpadding="0"><tr>
    	<td width="134%" style="text-align:center;border-top:none;" class="subHeader">Edit orders</td></tr>
  		<tr><td align="center" ><textarea name="orders" rows="15" style="width:100%;">'.functions::mod_contents(stripcslashes($text)).'</textarea></td></tr>
  		<tr><td align="center" style="padding:5px;"><input type="submit" name="Submit" value="Submit"></td></tr>
		</table></form></div>';
	}
	
	private function do_edit(){
		if(isset($_POST['orders']) && functions::store_content($_POST['orders']) != ''){
		 	$orders = explode("|||:::|||:::|||;;;",$this->village[0]['orders']);
		 	if(strstr($this->user[0]['rank'],"Captain") || strstr($this->user[0]['rank'],"Espada")){
				$captid = explode(" ",$this->user[0]['rank']);
				$captid = $captid[1];
			}
			else{
				$captid = 0;
			}
			/*	New script */
			$orders[''.$captid.''] = $_POST['orders'];
			$i = 0;
			foreach ($orders as &$value) {
			    $neworders .= " ".$orders[''.$i.'']." |||:::|||:::|||;;;";
			    $i++;
			}
			
			$GLOBALS['database']->execute_query("UPDATE `races` SET `orders` = '".functions::store_content($neworders)."' WHERE `name` = '".$this->village[0]['name']."'");
			$this->output_buffer .= '<div align="center">Your orders have been updated.</div>';
		}
		else{
			$this->output_buffer .= '<div align="center">No orders were submitted</div>';
		}
	}
	
    /*
     *                          Spend village points
     */
     
     //     Village point menu:
     private function villagePoint_menu(){
        //      Calculate regen price
        $villagers = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) AS `count` FROM `users` WHERE `race` = '".$this->village[0]['name']."'");
        $regenprice = floor(sqrt($villagers[0]['count']) * 5) * 100;
        $this->output_buffer .= '<div align="center">
        <form name="form1" method="post" action="">
          <table width="90%" border="0" cellspacing="0" cellpadding="0" class="table">
            <tr>
              <td colspan="3" align="center" class="subHeader" style="border-top:none;">Spend village funds</td>
            </tr>
            <tr>
              <td colspan="3" align="center" style="border-bottom:1px solid 1px;color:darkred;font-weight:bold;">Current funds: '.$this->village[0]['points'].'</td>
            </tr>
            <tr style="font-weight:bold;">
              <td width="33%" style="padding-left:5px;">Name</td>
              <td width="33%">Price</td>
              <td width="33%">&nbsp;</td>
            </tr>
            <tr>
               <td style="padding-left:5px;">Regeneration increase</td>
               <td>'.$regenprice.'</td>
               <td><input type="radio" name="radio" id="radio" value="regen"></td>
            </tr>';

        $this->output_buffer .= '<tr>
              <td colspan="3"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td style="padding:5px;font-weight:bold;" align="center">
                   <input type="submit" name="Submit" id="button" value="Submit">
                  </td>
                </tr>
              </table></td>
            </tr>
          </table>
        </form>
        </div>';
     }
     
     //     Spend village points
     private function villagePoint_spend(){
         if(isset($_POST['radio'])){
             if($_POST['radio'] == 'regen'){
                 $villagers = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) AS `count` FROM `users` WHERE `race` = '".$this->village[0]['name']."'");
                 $regenprice = floor(sqrt($villagers[0]['count']) * 5) * 100;
                 if($this->village[0]['points'] >= $regenprice){
                     $GLOBALS['database']->execute_query("UPDATE `race_vars` SET `regen` = `regen` + 1 WHERE `name` = '".$this->village[0]['name']."' LIMIT 1");
                     $GLOBALS['database']->execute_query("UPDATE `races` SET `points` = `points` - '".$regenprice."' WHERE `name` = '".$this->village[0]['name']."' LIMIT 1");
                     $this->output_buffer .= '<div align="center">The regeneration rate of your race has increased by 1</div>';
                 }
                 else{
                    $this->output_buffer .= '<div align="center">Your race does not have enough funds.</div>';
                 }
             }
         }
         else{
            $this->output_buffer .= '<div align="center">An invalid option was submitted</div>';
         }
     }
    
	
}

?>