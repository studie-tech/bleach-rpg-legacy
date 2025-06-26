<?php
/*				Terms.inc.php
 *			Prints the TOS to screen
 */
$fp = fopen('./files/terms.inc','r');
$output_buffer = stripslashes(fread($fp,filesize('./files/terms.inc')));
fclose($fp);
$GLOBALS['page']->insert_page_data('[CONTENT]',$output_buffer);
?>