<?php
/*------------------------------------------------------*/
/*						Error class						*/
/*				System Error-handling class				*/
/*------------------------------------------------------*/
class error{
	//	SETTINGS:
	var $CRITICAL_LEVEL = 8;					// error level at which the script will halt completely
	var $LOG_ERROR = 1;							//	0 = dont log, 1 = log.
	var $LOG_ERROR_LEVEL = 1;					//	Minimum error level the script will log 1 = all
	var $LOG_TYPE = 'FILE'; 				//	Set to either DATABASE or FILE
	var $LOG_FILE_PATH = './logs';				//	PATH to log files, used only with FILE directive in LOG_TYPE
	var $LOG_FILE_NAME = 'Error [W-Y].log';		//	Error log filename (date syntax between []);
	var $ERROR_OVERRIDE_CONTENT = 'default';	//	Error overrides content values: default (leave it up to the error handler), force (forces use of value below), never (never force content override, unadvised)
	var $ERROR_OVERRIDE_CONTENT_LEVEL = 5;		//	Error level at which the content screen will be disabled by default
	
	var $error_buffer;
	var $error_num;
	
	 function error(){
		//	Default error instance constructor
		$this->error_num = 0;
		$this->error_buffer = '';
	}
	
	 function setError($errordata){
		//	Sets error in the error buffer
		$this->error_buffer[$this->error_num] = $errordata;
		$this->error_num++;
	}		//DEPRECATED
	
	 function display_critical($errorno,$errormsg,$errorlvl){
	 	$file = fopen('./templates/critical_error.tpl','r');
		$file_data = fread($file,filesize('./templates/critical_error.tpl'));
		$file_data = explode('-->',$file_data);
		fclose($file);
		//	Parse template
		$output_data = str_replace('[ERRORNO]',$errorno,$file_data[1]);
		$output_data = str_replace('[ERRORMSG]',$errormsg,$output_data);
		$output_data = str_replace('[ERRORLVL]',$errorlvl,$output_data);
		echo $output_data;
		die();
	}
	
	 function log_error($errorno,$errormsg,$errorlvl){
		if($this->LOG_ERROR == 1){
			//	Log Error:
			if($this->LOG_TYPE == 'DATABASE'){
				//	Log error in database:
				if(is_object($GLOBALS['database'])){
					if($query = $GLOBALS['database']->execute_query("INSERT INTO `error_log` ( `id` , `errorno` , `errorlvl` , `errormsg`, `time` , `request_uri` , `ip` )VALUES (NULL , '".$errorno."', '".$errorlvl."', '".$errormsg."', '".time()."','".$_SERVER['REQUEST_URI']."', '".$_SERVER['REMOTE_ADDR']."')")){
						//	Row inserted into the table, error logged
						return true;
					}
					else{
						//	Failure in executing insert query, error not inserted!
						return false;
					}
				}
				else{
					//	FAILURE, database object not instantiated, cannot communicate with database!
					return false;
				}
			}
			elseif($this->LOG_TYPE == 'FILE'){
				//	Log error to file:
				//	Parse filename:
				$temp_filename = explode('[',$this->LOG_FILE_NAME);
				$filename = $temp_filename[0];
				$temp_filename = explode(']',$temp_filename[1]);
				$filename .= date($temp_filename[0]);
				$filename .= $temp_filename[1];
				//	Parse log data:
				$errordata = date('N G:i:s').' - '.$errorlvl.' - '.$errorno.' - '.$errormsg.' - '.$_SERVER['REQUEST_URI'].' - '.$_SERVER['REMOTE_ADDR']."\r\n";
				//	Make directory if not exists
				if(!is_dir($this->LOG_FILE_PATH)){
					if(!mkdir($this->LOG_FILE_PATH)){
						return false;
					}
				}
				//	Open log file
				if($file = fopen($this->LOG_FILE_PATH.'/'.$filename,'a')){
					//	Write to log file
					fwrite($file,$errordata);
				}
				else{
					return false;
				}
				return true;
			}
		}
		elseif($this->LOG_ERROR == 0){
			//	Errors not logged returns true because it was intended
			return true;
		}
		else{
			//	Invalid LOG_ERROR setting:
			return false;
		}
	}
	
    function captchaRequire($msg,$content = false){
     
        require_once('recaptchalib.php');
        $publickey = "6LcxU8YSAAAAABpfRNi93qX3PmFvpYMJcN5Qdbdp";

        // Include all other current POST variables
        $loginInfo = "";
        foreach($_POST as $key => $val){
            if( $key !== "recaptcha_challenge_field" && $key !== "recaptcha_response_field" ){
                $loginInfo .= "<input type='hidden' name='".$key."' value='".$val."'></input>";
            }
        }        
        
        $file = fopen('./templates/captcha.tpl','r');
        $file_data = fread($file,filesize('./templates/captcha.tpl'));
        $file_data = explode('-->',$file_data);
        fclose($file);
        //    Parse template
        $output_data = str_replace('[msg]',$msg,$file_data[1]);
        $output_data = str_replace('[reCaptcha]',recaptcha_get_html($publickey),$output_data);
        $output_data = str_replace('[loginInfo]',$loginInfo,$output_data);

        $GLOBALS['page']->append_content($output_data,'[ERROR]',1);
        
        $GLOBALS['page']->content_visibility($content);
             
    }
    
    function checkCaptcha(){
        require_once('recaptchalib.php');
        $privatekey = "6LcxU8YSAAAAAIp956uszQ1mozfnH2uKaR8FyOMA";
        $resp = recaptcha_check_answer ($privatekey,
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["recaptcha_challenge_field"],
                                        $_POST["recaptcha_response_field"]);
        
        // true if correct, false if incorrect
        return $resp->is_valid;
    }
    
	function handle_error($errorno,$errormsg,$errorlvl,$content = false){
		//	Log error if errorlvl > LOG_ERROR_LEVEL
		if($this->LOG_ERROR_LEVEL < $errorlvl){
			if(!$this->log_error($errorno,$errormsg,$errorlvl)){
				$errormsg .= '<br /> Additionally, an error occured while logging the error.';
			}
		}
		//	Process error:
		if($errorlvl >= $this->CRITICAL_LEVEL){
			//	System error, script unable to operate
			$this->display_critical($errorno,$errormsg,$errorlvl);
			//die();
		}
		else{
			/*	non critical error, set error at page, and proceed with parsing
			 *	non-content related matters
			 */
			$file = fopen('./templates/error.tpl','r');
			$file_data = fread($file,filesize('./templates/error.tpl'));
			$file_data = explode('-->',$file_data);
			fclose($file);
			//	Parse template
			$output_data = str_replace('[ERRORNO]',$errorno,$file_data[1]);
			$output_data = str_replace('[ERRORMSG]',$errormsg,$output_data);
			$output_data = str_replace('[ERRORLVL]',$errorlvl,$output_data);

			$GLOBALS['page']->append_content($output_data,'[ERROR]',1);
			if($this->ERROR_OVERRIDE_CONTENT == 'force' && $errorlvl >= $this->ERROR_OVERRIDE_CONTENT_LEVEL ){
				$content = false;
			}
			elseif($this->ERROR_OVERRIDE_CONTENT == 'never'){
				$content = true;
			}
			$GLOBALS['page']->content_visibility($content);
		}
	}
	
	 function checkError(){
		//	Used to check if errors were set previously
		return $this->error_num;
	}
}
?>