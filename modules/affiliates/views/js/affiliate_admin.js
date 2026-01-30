/*
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
 */

$(document).ready(function(){
    eraseClass();
    var default_val = $('#reward_value').val();
    $('input[name="reward_value_'+ $('#reward_type').val() +'"]').val(default_val);
    toggleValue($('#reward_type').val());
})
$(document).on('change', '#reward_type', function(){
    toggleValue($('#reward_type').val());
})

function toggleValue(val)
{
    if (parseInt(val) == 0)
    {
        if (parseFloat(_PS_VERSION_) < 1.6)
        {

            $('#pc-value').parent().prev('label').hide();
            $('#pc-value').parent().hide();
            $('#amount-value').parent().prev('label').show();
            $('#amount-value').parent().show();
        }
        else
        {
            $('#pc-value').parent().parent().parent().hide();
            $('#amount-value').parent().parent().parent().show();
        }
    }
    else if (parseInt(val) == 1)
    {
        if (parseFloat(_PS_VERSION_) < 1.6)
        {
            $('#pc-value').parent().prev('label').show();
            $('#pc-value').parent().show();
            $('#amount-value').parent().prev('label').hide();
            $('#amount-value').parent().hide();
        }
        else
        {
            $('#pc-value').parent().parent().parent().show();
            $('#amount-value').parent().parent().parent().hide();
        }
    }
}

function eraseClass() {
    $('#form-affiliate').find('table').removeClass('affiliate');
}