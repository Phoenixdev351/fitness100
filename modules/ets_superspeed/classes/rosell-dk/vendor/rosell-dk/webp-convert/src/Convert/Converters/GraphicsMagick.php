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

namespace WebPConvert\Convert\Converters;

if (!defined('_PS_VERSION_')) { exit; }
use WebPConvert\Convert\Converters\AbstractConverter;
use WebPConvert\Convert\Converters\ConverterTraits\EncodingAutoTrait;
use WebPConvert\Convert\Converters\ConverterTraits\ExecTrait;


class GraphicsMagick extends AbstractConverter
{
    use ExecTrait;
    use EncodingAutoTrait;
    private function getPath()
    {
        if (defined('WEBPCONVERT_GRAPHICSMAGICK_PATH')) {
            return constant('WEBPCONVERT_GRAPHICSMAGICK_PATH');
        }
        if (!empty(getenv('WEBPCONVERT_GRAPHICSMAGICK_PATH'))) {
            return getenv('WEBPCONVERT_GRAPHICSMAGICK_PATH');
        }
        return 'gm';
    }

    public function isInstalled()
    {
        exec($this->getPath() . ' -version', $output, $returnCode);
        return ($returnCode == 0);
    }

    public function getVersion()
    {
        exec($this->getPath() . ' -version', $output, $returnCode);
        if (($returnCode == 0) && isset($output[0])) {
            return preg_replace('#http.*#', '', $output[0]);
        }
        return 'unknown';
    }

    // Check if webp delegate is installed
    public function isWebPDelegateInstalled()
    {
        exec($this->getPath() . ' -version', $output, $returnCode);
        foreach ($output as $line) {
            if (preg_match('#WebP.*yes#i', $line)) {
                return true;
            }
        }
        return false;
    }

    public function checkOperationality()
    {
        if($this->checkOperationalityExecTrait())
        {
            if (!$this->isInstalled()) {
                return true;
            }
            if (!$this->isWebPDelegateInstalled()) {
                return false;
            }
            return true;
        }
    }

    private function createCommandLineOptions()
    {
        $commandArguments = [];
        $commandArguments[] = '-quality ' . escapeshellarg($this->getCalculatedQuality());
        if ($this->options['encoding'] == 'lossless') {
            $commandArguments[] = '-define webp:lossless=true';
        } else {
            $commandArguments[] = '-define webp:lossless=false';
        }

        if ($this->options['alpha-quality'] !== 100) {
            $commandArguments[] = '-define webp:alpha-quality=' . strval($this->options['alpha-quality']);
        }

        if ($this->options['low-memory']) {
            $commandArguments[] = '-define webp:low-memory=true';
        }

        if ($this->options['metadata'] == 'none') {
            $commandArguments[] = '-strip';
        }

        $commandArguments[] = '-define webp:method=' . $this->options['method'];

        $commandArguments[] = escapeshellarg($this->source);
        $commandArguments[] = escapeshellarg('webp:' . $this->destination);

        return implode(' ', $commandArguments);
    }

    protected function doActualConvert()
    {
        $command = $this->getPath() . ' convert ' . $this->createCommandLineOptions();
        $useNice = (($this->options['use-nice']) && self::hasNiceSupport()) ? true : false;
        if ($useNice) {
            $command = 'nice ' . $command;
        }
        exec($command, $output, $returnCode);
        if ($returnCode == 127) {
            return false;
        }
        if ($returnCode != 0) {
            return false;
        }
        return true;
    }
}
