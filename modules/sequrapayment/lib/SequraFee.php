<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

//@deprecated since 3.0.0
class SequraFee
{
    public function withTax()
    {
        return 0;
    }

    public function withoutTax()
    {
        return 0;
    }

    public function taxRate()
    {
        return 0;
    }

    public function displayPriceWithTax()
    {
        return 0;
    }

    public function productId()
    {
        return null;
    }

    public function asOrderItem()
    {
        return null;
    }
}
