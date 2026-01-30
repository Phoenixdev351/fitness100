<?php
if (!defined('_PS_VERSION_')) { exit; }
abstract class Db extends DbCore
{
    /*
    * module: ets_superspeed
    * date: 2025-03-17 16:01:24
    * version: 2.0.3
    */
    public function query($sql)
    {
        $context = Context::getContext();
        if(isset($context->ss_total_sql))
            $context->ss_total_sql++;
        else
            $context->ss_total_sql=1;
        return parent::query($sql);
    }
}