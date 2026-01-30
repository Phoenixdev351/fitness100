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

class XmlFeedUrl
{
    public function get($param = '')
    {
        if (_PS_VERSION_ < '1.5') {
            $xmlfeeds = new Xmlfeeds();

            return $xmlfeeds->getShopProtocol().$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$xmlfeeds->name.'/api/xml.php?'.$param;
        }

        $link = new Link();
        $url = $link->getModuleLink('xmlfeeds', 'api');
        $separator = strpos($url, '?') === false ? '?' : '&';

        return $url.$separator.$param;
    }
}
