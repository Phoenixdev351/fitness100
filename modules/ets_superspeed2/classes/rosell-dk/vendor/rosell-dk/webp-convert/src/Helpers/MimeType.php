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

namespace WebPConvert\Helpers;

if (!defined('_PS_VERSION_')) { exit; }
use ImageMimeTypeGuesser\ImageMimeTypeGuesser;

/**
 * Get MimeType, results cached by path.
 *
 * @package    WebPConvert
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since Release 2.0.6
 */
class MimeType
{
    private static $cachedDetections = [];

    /**
     * Get mime type for image (best guess).
     *
     * It falls back to using file extension. If that fails too, false is returned
     *
     * @return  string|false|null mimetype (if it is an image, and type could be determined / guessed),
     *    false (if it is not an image type that the server knowns about)
     *    or null (if nothing can be determined)
     */
    public static function getMimeTypeDetectionResult($absFilePath)
    {
        PathChecker::checkAbsolutePathAndExists($absFilePath);

        if (isset(self::$cachedDetections[$absFilePath])) {
            return self::$cachedDetections[$absFilePath];
        }
        $cachedDetections[$absFilePath] = ImageMimeTypeGuesser::lenientGuess($absFilePath);
        return $cachedDetections[$absFilePath];
    }
}
