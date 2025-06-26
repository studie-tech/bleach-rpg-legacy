<?php
/*					Rules.inc.php
 *			Shows rules and regulations
 */
$fp = fopen('./files/rules.inc','r');
$output_buffer = stripslashes(fread($fp,filesize('./files/rules.inc')));
fclose($fp);
$GLOBALS['page']->insert_page_data('[CONTENT]',$output_buffer);
?>