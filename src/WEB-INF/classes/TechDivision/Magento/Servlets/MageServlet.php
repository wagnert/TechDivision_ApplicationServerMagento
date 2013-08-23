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
     * Holds the request object
     *
     * @var Request
     */
    protected $request;

    /**
     * Holds the response object
     *
     * @var Response
     */
    protected $response;

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
     * Sets request object
     *
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Returns request object
     *
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Sets response object
     *
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * Returns response object
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
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
        $this->getRequest()->setServerVar(
            'SCRIPT_FILENAME', $this->getRequest()->getServerVar('DOCUMENT_ROOT') .  DIRECTORY_SEPARATOR . 'index.php'
        );
        $this->getRequest()->setServerVar('SCRIPT_NAME', '/index.php');
        $this->getRequest()->setServerVar('PHP_SELF', '/index.php');

        $_SERVER = $this->getRequest()->getServerVars();

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
     * Set all headers set by WebApplication::run()
     *
     * @return void
     */
    public function setHeaders()
    {
        // grap headers and set to respone object
        foreach (xdebug_get_headers() as $i => $h) {
            $h = explode(':', $h, 2);
            if (isset($h[1])) {
                $key = trim($h[0]);
                $value = trim($h[1]);
                $this->getResponse()->addHeader($key, $value);
                if ($key == 'Location') {
                    $this->getResponse()->addHeader('status', 'HTTP/1.1 301');
                }
            }
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

        $startTime = microtime(true);
        // init WebApplication
        $this->getWebApplication()->init();
        // run WebApplication
        $content = $this->getWebApplication()->run();

        $endTime = microtime(true);
        $deltaTime = $endTime - $startTime;
        // set all those headers set by WebApplication run

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