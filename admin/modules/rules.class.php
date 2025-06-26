<?php
/*
 *			Class file for easy modification of the game rules.
 */


class rules{
	private $output_buffer;
	private $modify_buffer;
	private $insert_buffer;
	
	public function rules(){
		if(!isset($_GET['act'])){
			$this->main_page();
		}
		elseif($_GET['act'] == 'edit_rules'){
			if(!isset($_POST['Submit'])){
				$this->edit_rules_form();
			}
			else{
				$this->upload_rules_edit();
			}
		}
		elseif($_GET['act'] == 'edit_terms'){
			if(!isset($_POST['Submit'])){
				$this->edit_terms_form();
			}
			else{
				$this->upload_terms_edit();
			}
		}
		$this->return_stream();
	}
	
	private function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	private function main_page(){
		$this->output_buffer .= '<div align="center">
		<table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
  		<tr><td colspan="3" align="center" class="mini_header">Rules</td></tr><tr>
    	<td colspan="3" align="center" style="border-top:1px solid #000000;">Modify the rules and terms of service </td>
  		</tr><tr>
    	<td width="44%" align="center" style="border-top:1px solid #000000;"><a href="?id='.$_GET['id'].'&act=edit_rules">Modify the rules</a></td>
    	<td width="12%" align="center" style="border-top:1px solid #000000;">&nbsp;</td>
    	<td width="44%" align="center" style="border-top:1px solid #000000;"><a href="?id='.$_GET['id'].'&act=edit_terms">Edit the terms of service</a></td>
  		</tr><tr>
    	<td colspan="3" align="center" style="border-top:1px solid #000000;">&nbsp;</td>
    	</tr></table></div>';
	}
	
	//	Edit the rules
	private function edit_rules_form(){
		if($this->get_rules()){
			$this->output_buffer .= '<div align="center">
			<form id="form1" name="form1" method="post" action="">
			<table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
  			<tr><td width="100%" align="center" class="mini_header">Modify the rules</td>
  			</tr><tr><td align="center" style="border-top:1px solid #000000;height:5px;"></td>
    		</tr><tr><td align="center" style="border-top:1px solid #000000;"><textarea name="rules" rows="30" class="textfield" style="width:100%;">
    		'.$this->modify_buffer.'</textarea></td>
  			</tr><tr><td align="center" style="border-top:1px solid #000000;padding-top:2px;padding-bottom:2px;"><input name="Submit" type="submit" class="button" value="Submit" /></td>
    		</tr></table></form></div>';
		}
	}
	
	//	Update rule modification to database.
	private function upload_rules_edit(){
		if(isset($_POST['rules']) && strlen($_POST['rules']) > 0){
			$this->insert_buffer = $_POST['rules'];
			if($this->save_rules()){
				$this->output_buffer .= '<div align="center">The rules have been updated.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
			else{
				$this->output_buffer .= '<div align="center">An error occured while uploading the new rules to file.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
		}
		else{
			$this->output_buffer .= '<div align="center">An error occured: you did not specify any rules.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
	//	Retrieves rules from file
	private function get_rules(){
		if(is_file('../files/rules.inc')){
			$fp = fopen('../files/rules.inc','r');
			$this->modify_buffer = stripslashes(fread($fp,filesize('../files/rules.inc')));
			fclose($fp);
			return 1;
		}
		else{
			$this->output_buffer .= '<div align="center">An error occured while trying to load the rules<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			return 0;
		}
	}
	
	//	Saves rules to file
	private function save_rules(){
		$fp = fopen('../files/rules.inc','w');
		if(fwrite($fp,$this->insert_buffer)){
			return 1;
		}
		fclose($fp);
		return 0;
	}
	
	
	//	Edit the terms
	private function edit_terms_form(){
		if($this->get_terms()){
			$this->output_buffer .= '<div align="center">
			<form id="form1" name="form1" method="post" action="">
			<table width="550" style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
  			<tr><td width="100%" align="center" class="mini_header">Modify the Terms</td>
  			</tr><tr><td align="center" style="border-top:1px solid #000000;height:5px;"></td>
    		</tr><tr><td align="center" style="border-top:1px solid #000000;"><textarea name="terms" rows="30" class="textfield" style="width:100%;">
    		'.$this->modify_buffer.'</textarea></td>
  			</tr><tr><td align="center" style="border-top:1px solid #000000;padding-top:2px;padding-bottom:2px;"><input name="Submit" type="submit" class="button" value="Submit" /></td>
    		</tr></table></form></div>';
		}
	}
	
	//	Update rule modification to database.
	private function upload_terms_edit(){
		if(isset($_POST['terms']) && strlen($_POST['terms']) > 0){
			$this->insert_buffer = $_POST['terms'];
			if($this->save_terms()){
				$this->output_buffer .= '<div align="center">The terms have been updated.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
			else{
				$this->output_buffer .= '<div align="center">An error occured while uploading the new terms to file.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			}
		}
		else{
			$this->output_buffer .= '<div align="center">An error occured: you did not specify any terms.<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
		}
	}
	
	//	Retrieves rules from file
	private function get_terms(){
		if(is_file('../files/terms.inc')){
			$fp = fopen('../files/terms.inc','r');
			$this->modify_buffer = stripslashes(fread($fp,filesize('../files/terms.inc')));
			fclose($fp);
			return 1;
		}
		else{
			$this->output_buffer .= '<div align="center">An error occured while trying to load the terms<br /><a href="?id='.$_GET['id'].'">Return</a></div>';
			return 0;
		}
	}
	
	//	Saves rules to file
	private function save_terms(){
		$fp = fopen('../files/terms.inc','w');
		if(fwrite($fp,$this->insert_buffer)){
			return 1;
		}
		fclose($fp);
		return 0;
	}
}

$rules = new rules();
?>