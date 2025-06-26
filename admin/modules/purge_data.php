<?php
/*
 *          Purges old tavern messages, news comments, and battles
 *      Shortest admin script ever! Booyah?
 */
class purge_tavern{
    private $output_buffer;
    
    
    //  Constructor
    public function __construct(){
        if(isset($_POST['Submit'])){
            if($_POST['Submit'] == 'Purge taverns'){
                $this->purge_taverns();
            }
            elseif($_POST['Submit'] == 'Purge battles'){
                $this->purge_battles();
            }
            elseif($_POST['Submit'] == 'Purge news comments'){
                $this->purge_news();
            }
            elseif($_POST['Submit'] == 'Purge PM'){
                $this->purge_PM();
            }
        }
        else{
            $this->purge_form();
        }
        $this->return_stream();    
    }
    
    function return_stream(){
        $GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
    }
    
    private function purge_form(){
        $this->output_buffer .= '<form id="form1" name="form1" method="post" action="">
          <div align="center">
          <table width="500" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td colspan="4" align="center" class="mini_header">Purge options</td>
            </tr>
            <tr>
              <td align="center" style="padding:4px;"><input name="Submit" type="submit" class="button" id="Submit" value="Purge taverns" /></td>
              <td align="center" style="padding:4px;"><input name="Submit" type="submit" class="button" id="Submit" value="Purge PM" /></td>
            </tr>
          </table>
        </div>
        </form>';    
    }

    
    private  function purge_PM(){
        $GLOBALS['database']->execute_query("DELETE FROM `users_pm` WHERE `time` < '".(time() - 2678400)."'");
        $GLOBALS['database']->execute_query("OPTIMIZE TABLE `users_pm`");
        $this->output_buffer .= '<div align="center">'.$GLOBALS['database']->last_affected_rows.' PM\'s have been purged.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
    }
    
    // purge tavern
    private function purge_taverns(){
        if($_POST['Submit'] == 'Purge taverns'){
            $villages = $GLOBALS['database']->fetch_data("SELECT `name` FROM `races`");
            $GLOBALS['database']->execute_query("TRUNCATE TABLE `tavern`");
            $GLOBALS['database']->execute_query("OPTIMIZE TABLE `tavern`");
            
            // Purge Cache
            $GLOBALS['cache2']->delete("nrevat:1");
            $GLOBALS['cache2']->delete("nrevat:2");
            
            
            $this->output_buffer .= '<div align="center">Taverns have been purged of old messages.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
        }
        else{
            $this->purge_form();   
        }
    }
    

}

$purge = new purge_tavern();
?>
