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

use TechDivision\Magento\Application\Magento;
use TechDivision\ServletContainer\Http\PostRequest;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\ServletConfig;
use TechDivision\ServletContainer\Servlets\PhpServlet;

/**
 * @package     TechDivision\Magento
 * @copyright   Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license     http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <jz@techdivision.com>
 */

class MageServlet extends PhpServlet
{

    /**
     * Holds the magento WebApplication object
     *
     * @var Magento
     */
    protected $webApplication;

    /**
     * Initialize WebApplication and Servlet
     *
     * @param ServletConfig $config
     * @return void
     */
    public function init(ServletConfig $config) {
        // call parent init
        parent::init($config);

        // register WebApplication
        $this->webApplication = new Magento();
    }

    /**
     * Returns WebApplication object
     *
     * @return Magento
     */
    public function getWebApplication()
    {
        return $this->webApplication;
    }

    /**
     * Initialize globals
     *
     * @return void
     */
    public function initGlobals()
    {
        // if the application has not been called over a vhost configuration append application folder name
        if (!$this->getServletConfig()->getApplication()->isVhostOf($this->getRequest()->getServerName())) {
            $this->getRequest()->setServerVar(
                'SCRIPT_FILENAME',
                $this->getRequest()->getServerVar('DOCUMENT_ROOT') .
                DIRECTORY_SEPARATOR . $this->getServletConfig()->getApplication()->getName() .
                DIRECTORY_SEPARATOR . 'index.php'
            );
            $this->getRequest()->setServerVar(
                'SCRIPT_NAME',
                DIRECTORY_SEPARATOR . $this->getServletConfig()->getApplication()->getName() .
                DIRECTORY_SEPARATOR . 'index.php'
            );
            $this->getRequest()->setServerVar(
                'PHP_SELF',
                DIRECTORY_SEPARATOR . $this->getServletConfig()->getApplication()->getName() .
                DIRECTORY_SEPARATOR . 'index.php'
            );
        } else {
            $this->getRequest()->setServerVar(
                'SCRIPT_FILENAME', $this->getRequest()->getServerVar('DOCUMENT_ROOT') .  DIRECTORY_SEPARATOR . 'index.php'
            );
            $this->getRequest()->setServerVar('SCRIPT_NAME', '/index.php');
            $this->getRequest()->setServerVar('PHP_SELF', '/index.php');
        }

        $_SERVER = $this->getRequest()->getServerVars();
        $_SERVER['SERVER_PORT'] = NULL;

        // check post type and set params to globals
        if ($this->getRequest() instanceof PostRequest) {
            $_POST = $this->getRequest()->getParameterMap();
            // check if there are get params send via uri
            parse_str($this->getRequest()->getQueryString(), $_GET);
        } else {
            $_GET = $this->getRequest()->getParameterMap();
        }

        $_REQUEST = $this->getRequest()->getParameterMap();

        foreach (explode('; ', $this->getRequest()->getHeader('Cookie')) as $cookieLine) {
            list($key, $value) = explode('=', $cookieLine);
            $_COOKIE[$key] = $value;
        }
    }

    /**
     * @param Request $req
     * @param Response $res
     * @throws \Exception
     */
    public function doGet(Request $req, Response $res)
    {
        // register request and response objects
        $this->setRequest($req);
        $this->setResponse($res);
        // start session
        $this->getRequest()->getSession()->start();
        // put session object to WebApplication
        $this->getWebApplication()->setSession($this->getRequest()->getSession());
        // set root dir in WebApplication
        $this->getWebApplication()->setRootDir($this->getServletConfig()->getWebappPath());
        // load WebApplication
        $this->getWebApplication()->load();
        // init globals
        $this->initGlobals();
        // init WebApplication
        $this->getWebApplication()->init();
        // run WebApplication
        $content = $this->getWebApplication()->run();
        // set headers
        $this->setHeaders();
        // set content
        $this->getResponse()->setContent($content);
    }

    /**
     * @see
     * @param Request $req
     * @param Response $res
     */
    public function doPost(Request $req, Response $res)
    {
        $this->doGet($req, $res);
    }

}