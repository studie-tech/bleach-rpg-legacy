
	var c_reloadwidth2=200
	var countDownTime2=countDownInterval2+1;
	
	function countDown2(){
	 	countDownTime2--;
	 	if (countDownTime2 <=0){
	 	 	countDownTime2=countDownInterval2;
	 	 	clearTimeout(counter2)
	 	 	window.location.reload()
	 	 	return
	 	}
		 
		if (document.all) //if IE 4+
			document.all.countDownText2.innerText = Math.floor(countDownTime2 / 60)+":"+(countDownTime2 % 60)+" ";
		else if (document.getElementById) //else if NS6+
			document.getElementById("countDownText2").innerHTML= Math.floor(countDownTime2 / 60)+":"+(countDownTime2 % 60)+" "
		else if (document.layers){
			document.c_reload.document.c_reload2.document.write('<b id="countDownText2">'+countDownTime2+' </b>')
			document.c_reload.document.c_reload2.document.close()
		}
		counter2=setTimeout("countDown2()", 1000);
	}
	
	function startit2(){
		if (document.all||document.getElementById)
			document.write('<b id="countDownText2">'+countDownTime2+' </b>')
			countDown2()
	}