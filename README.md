Things to change in Magento
===========================

/TechDivision_ApplicationServerMagento/instance-src/lib/Zend/Mime.php -> Mime_1.php
/TechDivision_ApplicationServerMagento/instance-src/lib/Zend/Date.php -> Date_1.php
/TechDivision_ApplicationServerMagento/instance-src/lib/Zend/Db/Statement.php -> Statement_1.php

/TechDivision_ApplicationServerMagento/instance-src/app/Mage.php:217
-> public static function register($key, $value, $graceful = true)

/TechDivision_ApplicationServerMagento/instance-src/errors/processor.php:475
-> header('location: ' . $this->$this->reportUrl);
-> /*
-> print '<script type="text/javascript">';
-> print "window.location.href = '{$this->reportUrl}';";
-> print '</script>';
-> error_log(__METHOD__ . ':' . __LINE__);
-> */
-> //exit;

/TechDivision_ApplicationServerMagento/instance-src/app/Mage.php:42
-> $paths[] = MAGENTO_ROOT . DS . 'app';

/TechDivision_ApplicationServerMagento/instance-src/app/Mage.php:51
-> //   include_once "Varien/Autoload.php";

/TechDivision_ApplicationServerMagento/instance-src/app/Mage.php:54
-> //Varien_Autoload::register();

/TechDivision_ApplicationServerMagento/instance-src/lib/Zend/Controller/Response/Abstract.php:318
-> return true;

/TechDivision_ApplicationServerMagento/instance-src/app/code/core/Mage/Core/Model/Session/Abstract/Varien.php:125
-> //session_start();

/TechDivision_ApplicationServerMagento/instance-src/app/code/core/Mage/Core/Model/Session/Abstract/Varien.php:174
->         //if (!isset($_SESSION)) {
           //    $this->start($sessionName);
           //}

/TechDivision_ApplicationServerMagento/instance-src/app/code/core/Mage/Core/Controller/Varien/Action.php:495
-> $session = Mage::getSingleton('core/session', array('name' => $this->_sessionNamespace)); //->start();


Mage_Core_Model_Session_Abstract_Varien



uncomment all "exit;" to "//exit;"

