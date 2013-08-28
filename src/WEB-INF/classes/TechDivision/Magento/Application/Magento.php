<?php

/**
 * TechDivision\Magento\Application\Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\Magento\Application;


/**
 * @package     TechDivision\Magento
 * @copyright   Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license     http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <jz@techdivision.com>
 */
class Magento
{
    /**
     * Defines session mapping
     *
     * @var array
     */
    protected $sessionMapping = array(
        'core' => 'core/session',
        'customer_base' => 'customer/session',
        'catalog' => 'catalog/session',
        'checkout' => 'checkout/session',
        'adminhtml' => 'adminhtml/session',
        'admin' => 'admin/session',
    );

    /**
     * Root dir to webapp
     *
     * @var string
     */
    protected $rootDir;

    /**
     * Holds the session instance
     *
     * @var \TechDivision\ServletContainer\Session\ServletSession
     */
    protected $session;

    /**
     * Initialize object
     *
     * @param \TechDivision\ServletContainer\Session\ServletSession $session
     */
    public function __construct() {}

    /**
     * Sets session object
     *
     * @param \TechDivision\ServletContainer\Session\ServletSession $session
     */
    public function setSession(\TechDivision\ServletContainer\Session\ServletSession $session)
    {
        $this->session = $session;
    }

    /**
     * Returns session instance
     *
     * @return \TechDivision\ServletContainer\Session\ServletSession
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Sets root dir to webapp
     *
     * @param string $rootDir
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Returns webapp root dir
     *
     * @return string
     */
    public function getRootDir()
    {
        return $this->rootDir;
    }

    /**
     * Loads the necessary files needed.
     *
     * @return void
     */
    public function load()
    {
        /**
         * Compilation includes configuration file
         */
        /*
        $compilerConfig = $this->getServletConfig()->getWebappPath() . '/includes/config.php';
        if (file_exists($compilerConfig)) {
            include $compilerConfig;
        }

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
            include_once $this->getServletConfig()->getWebappPath() . '/errors/503.php';
            exit;
        }
        */

        $mageFilename = $this->getRootDir() . '/app/Mage.php';
        require_once $mageFilename;
    }

    /**
     * Initializes the WebApplication.
     *
     * @return void
     */
    public function init()
    {
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
        try {
            // init magento framework
            \Mage::init($mageRunCode, $mageRunType);
        } catch (\Exception $e) {
            // throw new ApplicationException()
        }
    }

    /**
     * Runs the WebApplication
     *
     * @return string The WebApplications content
     */
    public function run()
    {

        // load session data
        foreach ($this->sessionMapping as $sessionNamespace => $sessionModel) {
            \Mage::getSingleton($sessionModel)->setData(
                $this->getSession()->getData($sessionNamespace)
            );
            \Mage::getSingleton($sessionModel)->setData(
                '___', '___'
            );
        }

        ob_start();

        try {
            // run magento framework
            \Mage::run();
        } catch (\Mage_Core_Exception $e) {
            error_log($e);
            echo $e->toString();
        }

        // grab the contents generated by WebApplication
        $content = ob_get_clean();

        // persist session data
        foreach ($this->sessionMapping as $sessionNamespace => $sessionModel) {
            $this->getSession()->putData(
                $sessionNamespace, \Mage::getSingleton($sessionModel)->getData()
            );
        }

        return $content;
    }

}