<?php
/*				Donations.inc.php
 *			Prints the donation-page to screen
 */

$output_buffer = '<div align="center">
<table width="95%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <td colspan="2" align="center" class="subHeader" style="border-top:none;" >Donations for Bleach-Game</td>
    </tr>
    <tr>
        <td style="padding-left: 5px">
            If you like this bleach-game project and want to support it, now you can. Please do not feel forced to donate any money if you\'re short on them yourself. 
            Also note that you will not gain anything in the game by donating; i.e. spirit shards are not awarded. This option is purely for those who wish to support the project and have some spare cash.
            <br /><br />
              <center>
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                    <input type="hidden" name="cmd" value="_s-xclick">
                    <input type="hidden" name="hosted_button_id" value="4SCMVTHTDW4JL">
                    <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                    <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
                </form>
              </center>
            
        </td>
    </tr>
</table>
</div>';


$GLOBALS['page']->insert_page_data('[CONTENT]',$output_buffer);
?>