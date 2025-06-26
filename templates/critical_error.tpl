<!--
	Critical Error message Template file, Alter to change the appearance of a critical
	error message on script die() due to critical error handled internally;
	
	keywords [ERRORNO] (error number), [ERRORMSG] (error message), [ERRORLVL] (error level)
	
	Refrain from putting HTML comments in this file, should the need arise, add them in this block
	HTML comments besides this one will possibly break the script.
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Critical Error!</title>
</head>
<body style="background-color:#CCCCCC;">
<div align="center">
  <table width="550px" style="background-color:#FFFFFF;border:2px solid #000000;" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center" style="background: url(./images/miniheader_content.gif) right no-repeat;border-bottom:1px solid #003399; padding-left:5px;color:#FFFFFF;font-weight:bold;">Critical Error: </td>
    </tr>
    <tr>
      <td style="padding:5px;">A critical error ocurred while processing your request, the error returned was: <b>[ERRORNO].[ERRORLVL]</b><br />
        with the following error message attached:<br />
        <b>[ERRORMSG]</b><br /><br />
        </td>
    </tr>
    <tr>
    	<td style="padding-left:5px;">What does this mean?<br />
        This means the site is down, and probably will be for quite some time, please <u>refrain</u> from using F5 once every 5 seconds<br />
        the system administrator has been notified of the error, and will fix it as soon as possible.</td>
    </tr>
    <tr>
      <td style="background: url(./images/miniheader_content.gif) no-repeat right;">&nbsp;</td>
    </tr>
  </table>
</div>
</body>
</html>