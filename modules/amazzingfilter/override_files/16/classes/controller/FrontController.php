<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 *  INFO: This override is used for applying custom sorting and custom number of products per page
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class FrontController extends FrontControllerCore
{
    public function pagination($total_products = null)
    {
        if (empty($this->context->custom_pagination)) {
            return parent::pagination($total_products);
        }
    }

    public function productSort()
    {
        if (empty($this->context->custom_sorting)) {
            return parent::productSort();
        }
    }
}
