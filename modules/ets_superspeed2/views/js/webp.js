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
function ssCheckSupportsWebP(callback) {
    const webP = new Image();
    webP.src = ss_link_image_webp;
    webP.onload = webP.onerror = function () {
        callback(webP.height >10);
    };
}

$(document).ready(function(){
    ssCheckSupportsWebP(function (supported) {
        if (!supported) {
            const imgElements = $('img');
            imgElements.each(function(){
                $(this).attr('src',$(this).attr('src').replaceAll('.webp','.jpg'));
                if($(this).attr('data-full-size-image-url'))
                    $(this).attr('data-full-size-image-url',$(this).attr('data-full-size-image-url').replaceAll('.webp','.jpg'));
            });
        }
    });
});