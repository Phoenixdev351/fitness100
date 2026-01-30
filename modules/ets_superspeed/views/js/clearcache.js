/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
 $(document).ready(function(){
    if($('#product_form_save_duplicate_btn').length)
    {
        var id_product = $('#form_id_product').val();
        $('#product_form_save_duplicate_btn').before('<button class="btn btn-outline-secondary btn-ets-sp-clear-cache hidden-xs uppercase ml-3" type="button" data-id="'+id_product+'" data-page="product">'+Clear_cache+'</button>')
    }
    if($('a.dropdown-item.duplicate.js-btn-save').length)
    {
        $('a.dropdown-item.duplicate.js-btn-save').before('<a class="dropdown-item btn-ets-sp-clear-cache js-btn-save" href="#">'+Clear_cache+'</a>')
    }
    if($('button[name="submitAddproductAndStay"]').length)
    {
        var id_product = $('#product_form input[name="id_product"]').val();
        if(id_product >0)
            $('button[name="submitAddproductAndStay"]').after(' <button class="btn btn-default btn-ets-sp-clear-cache pull-right" type="button" data-id="'+id_product+'" data-page="product"><i class="process-icon-delete"></i> '+Clear_cache+'</button>');
    }
    if($('form[name="category"]').length)
    {
        $('form[name="category"] .card-footer .btn-outline-secondary').after('<button class="btn btn-outline-secondary btn-ets-sp-clear-cache hidden-xs uppercase ml-3" type="button" data-id="0" data-page="category">'+Clear_cache+'</button>');
    }
    if($('button[name="submitAddcategoryAndBackToParent"]').length)
    {
        var id_category = $('#category_form input[name="id_category"]').val();
        if(id_category >0)
            $('button[name="submitAddcategoryAndBackToParent"]').after(' <button class="btn btn-default btn-ets-sp-clear-cache pull-right" type="button" data-id="'+id_category+'" data-page="category"><i class="process-icon-delete"></i> '+Clear_cache+'</button>');
    }
    if($('form[name="cms_page"]').length)
    {
        $('form[name="cms_page"] .card-footer .btn-outline-secondary').after('<button class="btn btn-outline-secondary btn-ets-sp-clear-cache hidden-xs uppercase ml-3" type="button" data-id="0" data-page="cms">'+Clear_cache+'</button>');
    }
    if($('form[name="manufacturer"]').length)
    {
        $('form[name="manufacturer"] .card-footer .btn-outline-secondary').after('<button class="btn btn-outline-secondary btn-ets-sp-clear-cache hidden-xs uppercase ml-3" type="button" data-id="0" data-page="manufacturer">'+Clear_cache+'</button>');
    }
    if($('#manufacturer_form_submit_btn').length)
    {
        var id_manufacturer = $('#manufacturer_form input[name="id_manufacturer"]').val();
        if(id_manufacturer>0)
            $('#manufacturer_form_submit_btn').after(' <button class="btn btn-default btn-ets-sp-clear-cache pull-right" type="button" data-id="'+id_manufacturer+'" data-page="manufacturer"><i class="process-icon-delete"></i> '+Clear_cache+'</button>');
    }
    if($('form[name="supplier"]').length)
    {
        $('form[name="supplier"] .card-footer .btn-outline-secondary').after('<button class="btn btn-outline-secondary btn-ets-sp-clear-cache hidden-xs uppercase ml-3" type="button" data-id="0" data-page="supplier">'+Clear_cache+'</button>');
    }
    if($('#supplier_form_cancel_btn').length)
    {
        var id_supplier = $('#supplier_form input[name="id_supplier"]').val();
        if(id_supplier>0)
            $('#supplier_form_cancel_btn').after(' <button class="btn btn-default btn-ets-sp-clear-cache" type="button" data-id="'+id_supplier+'" data-page="supplier"><i class="process-icon-delete"></i> '+Clear_cache+'</button>');
    }
    if($('button[name="savePost"]').length)
    {
        var id_post = $('#configuration_form input[name="id_post"]').val();
        if(id_post>0)
        {
            $('button[name="savePost"]').after(' <button class="btn btn-default btn-ets-sp-clear-cache pull-right" type="button" data-id="'+id_post+'" data-page="blog"><i class="process-icon-delete"></i> '+Clear_cache+'</button>');
        }
    }
    if($('button[name="saveCategory"]').length)
    {
        var id_category = $('#configuration_form input[name="id_category"]').val();
        if(id_category>0)
        {
            $('button[name="saveCategory"]').after(' <button class="btn btn-default btn-ets-sp-clear-cache pull-right" type="button" data-id="'+id_category+'" data-page="blog"><i class="process-icon-delete"></i> '+Clear_cache+'</button>');
        }
    }
    if($('form[name="meta"]').length)
    {
        var meta_page = $('#meta_page_name').val().replace('-','');
        if(meta_page=='index' || meta_page=='contact' || meta_page=='newproducts' || meta_page=='bestsales' || meta_page=='pricesdrop' || meta_page=='sitemap')
        {
            $('form[name="meta"] .card-footer .btn-outline-secondary').after('<button class="btn btn-outline-secondary btn-ets-sp-clear-cache hidden-xs uppercase ml-3" type="button" data-id="0" data-page="'+meta_page+'">'+Clear_cache+'</button>');
        }
    }
    $(document).on('click','.btn-ets-sp-clear-cache',function(){
        var page = $(this).data('page');
        var id_object = $(this).data('id');
        $.ajax({
            url: '',
            data: {
                submitDeleteCachePage:1,
                page: page,
                id_object : id_object,
            },
            type: 'post',
            dataType: 'json',
            success: function(json){
                if(json.success)
                {
                    $.growl.notice({ message: json.success });
                }
                if(json.errors)
                    $.growl.error({message:json.errors});
            },
            error: function(xhr, status, error)
            {
            }
        });
    });
 });
 