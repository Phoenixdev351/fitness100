/**
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitutes a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2020 idnovate.com
*  @license   See above
*/

/* http://keith-wood.name/localisation.html
   Localisation assistance for jQuery v1.0.4.
   Written by Keith Wood (kbwood{at}iinet.com.au) June 2007.
   Dual licensed under the GPL (http://dev.jquery.com/browser/trunk/jquery/GPL-LICENSE.txt) and
   MIT (http://dev.jquery.com/browser/trunk/jquery/MIT-LICENSE.txt) licenses.
   Please attribute the author if you use it. */
(function($){$.localise=function(c,d,e,f,g){if(typeof d!='object'&&typeof d!='string'){g=f;f=e;e=d;d=''}if(typeof e!='boolean'){g=f;f=e;e=false}if(typeof f!='string'&&!isArray(f)){g=f;f=['','']}var h={async:$.ajaxSettings.async,timeout:$.ajaxSettings.timeout};d=(typeof d!='string'?d||{}:{language:d,loadBase:e,path:f,timeout:g});var j=(!d.path?['','']:(isArray(d.path)?d.path:[d.path,d.path]));$.ajaxSetup({async:false,timeout:(d.timeout||500)});var k=function(a,b){if(d.loadBase){$.getScript(j[0]+a+'.js')}if(b.length>=2){$.getScript(j[1]+a+'-'+b.substring(0,2)+'.js')}if(b.length>=5){$.getScript(j[1]+a+'-'+b.substring(0,5)+'.js')}};var l=normaliseLang(d.language||$.localise.defaultLanguage);c=(isArray(c)?c:[c]);for(i=0;i<c.length;i++){k(c[i],l)}$.ajaxSetup(h)};$.localize=$.localise;$.localise.defaultLanguage=normaliseLang(navigator.language||navigator.userLanguage);function normaliseLang(a){a=a.replace(/_/,'-').toLowerCase();if(a.length>3){a=a.substring(0,3)+a.substring(3).toUpperCase()}return a}function isArray(a){return(a&&a.constructor==Array)}})(jQuery);
