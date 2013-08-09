<?php

/**
 * TechDivision\Example\Servlets\LoginServlet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\Magento\Servlets;

use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Servlets\HttpServlet;

/**
 * @package     TechDivision\Magento
 * @copyright   Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license     http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <jz@techdivision.com>
 */

class MageServlet extends HttpServlet
{

    /**
     * @param Request $req
     * @param Response $res
     * @throws \Exception
     */
    public function doGet(Request $req, Response $res)
    {

        /**
         * Compilation includes configuration file
         */
        define('MAGENTO_ROOT', '/opt/appserver/webapps/magento');

        $compilerConfig = MAGENTO_ROOT . '/includes/config.php';
        if (file_exists($compilerConfig)) {
            include $compilerConfig;
        }

        $mageFilename = MAGENTO_ROOT . '/app/Mage.php';
        $maintenanceFile = 'maintenance.flag';

        if (!file_exists($mageFilename)) {
            if (is_dir('downloader')) {
                header("Location: downloader");
            } else {
                echo $mageFilename." was not found";
            }
            exit;
        }

        if (file_exists($maintenanceFile)) {
            include_once MAGENTO_ROOT . '/errors/503.php';
            exit;
        }

        session_destroy();

        require_once $mageFilename;

        $req->setServerVar('SCRIPT_FILENAME', $req->getServerVar('DOCUMENT_ROOT') .  DS . 'index.php');
        $req->setServerVar('SCRIPT_NAME', '/index.php');
        $req->setServerVar('PHP_SELF', '/index.php');

        $_SERVER = $req->getServerVars();

        $_POST = $req->getParameterMap();
        $_GET = $req->getParameterMap();

        foreach (explode('; ', $req->getHeader('Cookie')) as $cookieLine) {
            list($key, $value) = explode('=', $cookieLine);
            $_COOKIE[$key] = $value;
        }

        error_log(var_export($_COOKIE, true));

        #Varien_Profiler::enable();

        if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) {
            \Mage::setIsDeveloperMode(true);
        }

        ini_set('display_errors', 1);

        umask(0);

        /* Store or website code */
        $mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';

        /* Run store or run website */
        $mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';

        ob_start();

        try {
            \Mage::run($mageRunCode, $mageRunType);
        } catch (\Exception $e) {

        }

        $content = ob_get_contents();

        ob_end_clean();

        // grap headers and set to respone object
        foreach (xdebug_get_headers() as $i => $h) {
            $h = explode(':', $h, 2);
            if (isset($h[1])) {
                $key = trim($h[0]);
                $value = trim($h[1]);
                $res->addHeader($key, $value);
                if ($key == 'Location') {
                    $res->addHeader('status', 'HTTP/1.1 301');
                }
            }
        }

        $res->setContent($content);

    }

    public function doPost(Request $req, Response $res)
    {
        $this->doGet($req, $res);
    }

}