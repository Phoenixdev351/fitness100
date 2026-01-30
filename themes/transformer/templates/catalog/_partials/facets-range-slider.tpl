{**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
                <div class="st-range-top st-range-bar {if $with_inputs && $facet.widgetType!='rangeslider'} with_inputs {elseif $disable_range_text} space_for_tooltips {/if}">
                  {if $with_inputs && $facet.widgetType!='rangeslider'}
                  <input class="st_lower_input form-control" />
                  {if !$vertical}<div class="value-split">-</div><input class="st_upper_input form-control" />{/if}
                  {elseif !$disable_range_text}
                  <span class="value-lower"></span>
                  <span class="value-split">-</span>
                  <span class="value-upper"></span>
                  {/if}
                </div>
                <div class="st_range_inner">
                <div class="st-range" data-jiazhong="{if $facet.widgetType=='rangeslider'}rangeslider{else}{$facet.type}{/if}" data-url="{$facet.properties.url}" data-min="{$facet['properties']['min']}" data-max="{$facet['properties']['max']}" data-lower="{if isset($facet['properties']['lower'])}{$facet['properties']['lower']}{else}{$facet['properties']['min']}{/if}" data-upper="{if isset($facet['properties']['upper'])}{$facet['properties']['upper']}{else}{$facet['properties']['max']}{/if}" data-values="{if isset($facet['properties']['values'])}{','|implode:$facet['properties']['values']}{/if}" data-prefix="{if isset($facet['properties']['prefix'])}{$facet['properties']['prefix']}{/if}" data-suffix="{if isset($facet['properties']['suffix'])}{$facet['properties']['suffix']}{/if}"></div>
                </div>
                <div class="st-range-bottom st-range-bar {if $with_inputs && $facet.widgetType!='rangeslider'} with_inputs {/if}">
                  {if $with_inputs && $facet.widgetType!='rangeslider' && $vertical}
                  <input class="st_upper_input form-control" />
                  {/if}
                </div>