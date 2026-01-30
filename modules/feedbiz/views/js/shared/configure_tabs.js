/** NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SARL SMC
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: support.gosport@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe SMC
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la SARL SMC est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 * ...........................................................................
 *
 * @author     debuss-a
 * @copyright  Copyright (c) 2011-2022 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
 * @package    CommonServices
 * @license   Commercial license
 */

$(document).ready(function() {
    
    /**
     * Display the last tab seen by the user.
     */
    function                            setCurrentTab()
    {
        var current_tab = $('#selected_tab').val();

        if (typeof(current_tab) !== 'undefined' && current_tab.length)
            $('#' + current_tab).click();
        else
            $('div[id^="conf-"]:first').show();
    }
    
    /*
     * Tabs
     */
    var confDiv = $('div[id^="conf-"]');
    var confTab = $('li[id^="menu-"]');
    
    if(typeof $('#is_feedbiz') !== 'undefined' && $('#is_feedbiz').val() == 1){
        // not use this event
    }else{
        $('li[id^="menu-"], li[id^="menudiv-"]').click(function() {
            var divName = 'conf-' + $(this).attr('id').match('^(.*)-(.*)$')[2];
            var confVisibleDiv = $('div[id^="conf-"]:visible');

            if (confVisibleDiv.attr('id') === divName)
                return (false);
            confTab.removeClass('selected');
            confDiv.fadeOut('fast');
            $(this).addClass('selected');
            $('#' + divName).delay(195).fadeIn('slow');
            $('input[name="current_tab"]').val('#' + divName);
            $('input[name="selected_tab"]').val($(this).attr('id'));
        });
        setCurrentTab();
    }
});