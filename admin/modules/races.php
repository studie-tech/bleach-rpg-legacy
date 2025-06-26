<?php
/*
 *				User administration
 *		Remove, modify, and search for users / characters
 */
class users{
	private $output_buffer;
	
	//	Constructor
	public function users(){
		if(!isset($_GET['act'])){
			$this->main_page();
		}
		elseif($_GET['act'] == 'mod'){
            if(isset($_GET['type'])){
                //      Edit chardata
                if(!isset($_POST['Submit'])){
                    $this->edit_race_form();
                }
                else{
                    $this->do_edit_race();
                }
            }
		}
		$this->return_stream();
	}
	
	//	Return stream
	private function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	//	Main page
	
	private function main_page(){
		$this->output_buffer .= '<table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
  		<tr><td style="text-align:center;width:100%;" colspan="3" class="mini_header">::Races admin:: </td>
  		</tr><tr>
    	<td style="width:33%;" align="center"><a href="?id='.$_GET['id'].'&act=mod&type=Shinigami">Shinigami</a></td>
    	<td style="width:33%;" align="center"><a href="?id='.$_GET['id'].'&act=mod&type=Hollow">Hollows</a></td>
  		</tr><tr><td colspan="3" align="center">&nbsp;</td></tr></table>';
	}
	
	
    /*
     *                  EDIT USER FUNCTIONS  
     */
     
    /*
     *          Edit main user stats
     */
     
    private function edit_race_form(){
        if(isset($_GET['type'])){
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `races` WHERE `name` = '".$_GET['type']."' LIMIT 1");
            if($data != '0 rows'){
                $this->output_buffer .= functions::parse_form('races','Edit Race',array('rankid','registration_choice','type','name'),$data);
            }
            else{
                $this->output_buffer .= '<div align="center">This race does not exist?<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
            }
        }
        else{
            $this->output_buffer .= '<div align="center">No race was specified.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
        }
    }
    
    private function do_edit_race(){
        if(isset($_GET['type'])){
            $changed = functions::check_data('races','name',$_GET['type'],array('rankid','registration_choice','type','name'));
            if(functions::update_data('races','name',$_GET['type'])){
                $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('".time()."', '".$_SESSION['username']."', '".$_GET['type']."', 'Races stats updated:<br /> ".$changed."', '".$_SERVER['REMOTE_ADDR']."');");
                $this->output_buffer .= 'The race has been updated <br /><a href="?id='.$_GET['id'].'">Return to user CP</a>';
            }
            else{
                $this->output_buffer .= 'An error occured while updating the race <br /><a href="?id='.$_GET['id'].'">Return to user CP</a>';
            }
        }
    }
        

    
}
$users = new users();
?>