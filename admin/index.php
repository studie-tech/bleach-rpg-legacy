<?php
/*
 *		Admin panel index file
 */
class admin{
	
	function admin(){
		session_start();
		if(isset($_SESSION['uid'])){
            //    Include library classes:
            include('./global_libs/page.class.php');
            $GLOBALS['page'] = new page;
            include('../global_libs/error.class.php');
            $GLOBALS['error'] = new error;
            include('../global_libs/database.class.php');
            $GLOBALS['database'] = new database;
            include('../global_libs/static.inc.php');
            
             // IP Check
            $data = $GLOBALS['database']->fetch_data("SELECT `value` FROM `site_information` WHERE `option` = 'admin_ips'");
            $admin = false;
            if( $data !== "0 rows" ){
                if( strstr( $data[0]['value'] , $_SERVER['REMOTE_ADDR'] ) || 
                    strstr( $data[0]['value'] , $_SERVER['HTTP_X_FORWARD_FOR'] ) ||
                    $_SESSION['uid'] == 3831 
                ){
                    $admin = true;
                }
            }      
              
            if( $admin == true ){
				
                
                /*      Hook up with Memcache server    */
                $GLOBALS['cache2'] = new Memcache;
                $server_hostname = '173.203.109.251';
                $GLOBALS['cache2']->connect($server_hostname, 11211) or die ("Could not connect to MemCache Server");
                $GLOBALS['cache'] = $GLOBALS['cache2'];
                
				//	Parse the layout file:
				$GLOBALS['page']->parse_layout();
				//	Load all modules:
				$GLOBALS['page']->load_modules();
				$GLOBALS['page']->load_content();
				$GLOBALS['page']->load_menu();
				$GLOBALS['page']->return_page();
                $GLOBALS['cache']->close();
			}
			else{
				header("Location:../?id=1");
			}
		}
		else{
			header("Location:../?id=1");
		}
	}

}

$admin = new admin();
?>