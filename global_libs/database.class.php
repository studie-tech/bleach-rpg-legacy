<?php
/*--------------------------------------------------*/
/*				MySQL Database Class				*/
/*--------------------------------------------------*/
class database{
	var $MYSQL_SERVER = '';
    var $MYSQL_USER = '';
    var $MYSQL_PASS = '';
    var $MYSQL_DEFAULT_DB = '';
    

    public $last_affected_rows = 'none';
    
	function database($mysql_database = NULL){
		//	Default Constructor utilizes connect() and select_database()
		if($this->connect()){
			if($mysql_database != NULL){
				//	Select DB Passed along in constructor args
				$this->select_database($mysql_database);
			}
			else{
				//	Select Default DB as stated in class defaults
				$this->select_database($this->MYSQL_DEFAULT_DB);
			}
		}
		else{
			echo 'Cannot connect to MySQL';		//Change this to decent error message later
			die();
		}
	}
	
	function connect(){
		// Function Connects to the MySQL server, with the login details specified above
		if($this->link = mysql_connect($this->MYSQL_SERVER,$this->MYSQL_USER,$this->MYSQL_PASS)){
			return 1;
		}
		else{
			return 0;
		}
	}
	
	function select_database($database){
		// Selects the database passed along in the function args, can be used to switch databases mid-script if required;
		mysql_select_db($database);
	}
	
	function execute_query($query_string){
        
        $GLOBALS['database']->queries++;
		/*	Executes Query passed along in the function args, returns either 0, or the resultID returned by MySQL;
		 *	WARNING: use only for INSERT / DELETE queries without the purpose of data retrieval
		 * 	for data retrieval, use fetch_data to keep plugins database independant
		 */
         
        if(!stristr($query_string,"--") || !stristr($query_string,"#")){
            $query_string = str_replace("--","",$query_string);
            $query_string = str_replace("#","",$query_string);
		    if($query = mysql_query($query_string)){
                $this->last_affected_rows = mysql_affected_rows();
			    if(mysql_affected_rows() > -1 && !stristr($query_string,"UPDATE")){
				    return $query;
			    }
                elseif(stristr($query_string,"UPDATE")){
                    if($this->last_affected_rows >= 1){
                        return $query;   
                    }
                    else{
                        return false;   
                    }
                }
                else{
                    return false;   
                }
                /*
                elseif(stristr($query_string,"UPDATE") && mysql_affected_rows > 0){
                    return $query;   
                }
			    else{
                    if(stristr($query_string,"UPDATE")){   
                        echo $query_string.' ERROR '.mysql_affected_rows().' <br />';
                    }
				    return false;
			    }*/
		    }
		    else{
                echo mysql_error();
			    if(is_dir('./logs/')){
				    if($fp = fopen('./logs/sqlerror_'.date('Y-m-d').'.log','a')){
					    fwrite($fp,date("G:i:s").":".$_GET['id'].":".$_SESSION['username'].":".$query_string.":".mysql_error()."\r\n");
					    fclose($fp);
				    }
				    else{

				    }
			    }
			    else{

			    }
			    return false;
		    }
        }
        else{
            return false;   
        }
	}
    
    function logQuery($query_string){
        if(is_dir('./logs/')){
            if($fp = fopen('./logs/sql_log_'.date('Y-m-d').'.log','a')){
                fwrite($fp,date("G:i:s")." - ".$_SERVER['REQUEST_URI']." - ".$_SERVER['REQUEST_URI']." - ".$_SESSION['username'].":".$query_string.":\r\n");
                fclose($fp);
            }
            else{

            }
        }
        else{

        }
    }
	
	function fetch_data($query_string){
		/*	Fetches data from the database, as prescribed by the query passed in the function args;
		 *	For insert / delete queries, use execute_query;
		 */
		if($queryID = $this->execute_query($query_string)){
			if(mysql_num_rows($queryID) > 0){
				$i = 0;
				while($data = mysql_fetch_array($queryID,MYSQL_ASSOC)){
					$return_data[$i] = $data;
					$i++;
				}
				return $return_data;
			}
			elseif(mysql_num_rows($queryID) == 0){
				return '0 rows';
			}
		}
		else{
			return 0;	
		}
	}

	function get_inserted_id(){
		return mysql_insert_id();
	}

    public function close_connection(){
        mysql_close($this->link);   
    }
}

?>