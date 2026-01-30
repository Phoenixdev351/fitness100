<?php

class SequraCreditAgreements
{
    public static function for_pp1($total_amount)
    {
        $credit_agreement = array();

        $instalment_count = 12;
        $instalment_fee = ($total_amount < 20000 ? 300 : 500);
        $instalment_amount = floor($total_amount / $instalment_count);

        $credit_agreement["instalment_count"] = $instalment_count;
        $credit_agreement["instalment_amount"] = $instalment_amount;
        $credit_agreement["instalment_fee"] = $instalment_fee;
        $credit_agreement["instalment_total"] = $instalment_amount + $instalment_fee;

        return $credit_agreement;
    }

    public static function for_pp2($total_amount)
    {
        $credit_agreement = array();

        $instalment_count = 12;
        $instalment_fee = self::pp2_instalment_fee($total_amount);
        $down_payment_amount = self::pp2_down_payment($total_amount);
        $drawdown_payment_amount = $total_amount - $down_payment_amount;
        $instalment_amount = floor($drawdown_payment_amount / $instalment_count);
        $setup_fee = ($total_amount < 10000 ? 0 : 2000);

        $credit_agreement["instalment_count"] = $instalment_count;
        $credit_agreement["instalment_amount"] = $instalment_amount;
        $credit_agreement["instalment_fee"] = $instalment_fee;
        $credit_agreement["instalment_total"] = $instalment_amount + $instalment_fee;
        $credit_agreement["setup_fee"] = $setup_fee;
        $credit_agreement["down_payment_amount"] = $down_payment_amount;
        $credit_agreement["down_payment_fees"] = $instalment_fee + $setup_fee;
        $credit_agreement["down_payment_total"] = $down_payment_amount + $instalment_fee + $setup_fee;
        $credit_agreement["drawdown_payment_amount"] = $drawdown_payment_amount;

        return $credit_agreement;
    }

    public static function pp2_instalment_fee($total_amount)
    {
        if ($total_amount < 20000) {
            return 300;
        }
        if ($total_amount < 40000) {
            return 500;
        }
        if ($total_amount < 60000) {
            return 700;
        }
        if ($total_amount < 80000) {
            return 900;
        }
        if ($total_amount < 160000) {
            return 1200;
        }
    }

    public static function pp2_down_payment($total_amount)
    {
        $pp2_max_amount = 160000;
        $pp2_down_payment_percent = 25.00;

        $over_max = max($total_amount, $pp2_max_amount) - $pp2_max_amount;
        $up_to_max = min($total_amount, $pp2_max_amount);

        return round($over_max + (($pp2_down_payment_percent / 100) * $up_to_max));
    }
}
