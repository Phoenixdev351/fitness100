{**
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
{extends file="helpers/list/list_header.tpl"}
{block name='override_header'}
{if $submit_form_ajax}
	<script type="text/javascript">
		$('#voucher', window.parent.document).val("{$new_cart_rule->code|escape:'htmlall':'UTF-8'}");
		parent.add_cart_rule({$new_cart_rule->id|intval|escape:'htmlall':'UTF-8'});
		parent.$.fancybox.close();
	</script>
{/if}
{/block}
