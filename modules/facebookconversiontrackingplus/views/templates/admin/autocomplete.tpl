{*
 * Facebook Products Feed catalogue export for Prestashop
 *
 * NOTICE OF LICENSE
 *
 * @author    Pol Rué
 * @copyright Smart Modules 2016
 * @license   One time purchase Licence (You can modify or resell the product but just one time per licence)
 * @version 2.3.3
 * @category Advertising & Marketing
 * Registered Trademark & Property of smart-modules.com
 *
 * ****************************************
 * *        Facebook Products Feed        *
 * *   http://www.smart-modules.com       *
 * *               V 2.3.3                *
 * ****************************************
*}
<script>
    var taxonomyList = {$google_categories nofilter}; {* JSON Object *}
    var gc_init = false;
</script>
<!-- {$gpt_url|escape:'htmlall':'UTF-8'} -->
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
   <script type="text/javascript">
       if (typeof taxonomyList === 'undefined') {
            var taxonomyList = '';
            $(document).ready(function() {
                var url = '{$gpt_url|escape:'htmlall':'UTF-8'}';
                url = url.split('?');
                var data = url[1];
                url = url[0];
                {literal}
                data = JSON.parse('{"' + decodeURI(data).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g,'":"') + '"}');
                {/literal}
                if (location.protocol === 'https:' && url.indexOf('https') == -1) {
                    // page is secure
                    url = url.replace('http:','https:');
                }
                console.log(url);
                getTaxonomyList(url, data, 1);
                /*jQuery.getJSON(testurl, function( data ) {
                    console.log("GeT Json: "+data.length);
                    taxonomyList = data;
                    enableAutocomplete();
                });*/
                function getTaxonomyList(url, data, tries) {
                    jQuery.ajax({
                        dataType: "json",
                        url: url,
                        headers: { 'Content-Type':'application/x-www-form-urlencoded' },
                        data: data,
                    }).done(function (data) {
                        console.log('Sucess: Get Google Categories');
                        taxonomyList = data;
                        enableAutocomplete();
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        if (tries > 0) {
                            if (url.indexOf('www') == -1) {
                                url = url.replace('://', '://www.');
                            } else if (url.indexOf('www') < 10) {
                                url = url.replace('://www.', '://');
                            }
                            //console.log('retry');
                            //console.log(url);
                            getTaxonomyList(url, data, tries-1);
                        } else {
                            console.log('Error could not get the Google Categories');
                        }
                    });
                }
            });
        } else {
            enableAutocomplete();
        }
        function enableAutocomplete() {
            console.log('Enable Autocomplete');
            console.log(taxonomyList.length);
            $('#massiveupdate').autocomplete({
                source: taxonomyList,
                minLength: 0,
                delay: 500,
                select: function( event, ui ) {
                    $("#massiveupdate").val( ui.item.value + ' - ' + ui.item.label );
                    $("#massiveupdate_id").val( ui.item.value );
                    return false;
                }
            });
            $('.tree_cat_input').on("focus", function(){
                $(this).autocomplete({
                    source: taxonomyList,
                    minLength: 0,
                    delay: 500,
                    select: function( event, ui ) {
                        $(this).val( ui.item.value + ' - ' + ui.item.label );
                        $(this).next('input').val( ui.item.value );
                        return false;
                    }
                });
            });
        }
</script>
<style>
.ui-autocomplete {
    position:absolute;
    z-index: 100 !important;
    overflow-x:hidden;
    max-width:50%;
    max-height:300px;
    overflow-y:auto;
    padding:0;
    list-style:none;
}
.ui-menu-item {
    padding:0 10px;
}
</style>
