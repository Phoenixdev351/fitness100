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

class OrderSettings
{
    const FILTER_DATE_NONE = 0;
    const FILTER_DATE_TODAY = 1;
    const FILTER_DATE_YESTERDAY = 2;
    const FILTER_DATE_THIS_WEEK = 3;
    const FILTER_DATE_THIS_MONTH = 4;
    const FILTER_DATE_THIS_YEAR = 5;
    const FILTER_DATE_CUSTOM_DAYS = 6;
    const FILTER_DATE_DATE_RANGE = 7;
}
