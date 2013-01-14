<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * LiteCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to licensing@litecommerce.com so we can send you a copy immediately.
 *
 * PHP version 5.3.0
 *
 * @category  LiteCommerce
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 */

namespace XLite\Core;

/**
 * Abstract URL manager
 *
 */
abstract class URLManager extends \XLite\Base
{
    /**
     * URL output type codes
     */
    const URL_OUTPUT_SHORT = 'short';
    const URL_OUTPUT_FULL  = 'full';

    /**
     * Remove trailing slashes from URL
     *
     * @param string $url URL to prepare
     *
     * @return string
     */
    public static function trimTrailingSlashes($url)
    {
        return \Includes\Utils\URLManager::trimTrailingChars($url);
    }

    /**
     * Return full URL for the resource
     *
     * @param string  $url       URL part to add          OPTIONAL
     * @param boolean $isSecure  Use HTTP or HTTPS        OPTIONAL
     * @param array   $params    URL parameters           OPTIONAL
     * @param string  $output    URL output type          OPTIONAL
     * @param boolean $isSession Use session ID parameter OPTIONAL
     *
     * @return string
     */
    public static function getShopURL(
        $url = '',
        $isSecure = null,
        array $params = array(),
        $output = null,
        $isSession = null
    ) {
        if (!preg_match('/^https?:\/\//Ss', $url)) {

            if (!isset($isSecure)) {
                $isSecure = static::isHTTPS();
            }

            if (!isset($output)) {
                $output = static::URL_OUTPUT_FULL;
            }

            $hostDetails = \XLite::getInstance()->getOptions('host_details');
            $host = $hostDetails[$isSecure ? 'https_host' : 'http_host'];

            if ($host) {
                $proto = ($isSecure ? 'https' : 'http') . '://';

                if ('/' != substr($url, 0, 1)) {
                    $url = $hostDetails['web_dir_wo_slash'] . '/' . $url;
                }

                $isSession = is_null($isSession) ? $isSecure : $isSession;

                if ($isSession) {
                    $session = \XLite\Core\Session::getInstance();
                    $url .= (false !== strpos($url, '?') ? '&' : '?') . $session->getName() . '=' . $session->getID();
                }

                foreach ($params as $name => $value) {
                    $url .= (false !== strpos($url, '?') ? '&' : '?') . $name . '=' . $value;
                }

                if (static::URL_OUTPUT_FULL == $output) {
                    $url = $proto . $host . $url;
                }
            }
        }

        return $url;
    }

    /**
     * Check for secure connection
     *
     * @return boolean
     */
    public static function isHTTPS()
    {
        return \Includes\Utils\URLManager::isHTTPS();
    }

    /**
     * Return current URI
     *
     * @return string
     */
    public static function getSelfURI()
    {
        return \Includes\Utils\URLManager::getSelfURI();
    }

    /**
     * Return current URL
     *
     * @return string
     */
    public static function getCurrentURL()
    {
        return \Includes\Utils\URLManager::getCurrentURL();
    }

    /**
     * Check if provided string is a valid host part of URL
     *
     * @param string $str Host string
     *
     * @return boolean
     */
    public static function isValidURLHost($str)
    {
        return \Includes\Utils\URLManager::isValidURLHost($str);
    }
}