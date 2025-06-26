<?php

if($_GET['ref']){
	$ref = "&ref=".$_GET['ref']."";
}
else{
	$ref = "";
}

$output_buffer .= "
<div align='center'>
    <center>
        <table border='0' cellspacing='1' width='100%' id='AutoNumber6'>
            <tr>
                <td width='45%' valign='top'>
                    <div align='center'>
                        <center>
                            <table border='4' cellpadding='0' cellspacing='0' width='95%' style='height:75px;'>
                                <tr>
                                    <td width='100%' valign='top'>
                                               
                                                <table border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse' width='100%' id='AutoNumber12'>
                                                    <tr>
                                                        <td width='50%'>
                                                            <img border='0' src='images/side.shinobi.gif' width='136' height='200' alt='Good Ichigo'></img>
                                                        </td>
                                                        <td width='50%' class='td3' align='center'>

                                                                <font face='Verdana'>
                                                                    <u><b>THE SHINIGAMI<br /></b></u><i>&quot;
                                                                    <a href='?id=2&amp;race=Shinigami".$ref."'>Our 
                                                                job as Shinigami is to keep order and balance 
                                                                between the worlds of human and hollows</a>&quot;</i></font>
                                                        </td>
                                                    </tr>
                                                </table>

                                        
                                    </td>
                                </tr>
                            </table>
                        </center>
                    </div>
                </td>
                <td width='55%' valign='top'>
                    <div align='center'>
                        <center>
                            <table border='4' cellpadding='0' cellspacing='0' width='95%' style='height:75px;'>
                                <tr>
                                    <td width='100%' valign='top'>
                                        
                                        <table  border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse' width='100%' id='AutoNumber11'>
                                            <tr>
                                                <td width='50%' class='td3' align='center'>
                                                    <font face='Verdana'><u><b>
                                                    THE HOLLOWS<br />
                                                    </b></u><i>&quot;<a href='?id=2&amp;race=Hollow".$ref."'>We seek.. to devour.. to kill..to slaughter.. 
                                                    humans.. shinigami.. everyone</a>&quot;</i></font>
                                                </td>
                                                <td width='50%' align='right'>
                                                    <img border='0' src='images/side.hollow.gif' width='235' height='200' alt='Evil Ichigo'></img>
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>
                        </center>
                    </div>
                </td>
            </tr>
            <tr>
                <td width='25%' valign='top' align='center'>
                    <b><font face='Verdana' size='2'><br />
                    <a href='?id=2&amp;race=Shinigami".$ref."'>Join Now</a></font></b>
                </td>
                <td width='25%' valign='top' align='center'>
                    <b><font face='Verdana' size='2'><br />
                    <a href='?id=2&amp;race=Hollow".$ref."'>Join Now</a></font></b>
                </td>
            </tr>
            <tr>
                <td width='50%' colspan='2' valign='top' align='center'>
                    <font face='Verdana' size='1'><br />
                    <a href='?id=2&amp;act=forgot'>Forgot your password?</a></font> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
                </td>
            </tr>
        </table>
    </center>
</div>
";
$GLOBALS['page']->insert_page_data('[CONTENT]',$output_buffer);
?>