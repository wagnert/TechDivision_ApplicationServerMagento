Things to change in Magento
===========================

/TechDivision_ApplicationServerMagento/instance-src/lib/Zend/Mime.php -> Mime_1.php
/TechDivision_ApplicationServerMagento/instance-src/lib/Zend/Db/Statement.php -> Statement_1.php

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

uncomment all "exit;" to "//exit;"

