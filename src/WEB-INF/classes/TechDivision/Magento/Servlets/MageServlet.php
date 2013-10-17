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
use TechDivision\ServletContainer\Http\HttpPart;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\ServletConfig;
use TechDivision\ServletContainer\Servlets\PhpServlet;

/**
 *
 * @package TechDivision\Magento
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Johann Zelger <jz@techdivision.com>
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
    public function init(ServletConfig $config)
    {
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
     * (non-PHPdoc)
     *
     * @see \TechDivision\ServletContainer\Servlets\PhpServlet::prepareGlobals()
     */
    protected function prepareGlobals(Request $req)
    {
        // if the application has not been called over a vhost configuration append application folder name
        if (!$this->getServletConfig()->getApplication()->isVhostOf($req->getServerName())) {
            $req->setServerVar(
                'SCRIPT_FILENAME',
                $req->getServerVar('DOCUMENT_ROOT') .
                DIRECTORY_SEPARATOR . $this->getServletConfig()->getApplication()->getName() .
                DIRECTORY_SEPARATOR . 'index.php'
            );
            $req->setServerVar(
                'SCRIPT_NAME',
                DIRECTORY_SEPARATOR . $this->getServletConfig()->getApplication()->getName() .
                DIRECTORY_SEPARATOR . 'index.php'
            );
            $req->setServerVar(
                'PHP_SELF',
                DIRECTORY_SEPARATOR . $this->getServletConfig()->getApplication()->getName() .
                DIRECTORY_SEPARATOR . 'index.php'
            );
        } else {
            $req->setServerVar(
                'SCRIPT_FILENAME', $req->getServerVar('DOCUMENT_ROOT') .  DIRECTORY_SEPARATOR . 'index.php'
            );
            $req->setServerVar('SCRIPT_NAME', '/index.php');
            $req->setServerVar('PHP_SELF', '/index.php');
        }
        parent::prepareGlobals($req);
    }
    
    /**
     *
     * @param Request $req            
     * @param Response $res            
     * @throws \Exception
     */
    public function doGet(Request $req, Response $res)
    {
        // start session
        $req->getSession()->start();
        // put session object to WebApplication
        $this->getWebApplication()->setSession($req->getSession());
        // set root dir in WebApplication
        $this->getWebApplication()->setRootDir($this->getServletConfig()->getWebappPath());
        // load WebApplication
        $this->getWebApplication()->load();
        // init globals
        $this->initGlobals($req);
        // init WebApplication
        $this->getWebApplication()->init();
        // run WebApplication
        $content = $this->getWebApplication()->run();
        
        error_log(var_export($_COOKIE, true));
        
        // set headers
        $this->addHeaders($res);
        // set content
        $res->setContent($content);
    }

    /**
     *
     * @see
     *
     * @param Request $req            
     * @param Response $res            
     */
    public function doPost(Request $req, Response $res)
    {
        $this->doGet($req, $res);
    }
}