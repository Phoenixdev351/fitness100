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
use WebPConvert\Convert\Helpers\JpegQualityDetector;

trait AutoQualityTrait
{
    abstract public function getMimeTypeOfSource();

    /** @var boolean  Whether the quality option has been processed or not */
    private $processed = false;

    /** @var boolean  Whether the quality of the source could be detected or not (set upon processing) */
    private $qualityCouldNotBeDetected = false;

    /** @var integer  The calculated quality (set upon processing - on successful detection) */
    private $calculatedQuality;

    public function isQualityDetectionRequiredButFailing()
    {
        $this->processQualityOptionIfNotAlready();
        return $this->qualityCouldNotBeDetected;
    }

    public function getCalculatedQuality()
    {
        $this->processQualityOptionIfNotAlready();
        return $this->calculatedQuality;
    }

    private function processQualityOptionIfNotAlready()
    {
        if (!$this->processed) {
            $this->processed = true;
            $this->processQualityOption();
        }
    }

    private function processQualityOption()
    {
        $options = $this->options;
        $source = $this->source;

        $q = $options['quality'];
        if ($q == 'auto') {
            if (($this->getMimeTypeOfSource() == 'image/jpeg')) {
                $q = JpegQualityDetector::detectQualityOfJpg($source);
                if (is_null($q)) {
                    $q = $options['default-quality'];
                    $this->qualityCouldNotBeDetected = true;
                }
                $q = min($q, $options['max-quality']);
            }
        }
        $this->calculatedQuality = $q;
    }
}
