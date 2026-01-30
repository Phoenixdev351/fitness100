{*
* Affiliates
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    FMM Modules
* @copyright © Copyright 2021 - All right reserved
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @category  FMM Modules
* @package   affiliates
*}
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css"/>
<script>
var socials = '{$socials nofilter}';
var social_label = parseInt({$social_label});
$(document).ready(function() {
	console.log(JSON.parse(socials))
    $("#share").jsSocials({
    	showCount: false,
    	showLabel: social_label,
    	url: "{$ref_link nofilter}",
        shareIn: "popup",
        shares: JSON.parse(socials),
    });
})


</script>
<div id="share"></div>

{literal}
<style type="text/css">
.jssocials-shares , .jssocials-share {
  display: inline-block!important; 
}
</style>
{/literal}