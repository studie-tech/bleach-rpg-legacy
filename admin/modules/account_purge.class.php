<?php
class account_purge{
    private $output_buffer;
    
    private function return_stream(){
        $GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
    }
    
    public function account_purge(){
        if(!isset($_GET['act'])){
            $this->main_screen();
        }
        elseif($_GET['act'] == 'flagged'){
            if(!isset($_POST['Submit'])){
                $this->flagged_accounts();   
            }
            else{
                $this->do_purge_flagged();
            }
        }
        elseif($_GET['act'] == 'inactive'){
            if(!isset($_POST['Submit'])){
                $this->inactive_accounts();   
            }
            else{
                $this->do_purge_inactive();   
            }
        }
        $this->return_stream();
    }
    
    private function main_screen(){
        $this->output_buffer .= '<div align="center"><table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="448" colspan="2" align="center" class="mini_header">::Purge users:: </td>
        </tr>
        <tr>
          <td colspan="2" align="center" style="padding:2px;">Using this panel unactivated accounts and accounts previously flagged for deletion can be purged.</td>
        </tr>
        <tr>
          <td align="center" width="50%"><a href="?id='.$_GET['id'].'&act=inactive">Inactive accounts</a></td>
          <td align="center" width="50%"><a href="?id='.$_GET['id'].'&act=flagged">Flagged accounts</a></td>
        </tr>
        <tr>
          <td colspan="2" align="center"></td>
        </tr>
        </table></div>';   
    }

    

    private function inactive_accounts(){
        $accounts = $GLOBALS['database']->fetch_data("SELECT `username`,`id`,`join_date`,`activation`,`last_login`,`rep_ever` FROM `users` JOIN `users_timer` ON `id` = `userid` WHERE 
            (`last_login` < (UNIX_TIMESTAMP() - 3628800)
            AND `join_date` < (UNIX_TIMESTAMP() - 3628800)
            ) AND `user_rank` = 'Member'
            AND `rep_ever` < 30
            AND `user_rank` != 'Admin'
            ORDER BY `last_login` ASC");
        $account = count($accounts);
        if($account < 300){
            $now = $account;
        }
        else{
            $now = 300;
        }
        $this->output_buffer .= '<div align="center"><form id="form1" name="form1" method="post" action="">
          <table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td width="498%" colspan="4" align="center" class="mini_header">::Inactive accounts:: </td>
            </tr>
            <tr>
              <td colspan="4" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" id="button" value="Purge accounts" /></td>
            </tr>
            <tr>
              <td colspan="4" align="center" style="border-bottom:1px solid #000000;">The following '.$now.' out of '.$account.' accounts have been flagged for deletion:</td>
            </tr>';
        if($accounts != '0 rows'){
            $this->output_buffer .= '
            <tr class="row1">
              <td align="center" style="font-weight:bold;">id</td>
              <td align="center" style="font-weight:bold;">username</td>
              <td align="center" style="font-weight:bold;">join date</td>
              <td align="center" style="font-weight:bold;">last login</td>
            </tr>';
            $i = 0;
            while($i < $now){
                $this->output_buffer .= '<tr class="row1">
                  <td align="center">'.$accounts[$i]['id'].'</td>
                  <td align="center">'.$accounts[$i]['username'].'</td>
                  <td align="center">'.date('Y-m-d',$accounts[$i]['join_date']).'</td>
                  <td align="center">'.date('Y-m-d',$accounts[$i]['last_login']).'</td>
                  </tr>';
                $i++;   
            }
        }
        else{
            $this->output_buffer .= '<tr class="row0">
              <td colspan="4" align="center">There are no inactive accounts right now.</td>
            </tr>';   
        }
        $this->output_buffer .= '<tr>
              <td colspan="4" align="center" style="border-top:1px solid #000000;">&nbsp;</td>
            </tr>
          </table>
        </form></div>';
    }
    
    private function do_purge_inactive(){
        $GLOBALS['database']->execute_query("DELETE `users_timer`,`users` FROM `users_timer` INNER JOIN `users` WHERE 
                                                (`last_login` < (UNIX_TIMESTAMP() - 3628800) AND 
                                                `join_date` < (UNIX_TIMESTAMP() - 3628800)) AND
                                                `user_rank` = 'Member' AND 
                                                `rep_ever` < 30 AND 
                                                `user_rank` != 'Admin' AND 
                                                `id` = `userid`");
                                                
        $GLOBALS['database']->execute_query("DELETE users_events.*
                                            FROM `users_events`
                                            LEFT JOIN `users` ON users_events.userid = users.id
                                            WHERE users.id is null");
                                            
        $GLOBALS['database']->execute_query("DELETE users_pm_settings.*
                                            FROM `users_pm_settings`
                                            LEFT JOIN `users` ON users_pm_settings.userid = users.id
                                            WHERE users.id is null");
                                            
        $GLOBALS['database']->execute_query("DELETE users_inventory.*
                                            FROM `users_inventory`
                                            LEFT JOIN `users` ON users_inventory.uid = users.id
                                            WHERE users.id is null");
                                            
        $GLOBALS['database']->execute_query("DELETE battle_options.*
                                            FROM `battle_options`
                                            LEFT JOIN `users` ON battle_options.uid = users.id
                                            WHERE users.id is null");
        
        $this->output_buffer .= '<div align="center">Accounts have been deleted <br /><a href="?id='.$_GET['id'].'">Return</a></div>';   
        
    }
    
    private function flagged_accounts(){
        $accounts = $GLOBALS['database']->fetch_data("SELECT `username`,`id`,`deletion_timer`,`join_date` FROM `users`,`users_timer` WHERE `deletion_timer` < '".(time() - 86400)."' AND `deletion_timer` > 0 AND `id` = `userid`");
        $this->output_buffer .= '<div align="center"><form id="form1" name="form1" method="post" action="">
          <table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td width="498%" colspan="4" align="center" class="mini_header">::Accounts flagged for deletion:: </td>
            </tr>
            <tr>
              <td colspan="4" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" id="button" value="Purge accounts" /></td>
            </tr>
            <tr>
              <td colspan="4" align="center" style="border-bottom:1px solid #000000;">The following '.(count($accounts)).' accounts have been flagged for deletion:</td>
            </tr>';
        if($accounts != '0 rows'){
            $this->output_buffer .= '
            <tr class="row1">
              <td align="center" style="font-weight:bold;">id</td>
              <td align="center" style="font-weight:bold;">username</td>
              <td align="center" style="font-weight:bold;">join date</td>
              <td align="center" style="font-weight:bold;">deletion time</td>
            </tr>';
            $i = 0;
            while($i < count($accounts)){
                $this->output_buffer .= '<tr class="row1">
                  <td align="center">'.$accounts[$i]['id'].'</td>
                  <td align="center">'.$accounts[$i]['username'].'</td>
                  <td align="center">'.date('Y-m-d',$accounts[$i]['join_date']).'</td>
                  <td align="center">'.date('Y-m-d',$accounts[$i]['deletion_timer']).'</td>
                  </tr>';
                $i++;   
            }
        }
        else{
            $this->output_buffer .= '<tr class="row0">
              <td colspan="4" align="center">No accounts are flagged for deletion right now</td>
            </tr>';   
        }
        $this->output_buffer .= '<tr>
              <td colspan="4" align="center" style="border-top:1px solid #000000;">&nbsp;</td>
            </tr>
          </table>
        </form></div>';
    }
    
    private function do_purge_flagged(){
                
        $GLOBALS['database']->execute_query("DELETE `users_timer`,`users` FROM `users_timer` INNER JOIN `users` WHERE `deletion_timer` < '".(time() - 86400)."' AND `deletion_timer` > 0 AND `id` = `userid`");
        
        $GLOBALS['database']->execute_query("DELETE users_events.*
                                            FROM `users_events`
                                            LEFT JOIN `users` ON users_events.userid = users.id
                                            WHERE users.id is null");
                                            
        $GLOBALS['database']->execute_query("DELETE users_pm_settings.*
                                            FROM `users_pm_settings`
                                            LEFT JOIN `users` ON users_pm_settings.userid = users.id
                                            WHERE users.id is null");
                                            
        $GLOBALS['database']->execute_query("DELETE users_inventory.*
                                            FROM `users_inventory`
                                            LEFT JOIN `users` ON users_inventory.uid = users.id
                                            WHERE users.id is null");
                                            
        $GLOBALS['database']->execute_query("DELETE battle_options.*
                                            FROM `battle_options`
                                            LEFT JOIN `users` ON battle_options.uid = users.id
                                            WHERE users.id is null");

        $this->output_buffer .= '<div align="center">Accounts have been deleted <br /><a href="?id='.$_GET['id'].'">Return</a></div>';   
        
    }


}
$purge = new account_purge();
?>
