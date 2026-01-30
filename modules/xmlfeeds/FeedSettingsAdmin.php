<?php
/**
 * 2010-2022 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2022 Bl Modules
 * @license
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class FeedSettingsAdmin extends Xmlfeeds
{
    protected $langId = 1;

    public function __construct($langId = 1)
    {
        parent::__construct();

        $this->langId = $langId;
    }

    public function getFilterDateTypes()
    {
        return array(
            OrderSettings::FILTER_DATE_NONE => 'None',
            OrderSettings::FILTER_DATE_TODAY => 'Today',
            OrderSettings::FILTER_DATE_YESTERDAY=> 'Yesterday',
            OrderSettings::FILTER_DATE_THIS_WEEK => 'Current week',
            OrderSettings::FILTER_DATE_THIS_MONTH => 'Current month',
            OrderSettings::FILTER_DATE_THIS_YEAR => 'Current year',
            OrderSettings::FILTER_DATE_CUSTOM_DAYS => 'Custom days',
            OrderSettings::FILTER_DATE_DATE_RANGE => 'Date range',
        );
    }

    public function manufacturersList($active = false)
    {
        $class = 'ManufacturerCore';

        if (!class_exists('ManufacturerCore', false)) {
            $class = 'Manufacturer';
        }

        $manufacturers = $class::getManufacturers();

        $this->smarty->assign([
            'manufacturers' => $manufacturers,
            'activeList' => explode(',', $active),
        ]);

        return $this->displaySmarty('views/templates/admin/element/manufacturerList.tpl');
    }

    public function supplierList($active = false)
    {
        $class = 'SupplierCore';

        if (!class_exists('SupplierCore', false)) {
            $class = 'Supplier';
        }

        $supplier = $class::getSuppliers();

        $this->smarty->assign([
            'suppliers' => $supplier,
            'activeList' => explode(',', $active),
        ]);

        return $this->displaySmarty('views/templates/admin/element/supplierList.tpl');
    }

    public function getOrderStatusList($active = '')
    {
        $states = Db::getInstance()->ExecuteS(
            'SELECT sl.id_order_state, sl.name, s.color
            FROM  '._DB_PREFIX_.'order_state_lang sl
            LEFT JOIN '._DB_PREFIX_.'order_state s ON
            s.id_order_state = sl.id_order_state
            WHERE sl.id_lang = "'.(int)$this->langId.'"
            ORDER BY sl.name ASC'
        );

        if (empty($states)) {
            return '';
        }

        $this->smarty->assign([
            'states' => $states,
            'activeList' => explode(',', $active),
        ]);

        return $this->displaySmarty('views/templates/admin/element/orderStatusList.tpl');
    }

    public function getOrderPaymentsList($active = '')
    {
        $paymentModules = Db::getInstance()->ExecuteS(
            'SELECT DISTINCT m.`name`
            FROM '._DB_PREFIX_.'module m
            LEFT JOIN '._DB_PREFIX_.'hook_module hm ON
            hm.`id_module` = m.`id_module`
            LEFT JOIN `'._DB_PREFIX_.'hook` h ON
            hm.`id_hook` = h.`id_hook`
            WHERE (h.`name` = "paymentOptions" OR h.`name` = "Payment")
            ORDER BY m.`name` DESC'
        );

        $paymentModulesFromOrders = Db::getInstance()->ExecuteS(
            'SELECT o.`module` AS `name`, o.`payment` AS displayName
            FROM '._DB_PREFIX_.'orders o
            GROUP BY o.`module`
            ORDER BY o.`payment` DESC'
        );

        if (empty($paymentModules) && empty($paymentModulesFromOrders)) {
            return '';
        }

        if (!empty($paymentModules)) {
            foreach ($paymentModules as $id => $p) {
                $paymentModules[$id]['displayName'] = str_replace('ps_', '', $p['name']);
                try {
                    $paymentModules[$id]['displayName'] = Module::getModuleName($p['name']);
                } catch (Exception $e) {
                }
            }
        }

        if (!empty($paymentModulesFromOrders)) {
            foreach ($paymentModulesFromOrders as $p) {
                foreach ($paymentModules as $pm) {
                    if ($p['name'] == $pm['name']) {
                        continue 2;
                    }
                }

                $paymentModules[] = $p;
            }
        }

        $this->smarty->assign([
            'paymentModules' => $paymentModules,
            'activeList' => explode(',', $active),
        ]);

        return $this->displaySmarty('views/templates/admin/element/orderPaymentsList.tpl');
    }

    public function getFilterAttributesHtml($s, $isWithout = false)
    {
        if (empty($s['feed_type'])) {
            return '';
        }

        $container = 'div';
        $label = 'label';
        $styleC = 'class';
        $input = 'input';
        $onlyWithAttributesHtml = '';
        $groups = AttributeGroupCore::getAttributesGroups($this->langId);
        $onlyWithAttributesActive = explode(',', $s['only_with_attributes']);
        $onlyWithoutAttributesActive = explode(',', $s['only_without_attributes']);

        foreach ($groups as $g) {
            $attributes = AttributeGroupCore::getAttributes($this->langId, $g['id_attribute_group']);
            $onlyWithAttributesHtml .= '<'.$container.' '.$styleC.'="blmod_mb10"><'.$container.' '.$styleC.'="attribute-group-title">'.$g['name'].'</div>';

            if (empty($attributes)) {
                continue;
            }

            foreach ($attributes as $a) {
                if (empty($a['id_attribute'])) {
                    continue;
                }

                $onlyWithAttributesHtml .= '<'.$label.' '.$styleC.'="attribute-list"><'.$input.' type="checkbox" name="only_with_attributes[]" value="'.$a['id_attribute'].'" ';
                $onlyWithAttributesHtml .= (in_array($a['id_attribute'], $onlyWithAttributesActive) ? 'BLMOD_CHECKED_WITH_' : '');
                $onlyWithAttributesHtml .= (in_array($a['id_attribute'], $onlyWithoutAttributesActive) ? 'BLMOD_CHECKED_WITHOUT_' : '');
                $onlyWithAttributesHtml .= '/> '.$a['name'].'</'.$label.'>';
            }

            $onlyWithAttributesHtml .= '<'.$container.' '.$styleC.'="blmod_cb"></'.$container.'></'.$container.'>';
        }

        $onlyWithoutAttributesHtml = str_replace('only_with_attributes[]', 'only_without_attributes[]', $onlyWithAttributesHtml);

        if ($isWithout) {
            return str_replace('BLMOD_CHECKED_WITHOUT_', 'checked', str_replace('BLMOD_CHECKED_WITH_', '', $onlyWithoutAttributesHtml));
        }

        return str_replace('BLMOD_CHECKED_WITH_', 'checked', str_replace('BLMOD_CHECKED_WITHOUT_', '', $onlyWithAttributesHtml));
    }
}
