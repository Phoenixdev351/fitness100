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
class PathChecker
{
    public static function checkAbsolutePath($absFilePath, $text = 'file')
    {
        if (empty($absFilePath)) {
            throw new InvalidInputException('Empty filepath for ' . $text);
        }

        if (preg_match('#[\x{0}-\x{1f}]#', $absFilePath)) {
            throw new InvalidInputException('Non-printable characters are not allowed');
        }

        // Prevent directory traversal
        if (preg_match('#\.\.\/#', $absFilePath)) {
            throw new InvalidInputException('Directory traversal is not allowed in ' . $text . ' path');
        }

        if (preg_match('#^\\w+://#', $absFilePath)) {
            throw new InvalidInputException('Stream wrappers are not allowed in ' . $text . ' path');
        }
    }

    public static function checkAbsolutePathAndExists($absFilePath, $text = 'file')
    {
        if (empty($absFilePath)) {
            throw new TargetNotFoundException($text . ' argument missing');
        }
        self::checkAbsolutePath($absFilePath, $text);
        if (@!file_exists($absFilePath)) {
            throw new TargetNotFoundException($text . ' file was not found');
        }
        if (@is_dir($absFilePath)) {
            throw new InvalidInputException($text . ' is a directory');
        }
    }

    public static function checkSourcePath($source)
    {
        self::checkAbsolutePathAndExists($source, 'source');
    }

    public static function checkDestinationPath($destination)
    {
        if (empty($destination)) {
            throw new InvalidInputException('Destination argument missing');
        }
        self::checkAbsolutePath($destination, 'destination');
        if (@is_dir($destination)) {
            throw new InvalidInputException('Destination is a directory');
        }
    }

    public static function checkSourceAndDestinationPaths($source, $destination)
    {
        self::checkSourcePath($source);
        self::checkDestinationPath($destination);
    }
}
