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
    die('Not Allowed, Xmlfeeds');
}

/**
 * Class XmlfeedsSkroutzModuleFrontController
 *
 * /index.php?fc=module&module=xmlfeeds&controller=skroutz
 * /module/xmlfeeds/skroutz
 */
class XmlfeedsSkroutzModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        require_once(dirname(__FILE__).'/../../api/SkroutzAttributes.php');
    }
}
