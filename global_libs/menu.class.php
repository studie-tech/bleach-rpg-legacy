<?php
/*--------------------------------------------------------------*/
/*							Menu class file						*/
/*																*/
/*		Class file strongly associated with the page class		*/
/*			Alterations to the way pages are called will		*/
/*			Result in changes to the menu class as well			*/
/*					Editing of this class should be				*/
/*						Done by knowledgeable					*/
/*								people							*/
/*--------------------------------------------------------------*/
class menu{
	var $menu_buffer;
	
	function menu(){
		//	Menu Constructor
		$this->menu_buffer = '';
	}
    
    public function get_menu( $key , $unblocker , $rankid ){
        $data = $GLOBALS['cache2']->get("menuunem:".$key);
        if( !$data ){
            // Get From Database
            $query = "SELECT `id`,`menu_name` FROM `pages` WHERE `menu_name` != 'NO' AND ((`allow_ranks` LIKE '%".$rankid."%') $unblocker) AND `require_login` = 'yes' ";
            $data = $GLOBALS['database']->fetch_data($query);
            // Insert into cache
            $GLOBALS['cache2']->add("menuunem:".$key,  $data, false, 3600);
        }
        return $data;
    }
	
	function construct_menu($user){

		if(isset($_SESSION['uid'])){
			$this->menu_buffer .= '[COLUMN1]';	
			
			if($user[0]['race'] == "Hollow" && $user[0]['level'] > 19){
				$unlocker = " || `VastoLord_access` = 'yes'";
			}
            
            // Get some memcache going
            $key = $user[0]['rankid'].$unlocker;

			$data2 = $this->get_menu( $key , $unlocker , $user[0]['rankid']);
            
			$column1 = '';
			$i = 0;
			while($i < count($data2) && $data2 != '0 rows'){
			 	if($i !== 0){$column1 .= ' - ';}
				$column1 .= '<a class="menulink" href="?id='.$data2[$i]['id'].'">'.$data2[$i]['menu_name'].'</a>';
				$i++;
			}
			$this->menu_buffer = str_replace('[COLUMN1]',$column1,$this->menu_buffer);
	
		}
        
		$this->menu_buffer .= "<br />";
        
		if($_SESSION['user_rank'] == 'Admin'){
			$this->menu_buffer .= '<font size="-2"><a href="./admin/">Admin panel</a> - </font>';
		}
		if($_SESSION['user_rank'] == 'Moderator' || $_SESSION['user_rank'] == 'Supermod' || $_SESSION['user_rank'] == 'Admin'){
			$this->menu_buffer .= '<font size="-2"><a href="?id=16">Moderator Controls</a> - </font>';
		}
        $this->menu_buffer .= '<font size="-2"><a href="/Manual/">Manual</a> - </font>';

	}
	
	function return_menu(){
		return $this->menu_buffer;
	}
}
?>