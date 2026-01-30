{*
 * Facebook Conversion Pixel Tracking Plus
 *
 * NOTICE OF LICENSE
 *
 * @author    Pol Rué
 * @copyright Smart Modules 2014
 * @license   One time purchase Licence (You can modify or resell the product but just one time per licence)
 * @version 2.3.3
 * @category Marketing & Advertising
 * Registered Trademark & Property of smart-modules.com
 *
 * ***************************************************
 * *     Facebook Conversion Trackcing Pixel Plus    *
 * *          http://www.smart-modules.com           *
 * *                     V 2.3.3                     *
 * ***************************************************
 *
*}

<script type='text/javascript'>
$(document).ready(function() {
    var tmpval = '0.0';
    $('#pixel_code').bind('input propertychange', function()
    {
        var text = $(this).val();
        var p= text.indexOf('?ev=')+4;
        var q= text.indexOf('&amp;');
        text = text.substr(text.indexOf('?ev=')+4,text.indexOf('&amp;')-(text.indexOf('?ev=')+4))
        if (text != '' && !isNaN(text))
        {
            $('#id_pixel').val(text);
        }
        else
        {
            text=get_id_pixel(text);
        }
    });
    {if !$old_ps}
        // Initialize Form Extra Options
        if ($('#pixel_type').val() != 1)
        {
            $('#pixel_extras').parent().parent().hide();
            $('#pixel_extras_type').parent().parent().hide();
            if ($('#pixel_type').val() == 1)
            {
                tmpval = $('#pixel_value').val();
                $('#pixel_value').val('{l s='Automatic Value' mod='facebookconversiontrackingplus' js=1}');
                $('#pixel_value').attr('disabled','disabled');
                $('#pixel_value').disabled = true;
            }
            if ($('#pixel_extras_type').val() == 1 || $('#pixel_extras_type').val() == 6)
            {
                $('#pixel_extras').parent().parent().hide();
            }
        }
        $('#pixel_value').focusout(function() {
            var value;
            value = $(this).val();
            value = $(this).val().split(',').join('.');
            value = parseFloat(value).toFixed(2);
            if (isNaN(value) && $('#pixel_type').val() != 1)
            {
                alert('{l s='Error: Value must be a number' mod='facebookconversiontrackingplus' js=1}');
                $(this).val('0.0');
            }
            else
                $(this).val(value);
        });
        update_extras($('#pixel_extras_type'));
        $('#pixel_extras_type').change(function()
        {
                 update_extras($(this));
        });
            function update_extras(element) {
                 if (element.val() == 1 || element.val() == 6)
            {
                $('#pixel_extras').parent().parent().hide();
            }
            else if (element.val() == 4)
            {
                $('#pixel_extras').parent().parent().show();
                $('#pixel_extras').parent().prev().html('{l s='Enter the ID of the CMS you want to track' mod='facebookconversiontrackingplus' js=1}');
            }
            else
            {
                $('#pixel_extras').parent().parent().show();
                $('#pixel_extras').parent().prev().html('{l s='Enter the ID of the Key page you want to track' mod='facebookconversiontrackingplus' js=1}');
            }
        }
    {else}
    // PS < 1.6
    //Initialize the Pixel Extras
        update_extras($('#pixel_extras_type'));
        $('#pixel_extras_type').change(function()
        {
                 update_extras($(this));
        });
        function update_extras(element) {
            if (element.val() == 1 || element.val() == 5) {
                //$('#pixel_extras').parent().parent().hide();
                $('#pixel_extras').parent().hide();
                $('#pixel_extras').parent().prev().hide();
            }
            else { 
                $('#pixel_extras').parent().show();
                $('#pixel_extras').parent().prev().show();
                $('#pixel_extras').parent().prev().html('{l s='Enter the ID of the Key page you want to track' mod='facebookconversiontrackingplus' js=1}');
            }
        }
     {/if}
});
</script>';