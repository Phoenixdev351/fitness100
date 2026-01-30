{*
 * 2010-2022 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2022 Bl Modules
 * @license
*}
<script>
	{literal}
	  (function(a,b,c,d,e,f,g){a['SkroutzAnalyticsObject']=e;a[e]= a[e] || function(){
	    (a[e].q = a[e].q || []).push(arguments);};f=b.createElement(c);f.async=true;
	    f.src=d;g=b.getElementsByTagName(c)[0];g.parentNode.insertBefore(f,g);
	  })(window,document,'script','https://analytics.skroutz.gr/analytics.min.js','skroutz_analytics');
	  skroutz_analytics('session', 'connect', '{/literal}{$skroutzId|escape:'htmlall':'UTF-8'}{literal}');
	{/literal}
</script>