<?php
/**
 * @package Let'em pay for Joomla! 3.1
 * @author Ing. Peter Vavro
 * @copyright (C) 2013 - Ing. Peter Vavro
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * More info at http://joomla.vavro.me
**/


// No direct access
defined('_JEXEC') or die;

/**
 * Contact component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_contact
 * @since		2.5
 */
class LetemPayHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	$vName	The name of the active view.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_LETEMPAY_SUBMENU_TRANSACTIONS'),
			'index.php?option=com_letempay&view=transactions',
			$vName == 'transactions'
		);

                JHtmlSidebar::addEntry(
			JText::_('COM_LETEMPAY_SUBMENU_FILES'),
			'index.php?option=com_letempay&view=files',
			$vName == 'files'
		);
                
		JHtmlSidebar::addEntry(
			JText::_('COM_LETEMPAY_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_letempay',
			$vName == 'categories');
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param	int		The category ID.
	 * @param	int		The contact ID.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions($categoryId = 0, $transactionId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($transactionId) && empty($categoryId)) {
			$assetName = 'com_letempay';
		}
		elseif (empty($transactionId))
		{
			$assetName = 'com_letempay.category.'.(int) $categoryId;
		}
		else
		{
			$assetName = 'com_letempay.transaction.'.(int) $transactionId;
		}

		$actions = array('core.admin'
                    , 'core.manage'
                    , 'core.create'
                    , 'core.edit'
                    , 'core.edit.own'
                    , 'core.edit.state'
                    , 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
}
