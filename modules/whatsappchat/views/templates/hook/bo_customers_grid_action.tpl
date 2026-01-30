{**
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate
*  @copyright 2020 idnovate
*  @license   See above
*}
{if version_compare($smarty.const._PS_VERSION_,'1.4','<')}
    <!-- TODO PS13 -->
    {literal}
    <script type="text/javascript">
        if (document.URL.indexOf('id_customer') > 0) {
            $(document).ready(function() {
                var id_customer = '{/literal}{$smarty.get.id_customer|default:0|escape:'htmlall':'UTF-8'}{literal}'
                //var html = ' <a href="#" onclick="customers_list.getCustomerPhoneAndOpenWhatsAppChat(' + id_customer + ');return false;"><img src="{/literal}{$this_path_bo|escape:'htmlall':'UTF-8'}{literal}views/img/whatsapp-32x32.png" /> {/literal}{$action_whatsappchat|escape:'htmlall':'UTF-8'}{literal}</a>';
                //$(this).find("a[href='javascript:window.print()']").append(html);
            });
        } else {
            $(document).ready(function() {
                $('.table.table tbody tr').each(function(){
                    //var html = '<a href="#" onclick="customers_list.getCustomerPhoneAndOpenWhatsAppChat(' + id_customer + ');return false;" ' + 'title="{/literal}{$action|escape:'htmlall':'UTF-8'}{literal}"><img src="{/literal}{$this_path_bo|escape:'htmlall':'UTF-8'}{literal}views/img/whatsapp-green.png" width="16px"/></a>';
                    //$(this).find('td:last').append(html);
                })
            });
        }
    </script>
    {/literal}
{else}
    {literal}
    <script type="text/javascript">
        var customers_list = {
            init: function() {
                customers_list.createListDropdown();
            },
            createListDropdown: function() {
                {/literal}{if version_compare($smarty.const._PS_VERSION_,'1.5','<')}{literal}
                var parent = $('table.table');
                {/literal}{if $smarty.get.tab|lower == 'adminorders'}{literal}
                parent = [];
                {/literal}{/if}{literal}
                {/literal}{elseif version_compare($smarty.const._PS_VERSION_,'1.7.6','>=')}{literal}
                var parent = $('table#customer_grid_table');
                {/literal}{else}{literal}
                var parent = $('table.table.customer');
                {/literal}{/if}{literal}
                if (parent.length) {
                    var items = parent.find('tbody tr');
                    if (items.length) {
                        items.each(function(){
                            var last_cell = $(this).find('td:last');
                            var checkbox = $(this).find('td:first input[type=checkbox]');
                            if (checkbox.length > 0) {
                                var id_customer = parseInt(checkbox.attr('value'));
                            } else {
                                {/literal}{if version_compare($smarty.const._PS_VERSION_,'1.6','>=')}{literal}
                                var id_customer = parseInt($(this).find('td:first').html());
                                {/literal}{else}{literal}
                                var id_customer = parseInt($(this).find('td.pointer:first').html());
                                {/literal}{/if}{literal}
                            }
                            if (last_cell.length) {
                                {/literal}{if version_compare($smarty.const._PS_VERSION_,'1.6','<')}{literal}
                                    var html = '<a href="#" onclick="customers_list.getCustomerPhoneAndOpenWhatsAppChat(' + id_customer + ');return false;" title="{/literal}{$action|escape:'htmlall':'UTF-8'}{literal}" class="btn btn-default"> <i class="icon-trash"></i> {/literal}{if version_compare($smarty.const._PS_VERSION_,'1.6','<')}{literal}<img src="{/literal}{$this_path_bo|escape:'htmlall':'UTF-8'}{literal}views/img/whatsapp-green.png" width="16px"/>{/literal}{else}{$action|escape:'htmlall':'UTF-8'}{/if}{literal}</a>';
                                    {/literal}{if version_compare($smarty.const._PS_VERSION_,'1.5','<')}{literal}
                                        $(this).find('td:last').append(html);
                                    {/literal}{elseif version_compare($smarty.const._PS_VERSION_,'1.6','<')}{literal}
                                        $(this).find('td:last').append(html);
                                    {/literal}{/if}{literal}
                                {/literal}{else}{literal}
                                    var button_container = last_cell.find('.btn-group'),
                                        button = customers_list.createWhatsAppChatButton(id_customer);
                                    if (last_cell.find('.btn-group-action').length) {
                                        {/literal}{if version_compare($smarty.const._PS_VERSION_,'1.7.6','>=')}{literal}
                                        var whatsapp_icon_big = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="24px" height="24px" style="-ms-transform: rotate(360deg);-webkit-transform: rotate(360deg);transform: rotate(360deg);width: 24px;height: 24px;vertical-align: bottom;" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21c5.46 0 9.91-4.45 9.91-9.91c0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.23-8.24 8.23c-1.48 0-2.93-.39-4.19-1.15l-.3-.17l-3.12.82l.83-3.04l-.2-.32a8.188 8.188 0 0 1-1.26-4.38c.01-4.54 3.7-8.24 8.25-8.24M8.53 7.33c-.16 0-.43.06-.66.31c-.22.25-.87.86-.87 2.07c0 1.22.89 2.39 1 2.56c.14.17 1.76 2.67 4.25 3.73c.59.27 1.05.42 1.41.53c.59.19 1.13.16 1.56.1c.48-.07 1.46-.6 1.67-1.18c.21-.58.21-1.07.15-1.18c-.07-.1-.23-.16-.48-.27c-.25-.14-1.47-.74-1.69-.82c-.23-.08-.37-.12-.56.12c-.16.25-.64.81-.78.97c-.15.17-.29.19-.53.07c-.26-.13-1.06-.39-2-1.23c-.74-.66-1.23-1.47-1.38-1.72c-.12-.24-.01-.39.11-.5c.11-.11.27-.29.37-.44c.13-.14.17-.25.25-.41c.08-.17.04-.31-.02-.43c-.06-.11-.56-1.35-.77-1.84c-.2-.48-.4-.42-.56-.43c-.14 0-.3-.01-.47-.01z" fill="#6c868e"></path></svg>';
                                        var toolbar_list = button_container.prepend('<a href="#" class="btn tooltip-link js-link-row-action dropdown-item float-left icon-whatsapp-bo-list" data-toggle="pstooltip" data-confirm-message data-clickable-row onclick="customers_list.getCustomerPhoneAndOpenWhatsAppChat(' + id_customer + ');return false;" title="{/literal}{$action|escape:'htmlall':'UTF-8'}{literal}" data-placement="top" data-original-title="{/literal}{$action|escape:'htmlall':'UTF-8'}{literal}">' + whatsapp_icon_big + '</a>');
                                        button_container.find('.dropdown-menu').append(button);
                                        {/literal}{else}{literal}
                                        button_container.find('ul.dropdown-menu').append($(document.createElement('li')).attr({'class': 'divider'}));
                                        button_container.find('ul.dropdown-menu').append(button);
                                        {/literal}{/if}{literal}
                                    } else {
                                        button_container.wrap($(document.createElement('div')).addClass('btn-group-action'));
                                        button_container.append(
                                            $(document.createElement('button')).addClass('btn btn-default dropdown-toggle').attr('data-toggle', 'dropdown')
                                                .append($(document.createElement('i')).addClass('icon-caret-down'))
                                        ).append($(document.createElement('ul')).addClass('dropdown-menu').append(button))
                                    }
                                {/literal}{/if}{literal}
                            }
                        });
                    }
                }
            },
            createWhatsAppChatButton: function(id_customer) {
            	var whatsapp_icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1em" height="1em" style="-ms-transform: rotate(360deg);-webkit-transform: rotate(360deg);transform: rotate(360deg);width: 18px;height: 18px;vertical-align: bottom;" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21c5.46 0 9.91-4.45 9.91-9.91c0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.23-8.24 8.23c-1.48 0-2.93-.39-4.19-1.15l-.3-.17l-3.12.82l.83-3.04l-.2-.32a8.188 8.188 0 0 1-1.26-4.38c.01-4.54 3.7-8.24 8.25-8.24M8.53 7.33c-.16 0-.43.06-.66.31c-.22.25-.87.86-.87 2.07c0 1.22.89 2.39 1 2.56c.14.17 1.76 2.67 4.25 3.73c.59.27 1.05.42 1.41.53c.59.19 1.13.16 1.56.1c.48-.07 1.46-.6 1.67-1.18c.21-.58.21-1.07.15-1.18c-.07-.1-.23-.16-.48-.27c-.25-.14-1.47-.74-1.69-.82c-.23-.08-.37-.12-.56.12c-.16.25-.64.81-.78.97c-.15.17-.29.19-.53.07c-.26-.13-1.06-.39-2-1.23c-.74-.66-1.23-1.47-1.38-1.72c-.12-.24-.01-.39.11-.5c.11-.11.27-.29.37-.44c.13-.14.17-.25.25-.41c.08-.17.04-.31-.02-.43c-.06-.11-.56-1.35-.77-1.84c-.2-.48-.4-.42-.56-.43c-.14 0-.3-.01-.47-.01z" fill="#6c868e"></path></svg>';
                {/literal}{if version_compare($smarty.const._PS_VERSION_,'1.7.6','>=')}{literal}
                return $(document.createElement('a')).attr({'href': '#', 'title':'{/literal}{$action|escape:'htmlall':'UTF-8'}{literal}', 'onclick': 'customers_list.getCustomerPhoneAndOpenWhatsAppChat(' + id_customer + ')', 'class': 'btn tooltip-link dropdown-item icon-whatsapp-bo'}).html(whatsapp_icon + ' ' + customers_list.tr('{/literal}{$action|escape:'htmlall':'UTF-8'}{literal}'));
                {/literal}{else}{literal}
                return $(document.createElement('li')).append($(document.createElement('a')).attr({'href': '#', 'title':'{/literal}{$action|escape:'htmlall':'UTF-8'}{literal}', 'onclick': 'customers_list.getCustomerPhoneAndOpenWhatsAppChat(' + id_customer + ')'}).html('<i class="icon-whatsapp"></i> ' + customers_list.tr('{/literal}{$action|escape:'htmlall':'UTF-8'}{literal}')));
                {/literal}{/if}{literal}
            },
            tr: function(str) {
                return str;
            },
            getCustomerPhoneAndOpenWhatsAppChat: function(id_customer) {
                $.ajax({
                    type: 'POST',
                    url: '{/literal}{$whatsappchat_admincontroller|escape:"quotes":"UTF-8"}{literal}',
                    async: true,
                    cache: false,
                    dataType : "json",
                    data: 'method=getCustomerMobilePhone&id_customer=' + id_customer,
                    success: function(jsonData)
                    {
                        if (jsonData.whatsappchat_response.code == 'OK') {
                            window.open(jsonData.whatsappchat_response.url, "sharer", "toolbar=0,status=0,width=660,height=725");
                        } else if (jsonData.whatsappchat_response.code == 'NOK') {
                            alert(jsonData.whatsappchat_response.msg);
                        }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        console.log(XMLHttpRequest);
                        if (textStatus != 'abort') {
                            alert("TECHNICAL ERROR: unable to open WhatsApp chat window \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
                        }
                    }
                });
            },
            openWhatsAppChat: function() {
                window.open("{/literal}{$url|escape:'quotes':'UTF-8'}{literal}", "sharer", "toolbar=0,status=0,width=660,height=725");
            },
        }
        $(function(){
            customers_list.init();
        });
        if (document.URL.indexOf('id_customer') > 0 || (document.URL.indexOf('/sell/customers/') > 0 && document.URL.indexOf('/view') > 0)) {
            $(document).ready(function(){
                {/literal}{if $show_button !== false}{literal}
                	{/literal}{if version_compare($smarty.const._PS_VERSION_,'1.7.6','>=')}{literal}
                		var whatsapp_icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="20px" height="20px" style="-ms-transform: rotate(360deg);-webkit-transform: rotate(360deg);transform: rotate(360deg);width: 20px;height: 20px;vertical-align: bottom;" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21c5.46 0 9.91-4.45 9.91-9.91c0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.23-8.24 8.23c-1.48 0-2.93-.39-4.19-1.15l-.3-.17l-3.12.82l.83-3.04l-.2-.32a8.188 8.188 0 0 1-1.26-4.38c.01-4.54 3.7-8.24 8.25-8.24M8.53 7.33c-.16 0-.43.06-.66.31c-.22.25-.87.86-.87 2.07c0 1.22.89 2.39 1 2.56c.14.17 1.76 2.67 4.25 3.73c.59.27 1.05.42 1.41.53c.59.19 1.13.16 1.56.1c.48-.07 1.46-.6 1.67-1.18c.21-.58.21-1.07.15-1.18c-.07-.1-.23-.16-.48-.27c-.25-.14-1.47-.74-1.69-.82c-.23-.08-.37-.12-.56.12c-.16.25-.64.81-.78.97c-.15.17-.29.19-.53.07c-.26-.13-1.06-.39-2-1.23c-.74-.66-1.23-1.47-1.38-1.72c-.12-.24-.01-.39.11-.5c.11-.11.27-.29.37-.44c.13-.14.17-.25.25-.41c.08-.17.04-.31-.02-.43c-.06-.11-.56-1.35-.77-1.84c-.2-.48-.4-.42-.56-.43c-.14 0-.3-.01-.47-.01z" fill="#fff"></path></svg>';
                		//var toolbar = $('.card-header .material-icons:contains("edit"):first').parent().parent().append('<a href="#" class="tooltip-link float-right icon-whatsapp-bo-edit" data-toggle="pstooltip" onclick="customers_list.openWhatsAppChat();return false;" title="{/literal}{$action_whatsappchat|escape:'htmlall':'UTF-8'}{literal}" data-placement="top" data-original-title="{/literal}{$action_whatsappchat|escape:'htmlall':'UTF-8'}{literal}">' + whatsapp_icon + '</a>');
                        var id_customer = 0;
                        if (document.URL.indexOf('id_customer') > 0) {
                            id_customer = '{/literal}{$smarty.get.id_customer|default:0|escape:'htmlall':'UTF-8'}{literal}'
                        }
                        //var toolbar_list = $('#customer_grid_table .material-icons:contains("edit")').parent().parent().prepend('<a href="#" class="btn tooltip-link js-link-row-action dropdown-item float-left icon-whatsapp-bo-list" data-toggle="pstooltip" data-confirm-message data-clickable-row onclick="customers_list.getCustomerPhoneAndOpenWhatsAppChat(' + id_customer + ');return false;" title="{/literal}{$action_whatsappchat|escape:'htmlall':'UTF-8'}{literal}" data-placement="top" data-original-title="{/literal}{$action_whatsappchat|escape:'htmlall':'UTF-8'}{literal}">' + whatsapp_icon + '</a>');
                        var ref_button = $('div.customer-personal-informations-card h3.card-header a').last();
                        var su_button = ref_button.clone();
                        su_button.attr('data-original-title', '{/literal}{$action_whatsappchat|escape:'htmlall':'UTF-8'}{literal}');
                        su_button.removeClass('float-right').addClass('btn btn-primary').html(whatsapp_icon + '{/literal}{$action_whatsappchat|escape:'htmlall':'UTF-8'}{literal}');
                        su_button.attr('href', '#');
                        su_button.attr('onclick', "customers_list.getCustomerPhoneAndOpenWhatsAppChat('{/literal}{$wa_id_customer|escape:'htmlall':'UTF-8'}{literal}');return false;");
                        su_button.attr('target', '_blank');
                        su_button.insertAfter(ref_button);
                        $('.js-superuser-order-view-page').append(html);
                    {/literal}{elseif version_compare($smarty.const._PS_VERSION_,'1.6','>=')}{literal}
                        var toolbar = $('ul#toolbar-nav').prepend('<li><a id="page-header-desc-customer-whatsapp" class="toolbar_btn" href="#" onclick="customers_list.openWhatsAppChat();return false;" title="{/literal}{$action_whatsappchat|escape:'htmlall':'UTF-8'}{literal}"><i class="icon-whatsapp bo"></i><div>{/literal}{$action_whatsappchat|escape:'htmlall':'UTF-8'}{literal}</div></a></li>');
                    {/literal}{/if}{literal}
                    var html = '<a class="btn btn-default" href="#" onclick="customers_list.openWhatsAppChat();return false;" ><i class="icon-whatsapp bo"></i> {/literal}{$action_whatsappchat|escape:'htmlall':'UTF-8'}{literal}</a>';
                    {/literal}{if version_compare($smarty.const._PS_VERSION_,'1.5','<')}{literal}
                        $("#content div.col-lg-7 .panel:first .hidden-print:first").prepend(html);
                    {/literal}{elseif version_compare($smarty.const._PS_VERSION_,'1.6','>=')}{literal}
                        $("#content div.col-lg-7 .panel:first .hidden-print:first").prepend(html);
                    {/literal}{else}{literal}
                        html = '<a class="toolbar_btn" href="#" onclick="customers_list.openWhatsAppChat();return false;" ><span class="icon-whatsapp bo"><img src="{/literal}{$this_path_bo|escape:'htmlall':'UTF-8'}{literal}views/img/whatsapp-32x32.png" /></span> <div>{/literal}{$action_whatsappchat|escape:'htmlall':'UTF-8'}{literal}</div></a>';
                        $('ul.cc_button').prepend('<li>' + html + '</li>');
                    {/literal}{/if}{literal}
                {/literal}{/if}{literal}
            });
        }
    </script>
    {/literal}
{/if}
