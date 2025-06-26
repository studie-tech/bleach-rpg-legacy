<?php

require_once('./libs/training.inc.php');

class train extends training{
	
	public function train(){
		if(!isset($_GET['page'])){
			$this->main_page();
		}
		elseif($_GET['page'] == 'train'){
			if($_GET['train'] == 'Strength' || $_GET['train'] == 'Intelligence' || $_GET['train'] == 'Speed' || $_GET['train'] == 'Sword' || $_GET['train'] == 'Shikai' || $_GET['train'] == 'Bankai'){
				$this->train_stats();
			}
		}
		$this->return_stream();
	}
	
	
	public function main_page(){
		$this->getUserData();
		
		/*	Get avatar	*/
		if(file_exists('./images/avatars/'.$this->user[0]['id'].'.gif')){
			$avatar = '<img src="./images/avatars/'.$this->user[0]['id'].'.gif" />';
		}
		else{
			$avatar = '<img src="./images/default_avatar.gif" />';
		}
		
		/*	Finished training something	*/
		if($this->user[0]['technique_timer'] < time() && isset($this->user[0]['activity']) && $this->user[0]['activity'] !== ""){
			$this->get_technique();	
		}
		
		/*	Define technique action; available, training, unavailable	*/
		$timer = $this->user[0]['technique_timer'] - time() + 1;
		$y = '<center>Time Left<br /><script>var countDownInterval2='.$timer.';</script><script src="libs/javascript/TrainingCounter.js"></script><script>startit2();</script></center>';
		$n = '<center><strike>[5 Minutes]</strike></center>';
		$t = '<center>[5 Minutes]</center>';
		if( isset($this->user[0]['activity']) && $this->user[0]['activity'] !== "" ){
			switch($this->user[0]['activity']){
				case "Strength": 		$status['0']=$y; $status['1']=$n; $status['2']=$n; $status['3']=$n; $status['4']=$n; $status['5']=$n; 	break;
				case "Intelligence": 	$status['0']=$n; $status['1']=$y; $status['2']=$n; $status['3']=$n; $status['4']=$n; $status['5']=$n; 	break;
				case "Speed": 			$status['0']=$n; $status['1']=$n; $status['2']=$y; $status['3']=$n; $status['4']=$n; $status['5']=$n; 	break;
				case "Sword": 			$status['0']=$n; $status['1']=$n; $status['2']=$n; $status['3']=$y; $status['4']=$n; $status['5']=$n; 	break;
				case "Shikai": 			$status['0']=$n; $status['1']=$n; $status['2']=$n; $status['3']=$n; $status['4']=$y; $status['5']=$n; 	break;
				case "Bankai": 			$status['0']=$n; $status['1']=$n; $status['2']=$n; $status['3']=$n; $status['4']=$n; $status['5']=$y; 	break;
			}
		}else{
		 	$status['0']='<a href="?id='.$_GET['id'].'&page=train&train=Strength">'.$t.'</a>'; 
			$status['1']='<a href="?id='.$_GET['id'].'&page=train&train=Intelligence">'.$t.'</a>';
			$status['2']='<a href="?id='.$_GET['id'].'&page=train&train=Speed">'.$t.'</a>';
			$status['3']='<a href="?id='.$_GET['id'].'&page=train&train=Sword">'.$t.'</a>';
			$status['4']='<a href="?id='.$_GET['id'].'&page=train&train=Shikai">'.$t.'</a>';
			$status['5']='<a href="?id='.$_GET['id'].'&page=train&train=Bankai">'.$t.'</a>';
			
		}
		
		/*	Define the wording based on race	*/
		if( $this->user[0]['rankid'] == 1 ){
			$s="Sword Skill";
            $t="Shikai";
            $u="Bankai";
		}else{
		 	$s="Humans Devoured";
            $t="Hollows Devoured";
            $u="Resurrection";
		}
        
        // Rei percentage
        $rei_perc = ($this->user[0]['cur_rei'] / $this->user[0]['max_rei']) * 100;
		
		/*	Page to be parsed	*/
		$this->output_buffer .= '<div align="center"><br />
  		<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
	   	    <tr>
      	        <td colspan="4" align="center" style="border-top:none;" class="subHeader">Training</td>
    	    </tr>
            <tr>
                <td colspan="4">
                <br />
                    <div align="center">
                        <table border="4" cellpadding="0" cellspacing="0" width="95%" height="75" class="subcontent">
                            <tr>
                                 <td width="30%" align="center">
                                    Strength: '.$this->user[0]['strength'].' <br />
                                    Intelligence: '.$this->user[0]['intelligence'].' <br />
                                    Speed: '.$this->user[0]['speed'].'<br />
                                </td>
                                <td width="30%" align="center">
                                    '.$s.': '.$this->user[0]['sword'].' <br />
                                    '.$t.': '.$this->user[0]['shikai'].'<br />
                                    '.$u.': '.$this->user[0]['bankai'].'<br />
                                </td>
                                <td align="center" style="padding-top:10px;">'.$avatar.'<br /><br /></td>
                            </tr>
                            <tr>
                                 <td width="30%" align="center" colspan="3" style="padding-bottom:5px;">
                                    Current Reiatsu: <div align="left" style="height:5px; width:200px; border: 1px solid #000000;"><img src="./images/rei_bar.jpg" style="border-right:1px solid #000000;" height="5px" width="'.$rei_perc.'%" /></div>
                                 </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
            
    	    
            <tr>
                <td align="center" colspan="4">&nbsp;</td>
            </tr>
            <tr>
	            <td align="center" colspan="4">
	      
                    <div align="center">
                      <center>
                      <table border="3" cellspacing="0" width="95%" id="AutoNumber1" cellpadding="0" style="padding:0; border-collapse: collapse" bordercolor="#CCCCCC" bordercolorlight="#CCCCCC" bordercolordark="#CCCCCC">
                        <tr>
                          <td width="10%" class="subHeader" align="center">
                          Attribute</td>
                          <td width="70%" class="subHeader" align="center">
                          Description</td>
                          <td width="20%" class="subHeader" align="center">
                          Time &amp; Action</td>
                        </tr>
                        <tr>
                          <td width="10%" bgcolor="#666666" align="center">
                            <img src="images/icons/strength.gif">
                          </td>
                          <td width="70%" bgcolor="#999999" style="padding-left:5px;">
                            Train your body through various physical exercises. <br />
	                        - Used in all forms of attack<br />Will grant you <font color="#008000">+1 strength</font>
                          </td>
                          <td width="20%" bgcolor="#999999" style="padding-left:5px;">
                            '.$status['0'].'
                          </td>
                        </tr>
                        <tr>
                          <td width="10%" bgcolor="#666666" align="center">
                            <img src="images/icons/intelligence.gif">
                          </td>
                          <td width="70%" bgcolor="#808080" style="padding-left:5px;">
                            Improve your intelligence by taking various IQ tests, doing brain games etc. <br />
                            - You will never be able to win a battle, if the opponent outsmarts you.<br />
	                        Will grant you <font color="#008000">+1 intelligence</font>
                          </td>
                          <td width="20%" bgcolor="#808080" style="padding-left:5px;">
                            '.$status['1'].'
                          </td>
                        </tr>
                        <tr>
                          <td width="10%" bgcolor="#666666" align="center">
                            <img src="images/icons/speed.gif">
                          </td>
                          <td width="70%" bgcolor="#999999" style="padding-left:5px;">
                            The only way to obtain speed is by moving fast. Run as fast as you can. <br />
                            - Good when fleeing and fighting<br />
	                        Will grant you <font color="#008000">+1 speed</font>
                          </td>
                          <td width="20%" bgcolor="#999999" style="padding-left:5px;">
                            '.$status['2'].'
	                      </td>
                        </tr>
                      </table>
                      </center>
                    </div>
                    <br /><br />';

if($this->user[0]['rankid']==1){

	if($this->user[0]['shikai'] > 0){
		$shikai = '<b>Shikai<br></b>Merely knowing the name of your zanpaktou and being able to release your shikai isn\'t nearly enough. You need to practise hard in order to attain full control';
	}else{
		$shikai = '<b>Shikai<br></b>You do not know the name of your zanpaktou at the moment and can therefore not practise the usage of your shikai.';	
		$status['4'] = '<center>Not Available</center>';
	}
	if($this->user[0]['bankai'] > 0){
		$bankai = '<b>Bankai<br></b>You are now able to release your bankai and is to be considered one of the strongest shinigami. It is said it takes years of practice to fully master the bankai, so 	train hard!';
	}else{
		$bankai = '<b>Bankai<br></b>You have not learned your bankai yet, and can therefore not practise the usage of it';	
		$status['5'] = '<center>Not Available</center>';
	}

	$this->output_buffer .= '
	<div align="center">
	  <center>
	  <table border="3" cellspacing="0" width="95%" id="AutoNumber1" cellpadding="0" style="padding:0; border-collapse: collapse" bordercolor="#CCCCCC" bordercolorlight="#CCCCCC" bordercolordark="#CCCCCC">
	    <tr>
	      <td width="10%" class="subHeader" align="center">
	        Technique
          </td>
	      <td width="70%" class="subHeader" align="center">
	        Name & Description
          </td>
	      <td width="20%" class="subHeader" align="center">
    	    Time &amp; Action
          </td>
	    </tr>
	    <tr>
	      <td width="10%" bgcolor="#666666" align="center">
	          <img src="images/icons/sword.gif">
          </td>
	      <td width="70%" bgcolor="#999999" style="padding-left:5px;">
	        <b>Sword Practise<br>
	        </b>Improve your fighting skills by taking lessons.
          </td>
	      <td width="20%" bgcolor="#999999" style="padding-left:5px;">
	        '.$status['3'].'</td>
	    </tr>
	    <tr>
	      <td width="10%" bgcolor="#666666" align="center">
	        <img src="images/icons/unknown.gif">
          </td>
	      <td width="70%" bgcolor="#808080" style="padding-left:5px;">
	        '.$shikai.'
          </td>
	      <td width="20%" bgcolor="#808080" style="padding-left:5px;">
	        '.$status['4'].'
          </td>
	    </tr>
	    <tr>
	      <td width="10%" bgcolor="#666666" align="center">
	        <img src="images/icons/unknown.gif">
          </td>
	      <td width="70%" bgcolor="#999999" style="padding-left:5px;">
	        '.$bankai.'
          </td>
	      <td width="20%" bgcolor="#999999" style="padding-left:5px;">
	        '.$status['5'].'
          </td>
	    </tr>

	  </table>
	  </center>
	</div>';
}else{
	
	if($this->user[0]['shikai'] > 0){
		$shikai = '<b>Devour Hollows<br></b>In order to sustain your adjuchas-form and develop further it is neccesary for you to devour hollows<br /><font color="#008000">Restore 40% Health</font>';
	}else{
		$shikai = '<b>Devour Hollows<br></b>You have not removed your mask and have no need for devouring other hollows yet';	
		$status['4'] = '<center>Not Available</center>';
	}
	if($this->user[0]['bankai'] > 0){
		$bankai = '<b>Resurrection<br></b>As a vasto lord you have sealed your original hollow powers in the form of an Zanpaktou. It is neccesary however to practise the strength and the release of these powers!';
	}else{
		$bankai = '<b>Resurrection<br></b>You are not yet powerful enough to have your abillities sealed in the form of a Zanpaktou.';	
		$status['5'] = '<center>Not Available</center>';
	}

	$this->output_buffer .= '
	<div align="center">
	  <center>
	  <table border="3" cellspacing="0" width="95%" id="AutoNumber1" cellpadding="0" style="padding:0; border-collapse: collapse" bordercolor="#CCCCCC" bordercolorlight="#CCCCCC" bordercolordark="#CCCCCC">
	    <tr>
	      <td width="10%" bordercolor="#CCCCCC" bgcolor="#333333">
	      <p style="margin-left: 5"><font color="#FFFFFF" size="2">Action</font></td>
	      <td width="70%" bgcolor="#333333">
	      <p style="margin-left: 5"><font color="#FFFFFF" size="2">Name & Description</font></td>
	      <td width="20%" bgcolor="#333333">
    	  <p style="margin-left: 5"><font color="#FFFFFF" size="2">Time &amp; Action</font></td>
	    </tr>
	    <tr>
	      <td width="10%" bgcolor="#666666" align="center">
	      <p align="center"><img src="images/icons/humans.gif"></td>
	      <td width="70%" bgcolor="#999999">
	      <p style="margin-left: 5"><font size="2"><b>Devour Humans<br>
	      </b>It is absolutely neccesary to devour humans and take their spirit energy. However, it is also important not to cause too much fuss and attract the shinigami, which means it can be hard to find suitable target.<br /><font color="#008000">Restore 20% Health</font></font></td>
	      <td width="20%" bgcolor="#999999">
	      <p style="margin-left: 5"><font size="2">'.$status['3'].'</font></td>
	    </tr>
	    <tr>
	      <td width="10%" bgcolor="#666666" align="center">
	      <p><img src="images/icons/hollows.gif"></td>
	      <td width="70%" bgcolor="#808080">
	      <p style="margin-left: 5"><font size="2">'.$shikai.'</font></td>
	      <td width="20%" bgcolor="#808080">
	      <p style="margin-left: 5"><font size="2">'.$status['4'].'</font></td>
	    </tr>
	    <tr>
	      <td width="10%" bgcolor="#666666" align="center">
	      <p align="center"><img src="images/icons/sword.gif"></td>
	      <td width="70%" bgcolor="#999999">
	      <p style="margin-left: 5"><font size="2">'.$bankai.'</font></td>
	      <td width="20%" bgcolor="#999999">
	      <p style="margin-left: 5"><font size="2">'.$status['5'].'</font></td>
	    </tr>

	  </table>
	  </center>
	</div>';
}
$this->output_buffer .= '   	      
	    </td></tr><tr><td align="center" colspan="4">&nbsp;</td>
	    </tr></table><br /><br /></div>';
	}
}

$training = new train();
?>