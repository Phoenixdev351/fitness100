<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

namespace WebPConvert\Convert\Converters\BaseTraits;

if (!defined('_PS_VERSION_')) { exit; }
trait DestinationPreparationTrait
{

    abstract public function getDestination();

    private function createWritableDestinationFolder()
    {
        $destination = $this->getDestination();

        $folder = dirname($destination);
        if (!file_exists($folder)) {
            if (!mkdir($folder, 0777, true)) {
                throw new CreateDestinationFolderException(
                    'Failed creating folder. Check the permissions!',
                    'Failed creating folder: ' . $folder . '. Check permissions!'
                );
            }
        }
    }

    private function checkDestinationWritable()
    {
        $destination = $this->getDestination();
        $dirName = dirname($destination);

        if (@is_writable($dirName) && @is_executable($dirName)) {
            return;
        }

        if (file_put_contents($destination, 'dummy') !== false) {
            if(file_exists($destination))
                \Ets_superspeed_defines::unlink($destination);
            return;
        }

        throw new CreateDestinationFileException(
            'Cannot create file: ' . basename($destination) . ' in dir:' . dirname($destination)
        );
    }

    private function removeExistingDestinationIfExists()
    {
        $destination = $this->getDestination();
        if (file_exists($destination)) {
            if (!\Ets_superspeed_defines::unlink($destination)) {
                throw new CreateDestinationFileException(
                    'Existing file cannot be removed: ' . basename($destination)
                );
            }
        }
    }
}
