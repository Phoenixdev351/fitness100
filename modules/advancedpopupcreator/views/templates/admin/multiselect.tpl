{**
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
*}

<style>
	/* Multiselect */
	.uix-multiselect { font-size: 1em; font-family: "Open Sans", Helvetica, Arial, sans-serif; background: #F5F8F9; border-radius: 3px;}
	.uix-multiselect ul { -moz-user-select: none; }
	.uix-multiselect li { margin: 0; padding: 0; cursor: default; min-height: 20px; font-size: 11px; list-style: none; }
	.uix-multiselect li a { color: #999; text-decoration: none; padding: 0; display: block; float: left; cursor: pointer;}
	.uix-multiselect li.ui-draggable-dragging { padding-left: 10px; }

	.uix-multiselect div.multiselect-selected-list { height: auto !important; position: relative; padding: 0; margin: 0; border: 0; float:right !important; }
	.uix-multiselect ul.multiselect-selected-list { position: relative; padding: 0; overflow: auto; overflow-x: hidden; background: #fff; margin: 0; list-style: none; width: 100%;  border: 1px solid #C7D6DB;}
	.uix-multiselect ul.multiselect-selected-list li { }

	.uix-multiselect div.multiselect-available-list { height: auto !important; position: relative; padding: 0; margin: 0; border: 0; float:left; }
	.uix-multiselect ul.multiselect-available-list { position: relative; padding: 0; overflow: auto; overflow-x: hidden; background: #fff; margin: 0; list-style: none; width: 100%;  border: 1px solid #C7D6DB;}
	.uix-multiselect ul.multiselect-available-list li { padding-left: 0px; }
	.uix-multiselect .option_content { padding-left: 5px; padding-right: 15px; }
	.uix-multiselect .ui-state-default { border: none; margin-bottom: 1px; position: relative; padding: 3px 3px 3px 20px; background: #edf7fb}

	.uix-multiselect .multiselect-available-list .option-element { background: #c2e6f4; }
	.uix-multiselect .multiselect-selected-list .option-element { background: #85dafa; }

	.uix-multiselect .multiselect-available-list .ui-state-hover { border: none; opacity: 0.70; color: #fff;}
	.uix-multiselect .multiselect-selected-list .ui-state-hover { border: none; opacity: 0.55; color: #fff;}

	.uix-multiselect .ui-widget-header {
		background: #edf7fb !important;
		border: 1px solid #C7D6DB !important;
		border-radius: 3px;
		font-weight: normal;
	}

	.uix-multiselect .add-all { float: right; padding: 7px; color: #0077a4;}
	.uix-multiselect .remove-all { float: right; padding: 7px; color: #0077a4;}
	.uix-multiselect .count { float: left; padding: 7px;}

	.uix-multiselect li span.ui-icon-arrowthick-2-n-s { position: absolute; left: 2px; }
	.uix-multiselect li a.action { position: absolute; right: 2px; top: 2px; }

	.uix-multiselect .ui-widget-header .uix-search {
		height: 15px;
		padding: 2px;
		opacity: 0.8;
		margin: 1px;
		width: 100px !important;
		float:right;
		font-size: 85%;
		box-sizing: border-box;
	}

	.uix-multiselect-original { position: absolute; left:-999999px; }
	.uix-multiselect { position: relative; margin-bottom: 5px; }
	.uix-multiselect .multiselect-selected-list, .uix-multiselect .multiselect-available-list { position:absolute; overflow:hidden; left: 0px !important;}
	.uix-multiselect .multiselect-selected-list { width: 50% !important; }
	.uix-multiselect .multiselect-available-list { width: 50% !important; }
	.uix-multiselect .multiselect-selected-list { float: right; }
	.uix-multiselect .ui-widget-header { overflow:hidden;  white-space:nowrap; padding:2px 4px; }
	.uix-multiselect .ui-widget-header div.header-text { white-space: nowrap; display: block !important;}
	.uix-multiselect .ui-widget-header .uix-control-right, .uix-multiselect .ui-widget-header .uix-control-left { width:16px; height:16px; }
	.uix-multiselect .ui-widget-header .uix-control-right { float:right; }
	.uix-multiselect .ui-widget-header .uix-control-left { float:left; }
	.uix-multiselect .uix-list-container { position:relative; height: 180px !important; overflow:auto; -webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; }
	.uix-multiselect .uix-list-container .ui-priority-secondary { padding-right:0; }
	.uix-multiselect .group-element { position:relative; padding-left:0;  white-space:nowrap; overflow:hidden; }
	.uix-multiselect .group-element-collapsable { padding-left:16px; }
	.uix-multiselect .group-element span.collapse-handle { position:absolute; margin-top:-8px; top:50%; left:0; }
	.uix-multiselect .group-element .label { margin:0 3px;  white-space:nowrap; overflow:hidden; }
	.uix-multiselect .group-element .ui-icon { float:left; cursor:pointer; }
	.uix-multiselect .option-element, .dragged-element { cursor:pointer; padding:3px 5px; }
	.uix-multiselect .option-element.ui-state-disabled { font-style:italic; }
	.dragged-element, .dragged-grouped-element { padding:1px 3px; }
	.dragged-grouped-element { padding-left:16px; }
	.uix-multiselect .grouped-option { position:relative; padding-left:16px }
	.uix-multiselect .grouped-option .ui-icon { position:absolute; left:0; }
</style>

<script type="text/javascript">
	var selectAll = "{l s='Select all' mod='advancedpopupcreator' js=1}";
	var deselectAll = "{l s='Deselect all' mod='advancedpopupcreator' js=1}";
	var selected = "{l s='selected' mod='advancedpopupcreator' js=1}";
	var itemsSelected_nil = "{l s='No options selected' mod='advancedpopupcreator' js=1}";
    var itemsSelected = "{l s='selected option' mod='advancedpopupcreator' js=1}";
    var itemsSelected_plural = "{l s='options selected' mod='advancedpopupcreator' js=1}";

	var itemsAvailable_nil = "{l s='No items available' mod='advancedpopupcreator' js=1}";
	var itemsAvailable = "{l s='options available' mod='advancedpopupcreator' js=1}";
	var itemsAvailable_plural = "{l s='options available' mod='advancedpopupcreator' js=1}";

	var itemsFiltered_nil = "{l s='No options found' mod='advancedpopupcreator' js=1}";
	var itemsFiltered = "{l s='option found' mod='advancedpopupcreator' js=1}";
	var itemsFiltered_plural = "{l s='options found' mod='advancedpopupcreator' js=1}";

	var searchOptions = "{l s='Search Options' mod='advancedpopupcreator' js=1}";
	var collapseGroup = "{l s='Collapse Group' mod='advancedpopupcreator' js=1}";
	var expandGroup = "{l s='Expand Group' mod='advancedpopupcreator' js=1}";
	var searchAllGroup = "{l s='Select All Group' mod='advancedpopupcreator' js=1}";
	var deselectAllGroup = "{l s='Deselect All Group' mod='advancedpopupcreator' js=1}";
</script>

<script src="../modules/advancedpopupcreator/lib/multiselect/ui.multiselect.js"></script>

<script type="text/javascript">
	var products_append = [];
	{if isset($products_available) && $products_available}
	    {foreach $products_available as $p}
	        products_append.push('<option value="{$p['id_product']|escape:'htmlall':'UTF-8'}">{$p['name']|escape:javascript}</option>');
	    {/foreach}

	    $("select[name='products[]']").append(products_append.join(''));
	{/if}

	{if isset($products_selected) && $products_selected}
	    var products_selected = "{$products_selected|escape:'htmlall':'UTF-8'}";
	    var products_sel_array = products_selected.split(',');
	    for (i = 0; i < products_sel_array.length; ++i) {
	        setSelectedIndex($("select[name='products[]']")[0], products_sel_array[i]);
	    }

	    function setSelectedIndex(s, v) {
		    for ( var i = 0; i < s.options.length; i++ ) {
		        if ( s.options[i].value == v ) {
		            s.options[i].selected = true;
		            return;
		        }
		    }
		}
	{/if}
</script>
