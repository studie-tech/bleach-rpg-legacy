
	var c_reloadwidth=200
	var countDownTime=countDownInterval+1;
	
	function countDown(){
	 	countDownTime--;
	 	if (countDownTime <=0){
	 	 	countDownTime=countDownInterval;
	 	 	clearTimeout(counter)
	 	 	window.location.reload()
	 	 	return
	 	}
		 
		if (document.all) //if IE 4+
			document.all.countDownText.innerText = Math.floor(countDownTime / 60)+":"+(countDownTime % 60)+" ";
		else if (document.getElementById) //else if NS6+
			document.getElementById("countDownText").innerHTML= Math.floor(countDownTime / 60)+":"+(countDownTime % 60)+" "
		else if (document.layers){
			document.c_reload.document.c_reload2.document.write('Time Left<br /><b id="countDownText">'+countDownTime+' </b>')
			document.c_reload.document.c_reload2.document.close()
		}
		counter=setTimeout("countDown()", 1000);
	}
	
	function startit(){
		if (document.all||document.getElementById)
			document.write('Time Left<br /><b id="countDownText">'+countDownTime+' </b>')
			countDown()
	}