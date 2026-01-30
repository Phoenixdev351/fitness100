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

class AccessLogAdmin extends Xmlfeeds
{
    const LIMIT = 200;

    private $pageId = 0;

    /**
     * @param int $pageId
     */
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
    }

    /**
     * @return int
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    public function getContent()
    {
        return array(
            'limit' => self::LIMIT,
            'logs' => $this->getByFeedId($this->getPageId()),
        );
    }

    public function getByFeedId($feedId)
    {
        $result = Db::getInstance()->executeS('SELECT l.*
			FROM '._DB_PREFIX_.'blmod_xml_access_log l
			WHERE l.feed_id = "'.(int)$feedId.'"
			ORDER BY l.id DESC
			LIMIT '.(int)self::LIMIT);

        return $result;
    }
}
