<?php
/**
 * @package Let'em pay for Joomla! 3.1
 * @author Ing. Peter Vavro
 * @copyright (C) 2013 - Ing. Peter Vavro
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * More info at http://joomla.vavro.me
**/


// No direct access
defined('_JEXEC') or die('Restricted access');

// import Joomla table library
jimport('joomla.database.table');

/**
 * Hello Table class
 */
class LetemPayTableTransaction extends JTable {

    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function __construct(&$db) {
        parent::__construct('#__letempay_transactions', 'id', $db);
    }

    /**
     * Method to load a row from the database by primary key and bind the fields
     * to the JTable instance properties.
     *
     * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
     *  set the instance property value is used.
     * @param   boolean  $reset  True to reset the default values before loading the new row.
     *
     * @return  boolean  True if successful. False if row not found or on error (internal error state set in that case).
     *
     * @link    http://docs.joomla.org/JTable/load
     * @since   11.1
     */
    public function load($keys = null, $reset = true) {

        if (parent::load($keys, $reset)) {

            if (isset($this->request_SetExpressCheckoutDG) && $this->request_SetExpressCheckoutDG) {

                // Convert the result_SetExpressCheckoutDG field to a registry.
                $result_SetExpressCheckoutDG = new JRegistry;
                $result_SetExpressCheckoutDG->loadString($this->request_SetExpressCheckoutDG);
                $this->request_SetExpressCheckoutDG = $result_SetExpressCheckoutDG->toArray();
            }
            
            if (isset($this->result_SetExpressCheckoutDG) && $this->result_SetExpressCheckoutDG) {

                // Convert the result_SetExpressCheckoutDG field to a registry.
                $result_SetExpressCheckoutDG = new JRegistry;
                $result_SetExpressCheckoutDG->loadString($this->result_SetExpressCheckoutDG);
                $this->result_SetExpressCheckoutDG = $result_SetExpressCheckoutDG->toArray();
            }


            if (isset($this->request_GetExpressCheckoutDetails) && $this->request_GetExpressCheckoutDetails) {

                // Convert the result_GetExpressCheckoutDetails field to a registry.
                $request_GetExpressCheckoutDetails = new JRegistry;
                $request_GetExpressCheckoutDetails->loadString($this->request_GetExpressCheckoutDetails);
                $this->request_GetExpressCheckoutDetails = $request_GetExpressCheckoutDetails->toArray();
            }


            if (isset($this->result_GetExpressCheckoutDetails) && $this->result_GetExpressCheckoutDetails) {

                // Convert the result_GetExpressCheckoutDetails field to a registry.
                $result_GetExpressCheckoutDetails = new JRegistry;
                $result_GetExpressCheckoutDetails->loadString($this->result_GetExpressCheckoutDetails);
                $this->result_GetExpressCheckoutDetails = $result_GetExpressCheckoutDetails->toArray();
            }


            if (isset($this->request_ConfirmPayment) && $this->request_ConfirmPayment) {

                // Convert the result_ConfirmPayment field to a registry.
                $request_ConfirmPayment = new JRegistry;
                $request_ConfirmPayment->loadString($this->request_ConfirmPayment);
                $this->request_ConfirmPayment = $request_ConfirmPayment->toArray();
            }

            if (isset($this->result_ConfirmPayment) && $this->result_ConfirmPayment) {

                // Convert the result_ConfirmPayment field to a registry.
                $result_ConfirmPayment = new JRegistry;
                $result_ConfirmPayment->loadString($this->result_ConfirmPayment);
                $this->result_ConfirmPayment = $result_ConfirmPayment->toArray();
            }


            // Convert the params field to a registry.
            $params = new JRegistry;
            $params->loadString($this->params);
            $this->params = $params;

            return true;

        } else {

            return false;
        }
    }


    /**
     * !!! TODO Stores a transaction
     *
     * @param	boolean	True to update fields even if they are null.
     * @return	boolean	True on success, false on failure.
     * @since	1.6
     */
    public function store($updateNulls = false) {

        // Transform the request_SetExpressCheckoutDG field
        if (is_array($this->request_SetExpressCheckoutDG)) {

            $registry = new JRegistry();
            $registry->loadArray($this->request_SetExpressCheckoutDG);
            $this->request_SetExpressCheckoutDG = (string) $registry;
        }

        // Transform the result_SetExpressCheckoutDG field
        if (is_array($this->result_SetExpressCheckoutDG)) {

            $registry = new JRegistry();
            $registry->loadArray($this->result_SetExpressCheckoutDG);
            $this->result_SetExpressCheckoutDG = (string) $registry;
        }

        // Transform the request_GetExpressCheckoutDetails field
        if (is_array($this->request_GetExpressCheckoutDetails)) {

            $registry = new JRegistry();
            $registry->loadArray($this->request_GetExpressCheckoutDetails);
            $this->request_GetExpressCheckoutDetails = (string) $registry;
        }

        // Transform the result_GetExpressCheckoutDetails field
        if (is_array($this->result_GetExpressCheckoutDetails)) {

            $registry = new JRegistry();
            $registry->loadArray($this->result_GetExpressCheckoutDetails);
            $this->result_GetExpressCheckoutDetails = (string) $registry;
        }

        // Transform the request_ConfirmPayment field
        if (is_array($this->request_ConfirmPayment)) {

            $registry = new JRegistry();
            $registry->loadArray($this->request_ConfirmPayment);
            $this->request_ConfirmPayment = (string) $registry;
        }

        // Transform the result_ConfirmPayment field
        if (is_array($this->result_ConfirmPayment)) {

            $registry = new JRegistry();
            $registry->loadArray($this->result_ConfirmPayment);
            $this->result_ConfirmPayment = (string) $registry;
        }

        // Transform the params field
        if (is_array($this->params)) {
            $registry = new JRegistry();
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

            // New newsfeed. A feed created and created_by field can be set by the user,
            // so we don't touch either of these if they are set.
            if (!intval($this->created)) {
                $this->created = $date->toSql();
            }

            if (empty($this->created_by)) {
                $this->created_by = $user->get('id');
            }
        }

        // Attempt to store the data.
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
