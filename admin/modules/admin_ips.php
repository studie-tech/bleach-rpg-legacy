<?php

class ips{
	var $output_buffer;
	
	//		Standard functions, constructor + return stream
	function ips(){
		if(!isset($_POST['Submit'])){
            //    Form
            $this->update_form();
        }
        else{
            //    Update news item:
            $this->update_list();
        }
		$this->return_stream();
	}
	
	function return_stream(){
		$GLOBALS['page']->insert_page_data('[CONTENT]',$this->output_buffer);
	}
	
	//		Update news item:
	function update_form(){
		$data = $GLOBALS['database']->fetch_data("SELECT * FROM `site_information` WHERE `option` = 'admin_ips'");
		if($data != '0 rows'){
			$this->output_buffer .= functions::parse_form('site_information','Update Admin IP list',array('id'),$data);
		}
		else{
			$this->output_buffer .= 'No admin IP list exists<br /><a href="?id='.$_GET['id'].'">Return</a>';
		}
	}
	
	function update_list(){
		if(functions::update_data('site_information','option','admin_ips')){
			$this->output_buffer .= 'The list has been updated <br /><a href="?id='.$_GET['id'].'">Return</a>';
		}
		else{
			$this->output_buffer .= 'An error occured while updating the list <br /><a href="?id='.$_GET['id'].'">Return</a>';
		}
	}

}

$news = new ips();

?>