<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
/**
 * Class CartRule.
 */
class CartRule extends CartRuleCore
{
    /**
     * Check if this CartRule can be applied.
     *
     * @param Context $context Context instance
     * @param bool $alreadyInCart Check if the voucher is already on the cart
     * @param bool $display_error Display error
     * @param bool $check_carrier
     * @param bool $useOrderPrices
     *
     * @return bool|mixed|string
     */
    /*
    * module: affiliates
    * date: 2023-03-03 16:04:52
    * version: 2.3.0
    */
    public function checkValidity(
        Context $context,
        $alreadyInCart = false,
        $display_error = true,
        $check_carrier = true,
        $useOrderPrices = false
    ) {
        if (Module::isInstalled('affiliates')) {
            if (!CartRule::isFeatureActive()) {
                return false;
            }
            if ((Tools::getIsset('addDiscount') || Tools::getIsset('submitAddDiscount')) &&
                Tools::getIsset('discount_name')) {
                $affiliates = Module::getInstanceByName('affiliates');
                $affiliateVouchers = $affiliates->getAffiliateVouchers();
                $groups = Customer::getGroupsStatic($context->cart->id_customer);
                if ($affiliates->isAffiliateCustomer($context->cart->id_customer) &&
                    isset($affiliateVouchers) && $affiliateVouchers && in_array($this->id, $affiliateVouchers) &&
                    in_array(Configuration::get('ID_AFFILIATE_GROUP'), $groups)) {
                    return (!$display_error) ? false : $affiliates->l('You can not use this voucher.');
                }
            }
        }
        return parent::checkValidity(
            $context,
            $alreadyInCart,
            $display_error,
            $check_carrier,
            $useOrderPrices
        );
    }
}
