<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.Biz, Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.Biz, Ltd. is strictly forbidden.
 * In order to obtain a license, please contact us: contact@feed.biz
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.Biz, Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Feed.Biz, Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Feed.Biz, Ltd. a l'adresse: contact@feed.biz
 * ...........................................................................
 *
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2022 Feed.Biz - Hong Kong - Head Quarters - Room 1408, 14/F,
 *            Tak Shing House - Theatre Lane - 20 des Voeux Road Central, -Central, Hong Kong
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Support by mail  :  support@feed.biz
 */

require_once dirname(__FILE__).'/feedbiz.tools.class.php';

class FeedbizCertificates
{
    /**
     * Official URL for curl certs
     */
    const URL = 'https://curl.haxx.se/ca/cacert.pem';

    /**
     * Has to be in the file
     */
    const PURPOSE = 'Bundle of CA Root Certificates';
    /**
     * File must have a SHA key, unfortunately, we can't parse it
     */
    const HEADER_REGEX = '## SHA(1|256): ([0-9a-f]{40})';
    /**
     * End of file
     */
    const TRAILER_REGEX = '-----END CERTIFICATE-----';

    /**
     * Expiration
     */
    const EXPIRES = 2592000; //1 month

    /**
     * Directory
     */
    const DIR_CERT = 'cert';
    /**
     * Default cert file which has not to be removed
     */
    const FILE_DEFAULT = 'cacert.pem';
    /**
     * Feed.biz certificates to include in other pem file in order to have matching certificate
     */
    const CA_FEEDBIZ = 'feedbiz.pem';

    /**
     * @param bool $alternate_certificate
     *
     * @return null|string
     */
    public static function getCertificate($alternate_certificate = false)
    {
        $fileid = floor((time() % (86400 * 365)) / self::EXPIRES); // file is valid till self::EXPIRES

        $cert_dir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.self::DIR_CERT;

        if ($alternate_certificate) {
            $cert_file = $cert_dir.DIRECTORY_SEPARATOR.$alternate_certificate;
        } else {
            $cert_file = $cert_dir.DIRECTORY_SEPARATOR.'cacert.'.$fileid.'.pem';
            if (!file_exists($cert_file)) {
                $cert_file = $cert_dir.DIRECTORY_SEPARATOR.'cacert.pem';
            }
        }

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            printf('%s - %s::%s()/#%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
            printf('cert_file: %s'."\n", print_r($cert_file, true));
            echo "</pre>";
        }

        if (!is_dir($cert_dir)) {
            mkdir($cert_dir);
            if (!is_dir($cert_dir)) {
                if (Feedbiz::$debug_mode) {
                    echo "<pre>\n";
                    printf('%s - %s::%s()/#%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                    printf('unable to create cert directory: %s'."\n", print_r($cert_dir, true));
                    echo "</pre>";
                }
                return(false);
            }
        }

        if (!is_readable($cert_dir) && !is_readable($cert_file)) {
            if (Feedbiz::$debug_mode) {
                echo "<pre>\n";
                printf('%s - %s::%s()/#%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                printf('unable to read cert file: %s'."\n", print_r($cert_file, true));
                echo "</pre>";
            }
            return(false);
        }

        if (!is_writeable($cert_dir)) {
            if (Feedbiz::$debug_mode) {
                echo "<pre>\n";
                printf('%s - %s::%s()/#%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                printf('unable to create cert directory: %s'."\n", print_r($cert_dir, true));
                echo "</pre>";
            }

            if (file_exists($cert_file)) {
                return ($cert_file);
            } else {
                $default_certificate = self::getDefaultCertificatePath();

                if (file_exists($default_certificate) && is_readable($default_certificate)) {
                    return($default_certificate);
                } else {
                    return(false);
                }
            }
        }

        self::cleanup();

        if (file_exists($cert_file) && filesize($cert_file) > 4096) {
            return($cert_file);
        } else {
            $contextOptions = array(
                'ssl' => array(
                'verify_peer'   => true,
                'cafile'        => self::getDefaultCertificatePath()
                )
            );

            $cert_content = FeedbizTools::fileGetContents(self::URL, false, $contextOptions);
            $ca_feedbiz_content = FeedbizTools::fileGetContents(
                $cert_dir.DIRECTORY_SEPARATOR.self::CA_FEEDBIZ
            );

            $purpose = preg_match('/'.self::PURPOSE.'/i', $cert_content);
            $sha_check = preg_match('/'.self::HEADER_REGEX.'/i', $cert_content);
            $eof_check = preg_match('/'.self::TRAILER_REGEX.'/i', $cert_content);

            if ($purpose && $sha_check && $eof_check) {
                if (file_put_contents($cert_file, $cert_content.$ca_feedbiz_content) !== false) {
                    return $cert_file;
                }
            }
        }

        return self::getDefaultCertificatePath();
    }

    public static function updateCertificate($alternate_certificate = false)
    {
        $fileid = floor((time() % (86400 * 365)) / self::EXPIRES); // file is valid till self::EXPIRES

        $cert_dir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.self::DIR_CERT;

        if ($alternate_certificate) {
            $cert_file = $cert_dir.DIRECTORY_SEPARATOR.$alternate_certificate;
        } else {
            $cert_file = $cert_dir.DIRECTORY_SEPARATOR.'cacert.'.$fileid.'.pem';
            if (!file_exists($cert_file)) {
                $cert_file = $cert_dir.DIRECTORY_SEPARATOR.'cacert.pem';
            }
        }

        self::cleanup();

        $contextOptions = array(
            'ssl' => array(
            'verify_peer'   => true,
            'cafile'        => self::getDefaultCertificatePath()
            )
        );

        $cert_content = FeedbizTools::fileGetContents(self::URL, false, $contextOptions);
        $ca_feedbiz_content = FeedbizTools::fileGetContents(
            $cert_dir.DIRECTORY_SEPARATOR.self::CA_FEEDBIZ
        );

        $purpose = preg_match('/'.self::PURPOSE.'/i', $cert_content);
        $sha_check = preg_match('/'.self::HEADER_REGEX.'/i', $cert_content);
        $eof_check = preg_match('/'.self::TRAILER_REGEX.'/i', $cert_content);

        if ($purpose && $sha_check && $eof_check) {
            if (file_put_contents($cert_file, $cert_content.$ca_feedbiz_content) !== false) {
                return $cert_file;
            }
        }
    }

    /**
     * returns null if the user has deleted the file
     * @return null|string
     */
    public static function getDefaultCertificatePath()
    {
        $default_cert_file = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.
            self::DIR_CERT.DIRECTORY_SEPARATOR.self::FILE_DEFAULT;

        if (file_exists($default_cert_file) && filesize($default_cert_file) && is_readable($default_cert_file)) {
            return($default_cert_file);
        } else {
            return(null);
        }
    }

    /**
     * delete old certificates
     * @return null
     */
    private static function cleanup()
    {
        $now = time();
        $default_certificate_file = self::getDefaultCertificatePath();

        $cert_dir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.self::DIR_CERT;

        if (!is_dir($cert_dir)) {
            return null;
        }

        $files = glob($cert_dir.'ca*.pem');

        if (!is_array($files) || !count($files)) {
            return null;
        }

        foreach ($files as $file) {
            if (basename($file) == $default_certificate_file) {
                continue;
            }
            if (filemtime($file) < $now - self::EXPIRES) {
                file_put_contents($file, null);
            }
        }
    }
}
