<?php //
/**
 * @package Let'em pay for Joomla! 3.1
 * @author Ing. Peter Vavro
 * @copyright (C) 2013 - Ing. Peter Vavro
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * More info at http://joomla.vavro.me
**/


// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.categories');

/**
 * Let'em pay Component Category Tree
 *
 * @static
 * @package     Let'em pay
 * @subpackage	com_letempay
 * @since 1.6
 */
class LetemPayCategories extends JCategories
{
	public function __construct($options = array())
	{
		$options['table'] = '#__letempay_files_details';
		$options['extension'] = 'com_letempay';
		parent::__construct($options);
	}
}