<?php
/*  disambig.php
    Last update: 4/16/07
    
    The purpose of this file is to contain the HTML for the disambiguation page that prints for 
    a user when he/she selects a record that has more than one possible location.  While the code
    that prints the choices is found in search.php, it is necessary to create a page around that
    selection table, so this file contains the raw HTML code that the table can sit within.
    
    print_first_half prints all page content before the table, and
    print_second_half prints the rest of the page.
*/




function print_first_half()
{
PRINT<<<END

<html  dir="LTR">
<head>
<title>Jerome Library -- Bowling Green State University                              </title>
<base target="_self"/>
<link rel="stylesheet" type="text/css" href="http://maurice.bgsu.edu/scripts/ProStyles.css" />
<link rel="stylesheet" type="text/css" href="http://maurice.bgsu.edu/screens/styles.css" />
<link rel="shortcut icon" type="ximage/icon" href="http://maurice.bgsu.edu/screens/favicon.ico" />
<script language="JavaScript" type="text/javascript" src="http://maurice.bgsu.edu/scripts/common.js"></script>
<script language="JavaScript" type="text/javascript" src="http://maurice.bgsu.edu/scripts/features.js"></script>

<script language="JavaScript">
<!-- Hide the JS
startTimeout(6000000, "http://maurice.bgsu.edu/search/X");
// -->
</script>
<noscript>
<meta http-equiv="Refresh" content="6000;URL=http://maurice.bgsu.edu/search/X" />
</noscript>

<script type="text/javascript" src="http://maurice.bgsu.edu/screens/bibdisplay.js"></script><script type="text/javascript" src="http://maurice.bgsu.edu/screens/brief.js"></script><!--[if lte IE 8]><link rel="stylesheet" media="all" type="text/css" href="http://maurice.bgsu.edu/screens/ie_styles.css" /><![endif]-->
</head>
<body FF7300 >
<!-- begin toplogo.html file -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<!-- 2006 WebPAC Pro Version, set from 26 March, 2007 -->
<!-- This file last changed: 9 February, 2007 -->
<title>BGSU Libraries Catalog</title>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="shortcut icon" href="http://maurice.bgsu.edu/screens/favicon.ico" />



<script type="text/javascript">
var timeout=125;
var closetimer=0;
var hp=0;


function ShowPopup(hoveritem, id)
{
hp = document.getElementById(id);

// Set position of hover-over popup
hp.style.top = hoveritem.offsetTop + 142; hp.style.left = hoveritem.offsetLeft - 0;

// Set popup to visible
hp.style.visibility = "Visible";

// cancel close timer
	mcancelclosetime();
}

function HidePopup()
{

if(hp) hp.style.visibility = "Hidden";
}
// go close timer
function mclosetime()
{
	closetimer = window.setTimeout(HidePopup, timeout); }

// cancel close timer
function mcancelclosetime()
{
	if(closetimer)
	{
		window.clearTimeout(closetimer);
		closetimer = null;
	}
}

// close layer when click-out
document.onclick = HidePopup(); 


</script>

<a href="http://maurice.bgsu.edu/search/X"><img src="http://maurice.bgsu.edu/screens/toplogo.jpg" alt="logo" usemap="#logomap" border="0"  /></a> <map id="logomap" name="logomap"> <area shape="rect" coords="70,60,990,120" href="http://www.bgsu.edu/colleges/library/about/page52945.html" alt="Pictures" /> </map> 

<div id="myheader" >

<ul class="pos_abs">

<span li><a class="one" href="http://maurice.bgsu.edu/search/X">Search Catalog</span></a></li> | 
<span li><a class="one" href="http://maurice.bgsu.edu/patroninfo" >My Library Account</span></a></li> |  
<span li><a id="hoverover" style="cursor:default; text-decoration: underline; color:white; " onMouseOver="ShowPopup(this, 'hoverpopup');" onMouseOut="mclosetime();">Course Reserves</span></a></li> | 
<span li><a class="one" href="http://maurice.bgsu.edu/feeds" >New Books</span></a></li> | 
<span li><a class="one" href="http://maurice.bgsu.edu/screens/help_index.html" >Catalog Help</span></a></li> | 
<span li><a class="one" href="http://www.bgsu.edu/colleges/library/hours.html" >Library Hours</span></a></li> | 

<span li><a class="one" href="http://www.bgsu.edu/colleges/library/infosrv/ref/ask.html" >Ask Us!</span></a></li> | 
<span li ><a class="one" href="http://www.bgsu.edu/colleges/library/" > Library Home</span></a></li>                                

</ul>
</div>

<div id="hoverpopup" style="visibility:hidden; position:absolute; top:0; left:0; " onmouseover="mcancelclosetime()" onmouseout="mclosetime()" >
 <table bgcolor=#000000> <tr><td><font color="#FFFFFF" size=2 ><a class="one" href="http://maurice.bgsu.edu/search/r">Search by Course</a></font></td></tr> <tr><td><font size=2><a class="one" href="http://maurice.bgsu.edu/search/p">Search by Instructor</a></font></td></tr> <tr><td><font size=2><a class="one" href="https://reserve.bgsu.edu/eres/default.aspx">Electronic Reserves</a></font></td></tr></table></div>
<p>&nbsp;</p>


END;
}

function print_second_half(){
PRINT<<<END
<p>&nbsp;</p>
<div id="botlogofooter">

<tr>
            <td align="center" height="5" valign="top" width="1"><img alt="Spacer" height="5" src="http://www.bgsu.edu/images/spacer.gif" width="1"></td>
         </tr>
         <tr>
            <td colspan="2">

               <!--Begin Global Footer LIB-->
               <table width="100%" height="22" border="0" cellpadding="0" cellspacing="0">
                  <tr align="left" valign="top">

                     <td width="10" height="22" align="center"><img width="10" height="22" hspace="0" vspace="0" border="0" alt="Spacer" src="http://www.bgsu.edu/images/spacer.gif"></td>
                     <td height="22" align="right" class="footer">
                        <div align="center"><background color="111111"><a href="http://www.bgsu.edu/colleges/library/">University&nbsp;Libraries&nbsp;&nbsp;</a>|&nbsp;&nbsp;<a href="http://www.bgsu.edu/colleges/library/page45203.html">Contact&nbsp;Us&nbsp;at&nbsp;the&nbsp;University&nbsp;Libraries</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.bgsu.edu/colleges/library/page41793.html">Libraries&nbsp;Site&nbsp;Map</a></font></font></div>

                     </td>
                  </tr>
               </table>
            </td>
         </tr>
         <tr>
            <td colspan="2">
               <!--Begin Global Footer-->

               <table width="100%" height="22" border="0" cellpadding="0" cellspacing="0">

                  <tr align="left" valign="top">
                     <td width="10" height="22" align="center"><img width="10" height="22" hspace="0" vspace="0" border="0" alt="Spacer" src="http://www.bgsu.edu/images/spacer.gif"></td>
                     <td height="22" align="right" class="footer">
                        <div align="center"><a href="http://www.bgsu.edu">Bowling&nbsp;Green&nbsp;State&nbsp;University&nbsp;&nbsp;</a>| &nbsp;Bowling&nbsp;Green,&nbsp;OH&nbsp;43403-0001&nbsp;&nbsp;| &nbsp;<a href="http://www.bgsu.edu/scripts/contact.html">Contact&nbsp;Us</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.bgsu.edu/map/">Campus Map</a>&nbsp;&nbsp;| &nbsp;<a href="http://www.bgsu.edu/sitemap.html">Site&nbsp;Map</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.oit.ohio.gov/IGD/policy/pdfs_policy/ITP-F.3.pdf">Accessibility Policy</a> (<a href="http://www.adobe.com/products/acrobat/">PDF Reader</a>)
                                 								</font></font></div>

                     </td>
                  </tr>
               </table>
            </td>
         </tr>
  

</tr>
</div>



<!--End the bottom logo table-->
<!-- end botlogo.html file -->


</body>
</html>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-3319349-1";
urchinTracker();
</script>
<!--this is customized <screens/bib_display.html>-->

END;

}
?>
