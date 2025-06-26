<?php
/*
 *				Global Messages
 *		Send a message to every user in the game
 */
class users{
	private $output_buffer;
	
	//	Constructor
	public function users(){
		if(!isset($_GET['act'])){
		 	if(!isset($_POST['Submit'])){
                $this->main_page();
            }
            else{
                $this->insert_message();
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
		$this->output_buffer .= '<div align="center">
                  <br /><form name="form1" method="post" action=""><table width="90%" border="0" cellspacing="0" cellpadding="0" class="table">
                    <tr>
                      <td colspan="2" align="center" style="border-top:none;" class="subHeader" >Global News Message</td>
                    </tr>
                    <tr>
                      <td align="center" colspan="2" style="color:darkred;">Use this feature sporadically and responsibly, joking around will not be tolerated.</td>
                    </tr>
                    <tr>
                      <td align="right" width="65%" style="padding-top:2px;padding-bottom:2px;"><textarea name="message" cols="30" rows="3" id="message"></textarea></td>
                      <td align="left" width="35%" style="padding-top:2px;padding-bottom:2px;padding-left:15px;"><input type="submit" name="Submit" value="Submit"></td>
                    </tr>
                  </table></form>
                </div>';  
	}
	
	private function insert_message(){
        if(
            $GLOBALS['database']->execute_query("UPDATE `event_options` SET `related_text` = '".functions::store_content($_POST['message'])."' WHERE `option` = 'news'")  
          ){
            $GLOBALS['database']->execute_query("UPDATE `users` SET `read_news` = 0 ");
            $GLOBALS['cache']->flush();         
            
            $this->output_buffer .= '<div align="center">The message has been uploaded.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
        }
        else{
            $this->output_buffer .= '<div align="center">An2 error occured uploading the message.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';   
        }
    }


    
}
$users = new users();
?>