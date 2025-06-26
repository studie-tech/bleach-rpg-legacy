
	var c_reloadwidth3=200
	var countDownTime3=countDownInterval3+1;
	
	function countDown3(){
	 	countDownTime3--;
	 	if (countDownTime3 <=0){
	 	 	countDownTime3=countDownInterval3;
	 	 	clearTimeout(counter3)
	 	 	window.location.reload()
	 	 	return
	 	}
		 
		if (document.all) //if IE 4+
			document.all.countDownText3.innerText = Math.floor(countDownTime3 / 60)+":"+(countDownTime3 % 60)+" ";
		else if (document.getElementById) //else if NS6+
			document.getElementById("countDownText3").innerHTML= Math.floor(countDownTime3 / 60)+":"+(countDownTime3 % 60)+" "
		else if (document.layers){
			document.c_reload.document.c_reload3.document.write('<b id="countDownText3">'+countDownTime3+' </b>')
			document.c_reload.document.c_reload3.document.close()
		}
		counter3=setTimeout("countDown3()", 1000);
	}
	
	function startit3(){
		if (document.all||document.getElementById)
			document.write('<b id="countDownText3">'+countDownTime3+' </b>')
			countDown3()
	}