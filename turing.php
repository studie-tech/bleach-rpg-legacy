<?php
session_start();
 
/*
* File: CaptchaSecurityImages.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 03/08/06
* Updated: 07/02/07
* Requirements: PHP 4/5 with GD and FreeType libraries
* Link: http://www.white-hat-web-design.co.uk/articles/php-captcha.php
* 
* This program is free software; you can redistribute it and/or 
* modify it under the terms of the GNU General Public License 
* as published by the Free Software Foundation; either version 2 
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
* GNU General Public License for more details: 
* http://www.gnu.org/licenses/gpl.html
*
*/
 
class CaptchaSecurityImages {
 
   var $font = 'impact.ttf';
 
   function generateCode($characters) {
      /* list all possible characters, similar looking characters and vowels have been removed */
      $possible = '23456789bcdfghjkmnpqrstvwxyz';
      $code = '';
      $i = 0;
      while ($i < $characters) { 
         $code .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
         $i++;
      }
      return $code;
   }
 
   function CaptchaSecurityImages($width='100',$height='20',$characters='4') {
          $code = $this->generateCode(4);
          /* font size will be 75% of the image height */
          $font_size = 15;
          $image = imagecreate(95, 20) or die('Cannot initialize new GD image stream');
          /* set the colours */
          $background_color = imagecolorallocate($image, 255, 255, 255);
          $text_color = imagecolorallocate($image, 20, 40, 100);
          $noise_color = imagecolorallocate($image, 100, 120, 180);
          /* generate random dots in background */
          for( $i=0; $i<($width*$height)/20; $i++ ) {
             imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $noise_color);
          }
          /* generate random lines in background */
          for( $i=0; $i<($width*$height)/250; $i++ ) {
             imageline($image, mt_rand(0,$width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height), $noise_color);
          }
          /* create textbox and add text */
          if(true || file_exists($font)){
              $textbox = imagettfbbox($font_size, 0, $this->font, $code) or die('Error in imagettfbbox function');
              $x = ($width - $textbox[4])/2;
              $y = ($height - $textbox[5])/2;
              imagettftext($image, $font_size, 0, $x, 17, $text_color, $this->font , $code) or die('Error in imagettftext function');
              /* output captcha image to browser */
              header('Content-Type: image/jpeg');
              imagejpeg($image);
              imagedestroy($image);
              $_SESSION['security_code'] = $code;
          }
          else{
              echo 'Font file not found';
          }
   }
 
}
 
$captcha = new CaptchaSecurityImages(100,40,6);
 
?>
