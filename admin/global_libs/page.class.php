<?php
/*------------------------------------------------------*/
/*						Page Parser Class				*/
/*------------------------------------------------------*/
class page{
	var $modules; //	Admin modules for including
	var $page_buffer;
	var $page_key_buffer;
	var $key_no;
	var $visible_content;
		
	function page(){
		$this->page_buffer = 'empty page';
		$this->key_no = 0;
		$this->visible_content = true;
	}
	
	function insert_page_data($keyword,$insert_data){
		//	places keyword in page_key_buffer with data parsed externally, keywords are replaced from
		//	layout.html later on.
		//	Used for content / menu includer
		$this->page_key_buffer[$this->key_no][0] = $keyword;
		$this->page_key_buffer[$this->key_no][1] = $insert_data;
		$this->key_no++;
	}
	
	function content_visibility($toggle){
		if($this->visible_content != false){
			$this->visible_content = $toggle;
		}
	}
	
	function load_content(){	
		/*	Function loads page from database / includes include page
		 *	function to be adapted to circumstances, 
		 */
		if(is_numeric($_GET['id'])){
			$id = $_GET['id'];
		}
		else{
			$test = array_keys($this->modules,'notes.class.php');
			$id = $test[0];
		}
		if(file_exists('./modules/'.$this->modules[$id])){
			//	File found, include it
			include('./modules/'.$this->modules[$id]);
		}
		else{
			echo 'file not found';
		}
		return $buffer;
	}
	
	function load_modules(){
		$dir = './modules';
		$dirHandle = opendir($dir);
		$i = 0;
		while(false !==  ($file = readdir($dirHandle))){
			if($file != '.htaccess' && $file != '.' && $file != '..'){
				$this->modules[$i] = $file;
				$i++;
			}
		}
	}
	
	function return_page(){
		//	Compile the page from the pre-parsed elements
		$i = 0;
		while($i < count($this->page_key_buffer)){
			if($this->page_key_buffer[$i][0] != '[CONTENT]' || $this->visible_content == true){
				$this->page_buffer = str_replace($this->page_key_buffer[$i][0],$this->page_key_buffer[$i][1],$this->page_buffer);
			}
			$i++;
		}
		if($this->visible_content == false){
			$this->page_buffer = str_replace('content_visible','content_hidden',$this->page_buffer);
		}
		//	Strip all other keys	(stupid users that input keys they dont use .-.)
		if(stristr($this->page_buffer,'[ERROR]')){
			$this->page_buffer = str_replace('[ERROR]','',$this->page_buffer);
		}
		//	Echo the parsed page to screen
		echo $this->page_buffer;
	}
	
	function append_content($data,$keyword,$append_type){
		//	Find keyword instance if instantiated
		$key = -1;
		$i = 0;
		while($i < count($this->page_key_buffer)){
			if($this->page_key_buffer[$i][0] == $keyword){
				$key = $i;
	
				break;
			}
			$i++;
		}
		if($key != -1){
			if($append_type == 1){
				//	Append at start
				$this->page_key_buffer[$i][1] = $data.$this->page_key_buffer[$i][1];
			}
			else{
				//	Append at end
				$this->page_key_buffer[$i][1] .= $data;
			}
		}
		else{
			$this->insert_page_data($keyword,$data);
		}
	}
	
	public function parse_layout(){
		if($file = fopen('./files/layout.html','r')){
			if($buffer = fread($file,filesize('./files/layout.html'))){
				fclose($file);
				$this->page_buffer = $buffer;
			}
			else{
				//	Layout parse failed, fread error
				$GLOBALS['error']->handle_error('500','Unable to read layout file','8');
			}			
		}
		else{
			//	Layout parse failed;
			$GLOBALS['error']->handle_error('500','Unable to open the layout file','8');
		}
	}
	
	public function load_menu(){
		$i = 0;
		while($i < count($this->modules)){
			$name = explode('.',$this->modules[$i]);
			$menu .= '<a href="?id='.$i.'">'.str_replace('_',' ',ucfirst($name[0])).' admin</a><br />';
			$i++;
		}
        $menu .= '<br /><div align="center">
        <form id="form1" name="form1" method="post" action="?id=8&act=search">
        <table style="border:1px solid #000000;" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td colspan="1" align="center" class="mini_header">Quick Search</td>
          </tr>
          <tr>
            <td  align="left" style="padding:2px;"><input type="text" class="textfield" name="username" id="textfield" /></td>
          </tr>
          <tr>
            <td colspan="1" align="center" style="padding:2px;"><input name="Submit" type="submit" class="button" value="Search Username" /></td>
            </tr>
        </table>
        </form>
        </div>';
		$this->insert_page_data('[MENU]',$menu);
	}
	
}
?>