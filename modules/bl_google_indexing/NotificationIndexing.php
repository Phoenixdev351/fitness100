<?php
/**
 * 2010-2021 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2021 Bl Modules
 * @license
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class NotificationIndexing
{
    const confirm = 1;
    const warning = 2;

    protected $messages = array();

    public function addWarn($message)
    {
        $this->add(self::warning, $message);
    }

    public function addConf($message)
    {
        $this->add(self::confirm, $message);
    }

    public function getMessages()
    {
        return $this->messages;
    }

    private function add($typeId, $message)
    {
        $this->messages[] = array(
            'type' => '',
            'typeId' => $typeId,
            'messageHtml' => $message,
            'cssClass' => $this->getMessageStyle($typeId),
        );
    }

    private function getMessageStyle($type)
    {
        switch ($type) {
            case self::warning:
                if (_PS_VERSION_ >= '1.6') {
                    return 'alert alert-warning';
                }

                return 'warning warn';
            case self::confirm:
                if (_PS_VERSION_ >= '1.6') {
                    return 'alert alert-success';
                }

                return 'conf confirm';
        }

        return 'warning warn';
    }
}
