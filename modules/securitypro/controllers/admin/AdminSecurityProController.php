<?php
/**
 * This file is part of the securitypro package.
 *
 * @author Mathias Reker
 * @copyright Mathias Reker
 * @license Commercial Software License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (false === \defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AdminSecurityProController
 */
class AdminSecurityProController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();

        \Tools::redirectAdmin(\Context::getContext()->link->getAdminLink('AdminModules') . '&configure=securitypro&tab_reset=1');
    }
}
