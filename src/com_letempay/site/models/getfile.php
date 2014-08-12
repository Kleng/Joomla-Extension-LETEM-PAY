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
 
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
 
class LetemPayModelGetFile extends JModelItem
{
	/**
	 * @var object item
	 */
	protected $item;
 
	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function populateState() 
	{
		$app = JFactory::getApplication();

		// Get the message id
		$id = JRequest::getInt('id');
		$this->setState('file.id', $id);
	}


	/**
	 * Method to get file content from database.
	 *
	 * @param	integer The id of the file.
	 *
	 * @return	array   Items
	 * @since	1.6
	 */
	public function &getItem($pk = null) {

            // Initialise variables.
            $pk = (!empty($pk)) ? $pk : (int) $this->getState('file.id');

            if (!isset($this->item)) {

                $id = $this->getState('file.id');
                $query = $this->_db->getQuery(true);

                $query->from('#__letempay_files_details as a');
                $query->join('LEFT', '#__categories AS c on c.id = a.catid');
                $query->select('a.id, a.catid, a.file_name, a.file_size, a.file_type, a.file_blob'); // ,a.checked_out, a.checked_out_time, a.catid, a.published, a.created, a.created_by, a.ordering

                $query->where('a.id = ' . (int) $pk);
                $query->where('(a.state = 1)');
                $query->where('(c.published = 1)');

                // -TEST- print_r($query->__toString());
                $this->_db->setQuery($query);

                if (!$this->item = $this->_db->loadObject()) {
                    $this->setError($this->_db->getError());
                }
            }

            return $this->item;
	}
        
	/**
	 * Increment the hit counter for the article.
	 *
	 * @param   integer  Optional primary key of the article to increment.
	 *
	 * @return  boolean  True if successful; false otherwise and internal error set.
	 */
	public function hit($pk = 0)
	{
            if($pk != 0){
                
                $db = $this->getDbo();
                $db->setQuery('UPDATE #__letempay_files_details SET hits = hits + 1 WHERE id = ' . (int) $pk);

                try
                {
                    $db->execute();
                }
                catch (RuntimeException $e)
                {
                    $this->setError($e->getMessage());
                    return false;
                }

		return true;
            }
	}
}
