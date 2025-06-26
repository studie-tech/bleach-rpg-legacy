<?php	
		// User Dot
		$height = (-15+72*$_GET['lo']);
		$width = -15+72*$_GET['la'];
		// Enemy Dot
		$Eheight = (-20+72*$_GET['Elo']);
		$Ewidth = -15+72*$_GET['Ela'];
		// Event Dot
		$Evheight = (-18+72*$_GET['Evlo']);
		$Evwidth = -10+72*$_GET['Evla'];

		// Create image
		$image = imagecreatefromgif("../images/map2.gif");
		
		// Colors
		$red = imagecolorallocate($image, 120, 0, 0);
		$blue = imagecolorallocate($image, 0, 0, 120);
		$yellow = imagecolorallocate($image, 225, 159, 7);
		
		// Process Dots
 	   imagefilledellipse($image, $Evheight, $Evwidth, 30, 30, $yellow);
   	   imagefilledellipse($image, $Eheight, $Ewidth, 30, 30, $blue);
	   imagefilledellipse($image, $height, $width, 30, 30, $red);


		// Finish it off	   
	   $gold = imagecolorallocate($image, 255, 240, 00);
	   imagepng($image);
	   imagedestroy($image);
?>