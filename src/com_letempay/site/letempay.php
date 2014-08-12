<?php
/**
 * @package Let'em pay for Joomla! 3.1
 * @author Ing. Peter Vavro
 * @copyright (C) 2013 - Ing. Peter Vavro
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * More info at http://joomla.vavro.me
**/


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// Get an instance of the controller prefixed by HelloWorld
$controller = JControllerLegacy::getInstance('LetemPay');
 
// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();
