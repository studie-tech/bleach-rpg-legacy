<?php
class gamestart{

    function gamestart(){ 
        //		Page generation
        $mtime = microtime();
        $mtime = explode(" ",$mtime);
        $mtime = $mtime[1] + $mtime[0];
        $starttime = $mtime;
        
        // Set the include path properly for PHPIDS and include
        include('./global_libs/IDS/Init.php');
        set_include_path(
            get_include_path()
            . PATH_SEPARATOR
            . './global_libs/'
        );
        $request = array(
          'GET' => $_GET,
          'POST' => $_POST
        );
        $init = IDS_Init::init('./global_libs/IDS/Config/Config.ini.php');
        $ids = new IDS_Monitor($request, $init);

        // Run IDS
        $result = $ids->run();
        $impact = $result->getImpact();

        if( $impact <= 7 ){ 
            
            /*
             *		Instantiate main Classes
             */
            include('./global_libs/static.inc.php'); 
            include('./global_libs/page.class.php');
            $GLOBALS['page'] = new page;
            include('./global_libs/error.class.php');
            $GLOBALS['error'] = new error;
            include('./global_libs/database.class.php');
            $GLOBALS['database'] = new database;
            include('./global_libs/menu.class.php');
            $GLOBALS['menu'] = new menu;
            $GLOBALS['database']->queries = 0;  
            
            /*      Hook up with Memcache server    */
            //$GLOBALS['cache'] = new Memcache;
            //$server_hostname = '173.203.108.8';
            //$GLOBALS['cache']->connect($server_hostname, 11211) or die ("Could not connect to MemCache Server");
            
            /*      Hook up with Memcache server    */
            $GLOBALS['cache2'] = new Memcache;
            $server_hostname = '173.203.109.251';
            $GLOBALS['cache2']->connect($server_hostname, 11211) or die ("Could not connect to MemCache Server");

            $GLOBALS['cache'] = $GLOBALS['cache2'];                              
            
            /*    Get main user data, and create user class                */    
            session_start();   
            if(isset($_SESSION['uid'])){
                $GLOBALS['userdata'] = functions::get_user($_SESSION['uid']);
                //print_r($GLOBALS['userdata']);
            }
            include('./global_libs/user.class.php');
            $GLOBALS['user'] = new user(); 
            $GLOBALS['user']->setData($GLOBALS['userdata']);
            $GLOBALS['user']->parse(); 
          
            /*
             *		End of class include
             *
             *		Begin page processing
             */
            if(isset($_SESSION['layout'])){
                $GLOBALS['page']->parse_layout('./files/layout_'.$_SESSION['layout'].'/layout.html');
            }
            else{
                $GLOBALS['page']->parse_layout('./files/layout_default/layout.html');
            }
             
            
            $GLOBALS['page']->load_content($GLOBALS['userdata'],$ally);
                      
            if(isset($_SESSION['uid'])){
                //    Set messages
                $GLOBALS['page']->insert_page_data('[MSG]',functions::check_messages($GLOBALS['userdata']));
                //    Set menu
                $GLOBALS['menu']->construct_menu($GLOBALS['userdata']);
                $GLOBALS['page']->insert_page_data('[MENU]',$GLOBALS['menu']->return_menu());
                //    Set options
                $GLOBALS['page']->insert_page_data('[OPTIONS]',functions::set_options());
            }                            
          
            
            //       Close the connection to memcache
            $GLOBALS['cache']->close();
            
            //       Return Page
            $GLOBALS['page']->return_page();
                  
            //        Parsetime
            $mtime = microtime();
            $mtime = explode(" ",$mtime);
            $mtime = $mtime[1] + $mtime[0];
            $endtime = $mtime;
            $totaltime = ($endtime - $starttime);
            $totaltime = round($totaltime, 4);
            $GLOBALS['database']->close_connection();
            //echo '<font style="font-size:11px">:'.$totaltime.' seconds with '.$GLOBALS['database']->queries.' queries:</font>';
        }
        else{
            include('./global_libs/page.class.php');
            $GLOBALS['page'] = new page;
            
            if(isset($_SESSION['layout'])){
                $GLOBALS['page']->parse_layout('./files/layout_'.$_SESSION['layout'].'/layout.html');
            }
            else{
                $GLOBALS['page']->parse_layout('./files/layout_default/layout.html');
            }
            if(isset($_SESSION['uid'])){
                //    Set messages
                
                $GLOBALS['page']->insert_page_data('[MSG]',functions::check_messages($GLOBALS['userdata']));
                //    Set menu
                $GLOBALS['page']->insert_page_data('[MENU]',$GLOBALS['menu']->return_menu());
                
                //    Set options
                $GLOBALS['page']->insert_page_data('[OPTIONS]',functions::set_options());
            } 
            
            $GLOBALS['page']->insert_page_data('[AUTO]',"<a href='?id=1'>Return</a>");
            $GLOBALS['page']->insert_page_data('[CONTENT]',"The system has detected actions on your account which are suspicious. 
               These actions have now been logged, and will be reviewed as soon as possible. <br /><br />
               The security system is composed of an aggressive algorithm that analyses all user input information, 
               e.g. messages, message titles, nindos etc. If you did not intentionally attempt to compromise our site, 
               please disregard this message and re-attempt your previous action. Please note that extensive usage of 
               symbols etc. in your input may cause the system to flag it as suspicious again.");
               
            $GLOBALS['page']->return_page();
            
        }
    }
}

$start = &new gamestart();    

?>