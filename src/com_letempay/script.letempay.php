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
 
/**
 * Script file of List Of Items component
 */
class com_LetemPayInstallerScript
{
 
    /**
     * method to run after an install/update/uninstall method
     *
     * @return void
     */
    function postflight($type, $parent) 
    {
        // Add First / Default category
        // Source : https://gist.github.com/3211464

          // Get the database object
          $db = JFactory::getDbo();

          // JTableCategory is autoloaded in J! 3.0, so...
          if (version_compare(JVERSION, '3.0', 'lt'))
          {
              JTable::addIncludePath(JPATH_PLATFORM . 'joomla/database/table');
          }

          // Initialize a new category
          $category = JTable::getInstance('Category');
          $category->extension = 'com_letempay';
          $category->title = 'General';
          $category->description = 'A general root category (!DO NOT DELETE!)';
          $category->published = 1;
          $category->access = 1;
          $category->params = '{"target":"","image":""}';
          $category->metadata = '{"page_title":"","author":"","robots":""}';
          $category->language = '*';

          // Set the location in the tree
          $category->setLocation(1, 'last-child');

          // Check to make sure our data is valid
          if (!$category->check())
          {
              JError::raiseNotice(500, $category->getError());
              return false;
          }

          // Now store the category
          if (!$category->store(true))
          {
              JError::raiseNotice(500, $category->getError());
              return false;
          }

          // Build the path for our category
          $category->rebuildPath($category->id);
    }
}