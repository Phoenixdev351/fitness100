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
                    {block name='facet_item_slider'}
                      {foreach from=$facet.filters item="filter"}
                        <ul id="facet_{$_expand_id}"
                          class="faceted-slider"
                          data-slider-min="{$facet.properties.min}"
                          data-slider-max="{$facet.properties.max}"
                          data-slider-id="{$_expand_id}"
                          data-slider-values="{$filter.value|@json_encode}"
                          data-slider-unit="{$facet.properties.unit}"
                          data-slider-label="{$facet.label}"
                          data-slider-specifications="{$facet.properties.specifications|@json_encode}"
                          data-slider-encoded-url="{$filter.nextEncodedFacetsURL}"
                        >
                          <li>
                            <p id="facet_label_{$_expand_id}">
                              {$filter.label}
                            </p>

                            <div id="slider-range_{$_expand_id}"></div>
                          </li>
                        </ul>
                      {/foreach}
                    {/block}