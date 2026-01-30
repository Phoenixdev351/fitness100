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

class NotificationXml
{
    const TYPE_CONFIRM = 1;
    const TYPE_WARNING = 2;

    protected $messages = array();

    public function addWarn($message)
    {
        $this->add(self::TYPE_WARNING, $message);
    }

    public function addConf($message)
    {
        $this->add(self::TYPE_CONFIRM, $message);
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
            'message' => $message,
            'cssClass' => $this->getMessageStyle($typeId),
        );
    }

    private function getMessageStyle($type)
    {
        switch ($type) {
            case self::TYPE_WARNING:
                if (_PS_VERSION_ >= '1.6') {
                    return 'alert alert-warning';
                }

                return 'warning warn';
            case self::TYPE_CONFIRM:
                if (_PS_VERSION_ >= '1.6') {
                    return 'alert alert-success';
                }

                return 'conf confirm';
        }

        return 'warning warn';
    }
}
