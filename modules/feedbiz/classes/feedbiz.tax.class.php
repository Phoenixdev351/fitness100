<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.Biz, Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.Biz, Ltd. is strictly forbidden.
 * In order to obtain a license, please contact us: contact@feed.biz
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.Biz, Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Feed.Biz, Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Feed.Biz, Ltd. a l'adresse: contact@feed.biz
 * ...........................................................................
 *
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2022 Feed.Biz - Hong Kong - Head Quarters - Room 1408, 14/F,
 *            Tak Shing House - Theatre Lane - 20 des Voeux Road Central, -Central, Hong Kong
 * @license   Commercial license
 * Support by mail  :  support@feed.biz
 */

class FeedbizTax
{
    public $tax_rates = array();

    const ID_TAX_RULES_GROUP = 1;

    public static function getDefaultStoreTaxCalculator($type)
    {
        $id_country = (int)Configuration::get('PS_COUNTRY_DEFAULT');
        $cache_id = 'store-'.(int)$id_country.'-'. 0 .'-'. 0 .'-'.$type;

        $taxCalculator = null;
        //check if PS provides cache
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            if (Cache::isStored($cache_id)) {
                $taxCalculator = Cache::retrieve($cache_id);
            }
        }

        if (version_compare(_PS_VERSION_, '1.5', '>=') && $taxCalculator == null) {
            $taxes = array();
            $rows = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'tax_rule`
			WHERE `id_country` = '.(int)$id_country.'
			AND `id_tax_rules_group` = '.(int)$type.'
			AND `id_state` IN (0)
			ORDER BY `zipcode_from` DESC, `zipcode_to` DESC, `id_state` DESC, `id_country` DESC');
            $behavior = 0;
            $first_row = true;

            foreach ($rows as $row) {
                $tax = new Tax((int)$row['id_tax']);
                $taxes[] = $tax;
                // the applied behavior correspond to the most specific rules
                if ($first_row) {
                    $behavior = $row['behavior'];
                    $first_row = false;
                }
                if ($row['behavior'] == 0) {
                    break;
                }
            }
            $taxCalculator = new TaxCalculator($taxes, $behavior);

            //check if PS provides cache
            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                Cache::store($cache_id, $taxCalculator);
            }
        }

        return $taxCalculator;
    }
}
