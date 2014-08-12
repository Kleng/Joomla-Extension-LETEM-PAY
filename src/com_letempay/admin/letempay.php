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

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_letempay')) {
    return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// Include dependancies - import joomla controller library
jimport('joomla.application.component.controller');

// require helper file
JLoader::register('LetemPayHelper', __DIR__ . DS . 'helpers' . DS . 'letempay.php');

// Get an instance of the controller prefixed by LetemPay
$controller = JControllerLegacy::getInstance('LetemPay');

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();