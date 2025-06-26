<?php
class optimize{
    private $output_buffer;
    
    public function __construct(){
        if(!isset($_POST['Submit'])){
            $this->purge_form();
        }
        else{
            $this->do_optimize();
        }
        $this->return_stream();    
    }
    
    function return_stream(){
        $GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
    }
    
    private function purge_form(){
        $this->output_buffer .= '<form id="form1" name="form1" method="post" action="">
          <div align="center">
          <table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td width="548" align="center" class="mini_header">Optimize tables</td>
            </tr>
            <tr>
              <td align="center" style="padding:4px;"><input name="Submit" type="submit" class="button" id="Submit" value="Optimize tables" /></td>
            </tr>
          </table>
        </div>
        </form>';    
    }
    
    private function do_optimize(){
     	/* Optimize link - update at last*/
        $data = $GLOBALS['database']->fetch_data("OPTIMIZE TABLE  `admin_edits` ,  `admin_notes` ,  
        `battle_options` ,  `event_options` ,  `items` ,  `lead_notifications` ,  `levels` ,  
        `moderator_log` ,  `pages` ,  `pass_request` ,  `races` ,  `race_vars` ,  `referals` ,  
        `site_information` ,  `tavern` ,  `unlock` ,  `users` , `users_events` ,  `users_inventory` ,  
        `users_pm` ,  `users_pm_settings` ,  `users_timer` ,  `user_reports`");
        $this->output_buffer .= '<div align="center">The tables have been optimized</div>';
    }
}
$optimize = new optimize();
?>
