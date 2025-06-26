
	var c_reloadwidth1=200
	var countDownTime1=countDownInterval1+1;
	
	function countDown1(){
	 	countDownTime1--;
	 	if (countDownTime1 <=0){
	 	 	countDownTime1=countDownInterval1;
	 	 	clearTimeout(counter1)
	 	 	//window.location.reload()
            document.getElementById("countDownText1").innerHTML = "Training Done";
	 	 	return
	 	}
		 
		if (document.all) //if IE 4+
			document.all.countDownText1.innerText = Math.floor(countDownTime1 / 60)+":"+(countDownTime1 % 60)+" ";
		else if (document.getElementById) //else if NS6+
			document.getElementById("countDownText1").innerHTML= Math.floor(countDownTime1 / 60)+":"+(countDownTime1 % 60)+" "
		else if (document.layers){
			document.c_reload.document.c_reload2.document.write('<b id="countDownText1">'+countDownTime1+' </b>')
			document.c_reload.document.c_reload2.document.close()
		}
		counter1=setTimeout("countDown1()", 1000);
	}
	
	function startit1(){
		if (document.all||document.getElementById){ 
            if( countDownTime1 <= 0 ){
                document.write('<b id="countDownText1">Training Done</b>')
            }
            else{
                document.write('<b id="countDownText1">'+countDownTime1+' </b>')
            }
			countDown1()
        }
	}