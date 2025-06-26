<!--
	Template for regular error message parsed ABOVE the content
	to warn users about possible problems with the page they're viewing.
	
	keywords [ERRORNO] (error number), [ERRORMSG] (error message), [ERRORLVL] (error level)
	
	Refrain from putting HTML comments in this file, should the need arise, add them in this block
	HTML comments besides this one will possibly break the script.	
-->

<div align="center">
  <table width="90%" class="table" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center" style="border-top:none;" class="subHeader"> Error: </td>
    </tr>
    <tr>
      <td style="padding:5px;" align="center">An error ocurred while processing your request.<br /> the error returned was: <span style="color:darkred;">[ERRORNO].[ERRORLVL]</span><br />
        with the following error message attached:<br />
        <span style="color:darkred;">[ERRORMSG]</span>
        </td>
    </tr>
  </table><br />
</div>