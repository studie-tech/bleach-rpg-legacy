<?php
/*				Administrative notes
 *		Stores notes to other administrators
 *		Might be used to generate daily reports
 */

class notes{
	private $output_buffer;	
	
	public function notes(){
        if(!isset($_GET['act'])){
        	$this->main_page();
        }
		elseif($_GET['act'] == 'new'){
	        if(!isset($_POST['Submit'])){
                $this->new_note();
            }
            else{
                $this->post_new_note();
            }
		}
		elseif($_GET['act'] == 'view' && is_numeric($_GET['nid'])){
		    $this->view_note();
		}
		elseif($_GET['act'] == 'delete' && is_numeric($_GET['nid'])){
			if(!isset($_POST['Submit'])){
                $this->validate_delete();
            }
            else{
                $this->do_delete();  
            }
		}
        elseif($_GET['act'] == 'modify' && is_numeric($_GET['nid'])){
            if(!isset($_POST['Submit'])){
                $this->modify_note();
            }
            else{
                $this->do_modify_note();   
            }
        }
		elseif($_GET['act'] == 'clear'){
			if(!isset($_POST['Submit'])){
                $this->validate_clear();
            }
            elseif($_POST['Submit'] == 'Yes'){
                $this->clear_notes();  
            }
		}
		$this->return_stream();
	}
	
	private function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	private function main_page(){
		$this->output_buffer .= '  <div align="center">
          <table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td width="100%" colspan="3" align="center" class="mini_header">Admin notes </td>
            </tr>
            <tr>
              <td colspan="3" align="center" style="color:darkred;">Here you can post notes to fellow members of the administration.</td>
            </tr>
            <tr>
              <td align="center" width="33%" style="border-top:1px solid #000000;"><a href="?act=new">New note </a></td>
              <td align="center" width="33%" style="border-top:1px solid #000000;">&nbsp;</td>
              <td align="center" width="33%" style="border-top:1px solid #000000;"><a href="?act=clear">Clear notes </a></td>
            </tr>
            <tr>
              <td colspan="3" align="center" class="sub_header">Current notes:</td>
            </tr>';
        $notes = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_notes` ORDER BY `time` DESC");
        if($notes != '0 rows'){
            $this->output_buffer .= '<tr>
              <td colspan="4" align="center"><table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:none;">
                <tr>
                  <td width="40%" style="padding-left:5px;border-bottom:1px solid #000000;">Title</td>
                  <td width="15%" style="border-bottom:1px solid #000000;">Author</td>
                  <td width="15%" style="border-bottom:1px solid #000000;">&nbsp;</td>
                  <td width="15%" style="border-bottom:1px solid #000000;">&nbsp;</td>
                  <td width="15%" style="border-bottom:1px solid #000000;">&nbsp;</td>
                </tr>';
            $i = 0;
            while($i < count($notes)){
                $this->output_buffer .= '
                <tr class="row'.(($i % 2) + 1).'">
                  <td style="padding-left:5px;">'.$notes[$i]['title'].'</td>
                  <td>'.$notes[$i]['posted_by'].'</td>
                  <td align="center"><a href="?id='.$_GET['id'].'&act=view&nid='.$notes[$i]['id'].'">View</a></td>
                  <td align="center"><a href="?id='.$_GET['id'].'&act=modify&nid='.$notes[$i]['id'].'">Modify</a></td>
                  <td align="center"><a href="?id='.$_GET['id'].'&act=delete&nid='.$notes[$i]['id'].'">Remove</a></td>
                </tr>';
                $i++;
            }
            $this->output_buffer .= '</table></td></tr>';
        }
        else{
            $this->output_buffer .= '<tr>
              <td colspan="3" align="center" style="color:darkred;">No notes found</td></tr>';
        }
        $this->output_buffer .= '<tr>
              <td colspan="3" align="center" style="border-top:1px solid #000000;">&nbsp;</td>
            </tr>
          </table>
        </div><br />';
	}

    private function new_note(){
        $this->output_buffer .= functions::parse_form('admin_notes','New note',array('id','posted_by','time'));
    }

    private function post_new_note(){
        $data['time'] = time();
        $data['posted_by'] = $_SESSION['username'];
        if(functions::insert_data('admin_notes',$data)){
            $this->output_buffer .= 'The note has been added <br /><a href="?id='.$_GET['id'].'">Return</a>';
        }
        else{
            $this->output_buffer .= 'An error occured when adding the note<br /><a href="?id='.$_GET['id'].'">Return</a>';
        }
    }
    
    private function view_note(){
        $note = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_notes` WHERE `id` = '".$_GET['nid']."' LIMIT 1");
        if($note != '0 rows'){
            $this->output_buffer .= '<div align="center">
              <table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td colspan="2" align="center" class="mini_header">'.$note[0]['title'].'</td>
                </tr>
                <tr>
                  <td align="left" style="padding-left:10px;" width="20%">Posted by:</td>
                  <td align="left" width="80%">'.$note[0]['posted_by'].'</td>
                </tr>
                <tr>
                  <td align="left" style="padding-left:10px;" width="20%">Posted at:</td>
                  <td align="left" width="80%">'.date('d-m-Y G:i:s',$note[0]['time']).'</td>
                </tr>
                <tr>
                  <td colspan="2" align="center" class="sub_header">Note content:</td>
                </tr>
                <tr>
                  <td colspan="2" align="center">'.$note[0]['text'].'</td>
                </tr>
                <tr>
                  <td colspan="2" align="center">&nbsp;</td>
                  </tr>
              </table><br />
              <a href="?id='.$_GET['id'].'">Return to main page</a>
            </div>';
        }
    }
    
    private function validate_clear(){
        $this->output_buffer = '<form id="form1" name="form1" method="post" action="">
        <table width="350" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
         <tr><td colspan="2" class="mini_header">::Delete item:: </td></tr><tr>
        <td colspan="2" align="center" style="padding:2px;">Clear all notes? </td>
          </tr><tr><td width="224" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" value="Yes" /></td>
          <td width="224" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" value="No" /></td>
          </tr></table></form>';   
    }
    
    private function clear_notes(){
        if($GLOBALS['database']->execute_query("TRUNCATE TABLE `admin_notes`")){
            $this->output_buffer .= 'All admin / mod notes have been removed<br /><a href="?id='.$_GET['id'].'">Return</a>';
        }
    }
    
    private function modify_note(){
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_notes` WHERE `id` = '".$_GET['nid']."'");
        if($data != '0 rows'){
            $this->output_buffer .= functions::parse_form('admin_notes','Update note',array('id','time','posted_by'),$data);
        }
        else{
            $this->output_buffer .= 'This note does not exist<br /><a href="?id='.$_GET['id'].'">Return</a>';
        }
    }
    
    private function do_modify_note(){
        if(functions::update_data('admin_notes','id',$_GET['nid'])){
            $this->output_buffer .= 'The note has been updated <br /><a href="?id='.$_GET['id'].'">Return</a>';
        }
        else{
            $this->output_buffer .= 'An error occured while updating the note <br /><a href="?id='.$_GET['id'].'">Return</a>';
        }
    }
    
    private function validate_delete(){
        $this->output_buffer = '<form id="form1" name="form1" method="post" action="">
        <table width="350" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
         <tr><td colspan="2" class="mini_header">::Delete item:: </td></tr><tr>
        <td colspan="2" align="center" style="padding:2px;">Delete this note?</td>
        </tr><tr><td width="224" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" value="Yes" /></td>
        <td width="224" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" value="No" /></td>
        </tr></table></form>';
    }
    
    private function do_delete(){
        if($GLOBALS['database']->execute_query("DELETE FROM `admin_notes` WHERE `id` = '".$_GET['nid']."' LIMIT 1")){
            $this->output_buffer .= 'The admin note has been deleted <br /><a href="?id='.$_GET['id'].'">Return</a>';
        }
    }
}

$notes = new notes();
?>