<?php
/**
 * @package Let'em pay for Joomla! 3.1
 * @author Ing. Peter Vavro
 * @copyright (C) 2013 - Ing. Peter Vavro
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * More info at http://joomla.vavro.me
**/


defined('_JEXEC') or die;

/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 */
class LetemPayTableFile extends JTable
{
    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  Database connector object
     *
     * @since   1.0
     */
    public function __construct(&$db)
    {
            parent::__construct('#__letempay_files_details', 'id', $db);
    }

    /**
     * Overloaded bind function
     *
     * @param   array  $array   Named array to bind
     * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  mixed  Null if operation was satisfactory, otherwise returns an error
     * @since   1.6
     */
    public function bind($array, $ignore = '')
    {

        $jinput = &JFactory::getApplication()->input;
        $files = $jinput->files->get('jform');

        if ($files && $files['file_upload']['name']) {

            jimport('joomla.filesystem.file');

            $fileName = $files['file_upload']['name'];
            $array['file_size'] = $files['file_upload']['size'];
            $array['file_type'] = $files['file_upload']['type'];
            $tmpName =  $files['file_upload']['tmp_name'];

            // Make the file name safe.
            $fileName = JFile::makeSafe($fileName);

            if(!get_magic_quotes_gpc()) {
               $fileName = addslashes($fileName);
            }

            $array['file_name'] = strtolower( $fileName );

            $content = file_get_contents($tmpName, TRUE);

            $array['file_blob'] = $content;
        }

        if (isset($array['params']) && is_array($array['params']))
        {
                $registry = new JRegistry;
                $registry->loadArray($array['params']);
                $array['params'] = (string) $registry;
        }

        return parent::bind($array, $ignore);
    }


	/**
	 * Stores a contact
	 *
	 * @param   boolean	True to update fields even if they are null.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function store($updateNulls = false)
	{
            // Transform the params field
            if (is_array($this->params)) {
                $registry = new JRegistry;
                $registry->loadArray($this->params);
                $this->params = (string) $registry;
            }

            $date = JFactory::getDate();
            $user = JFactory::getUser();

            if ($this->id) {

                // Existing item
                $this->modified = $date->toSql();
                $this->modified_by = $user->get('id');

            } else {

                if (!(int) $this->created) {
                    $this->created = $date->toSql();
                }

                if (empty($this->created_by)) {
                    $this->created_by = $user->get('id');
                }
            }

            return parent::store($updateNulls);
        }


	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.
	 *                            If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success; false if $pks is empty.
	 *
	 * @link    http://docs.joomla.org/JTable/publish
	 * @since   11.1
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
            
		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
                                $this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				return false;
			}
		}

		// Update the publishing state for rows with the given primary keys.
		$query = $this->_db->getQuery(true)
			->update($this->_db->quoteName($this->_tbl))
			->set($this->_db->quoteName('state').' = ' . (int) $state);

		// Determine if there is checkin support for the table.
		if (property_exists($this, 'checked_out') || property_exists($this, 'checked_out_time'))
		{
			$query->where('(checked_out = 0 OR checked_out = ' . (int) $userId . ')');
			$checkin = true;
		}
		else
		{
			$checkin = false;
		}

		// Build the WHERE clause for the primary keys.
		$query->where($k . ' = ' . implode(' OR ' . $k . ' = ', $pks));

		$this->_db->setQuery($query);
               
		try
		{
			$this->_db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
                
		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin the rows.
			foreach ($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->published = $state;
		}

		$this->setError('');
		return true;
	}
}
