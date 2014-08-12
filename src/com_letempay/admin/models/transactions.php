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
 * Methods supporting a list of article records.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_content
 */
class LetemPayModelTransactions extends JModelList
{

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
                    $config['filter_fields'] = array(
                            'id', 'a.id',
                            'catid', 'a.catid',
                            'state', 'a.state',
                    );
		}

		parent::__construct($config);
	}


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
		$this->setState('filter.category_id', $categoryId);

		// List state information.
		parent::populateState('a.created', 'desc');
	}


	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();

		$app = JFactory::getApplication();

		// Select the required fields from the table.
		$query->select('a.id
                    , a.API_Environment
                    , a.token
                    , a.catid
                    , a.request_SetExpressCheckoutDG
                    , a.result_SetExpressCheckoutDG
                    , a.request_GetExpressCheckoutDetails
                    , a.result_GetExpressCheckoutDetails
                    , a.request_ConfirmPayment
                    , a.result_ConfirmPayment
                    , a.state
                    , a.created
                    , a.created_by');

		$query->from('#__letempay_transactions AS a');

		// Join over the categories.
		$query->select('c.title AS category_title')
			->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Join over the users for the author.
		$query->select('ua.name AS author_name')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by');
                
		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state = 0 OR a.state = 1)');
		}

		// Filter by a single or group of categories.
		$baselevel = 1;
		$categoryId = $this->getState('filter.category_id');

		if (is_numeric($categoryId))
		{
			$cat_tbl = JTable::getInstance('Category', 'JTable');
			$cat_tbl->load($categoryId);
			$rgt = $cat_tbl->rgt;
			$lft = $cat_tbl->lft;
			$baselevel = (int) $cat_tbl->level;
			$query->where('c.lft >= ' . (int) $lft)
				->where('c.rgt <= ' . (int) $rgt);
		}
		elseif (is_array($categoryId))
		{
			JArrayHelper::toInteger($categoryId);
			$categoryId = implode(',', $categoryId);
			$query->where('a.catid IN (' . $categoryId . ')');
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.created');
		$orderDirn = $this->state->get('list.direction', 'asc');

		if ($orderCol == 'category_title') {

                    $orderCol = 'c.title ' . $orderDirn . ', a.created';
		}

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		// echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}
        

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the list items.
		$query = $this->_getListQuery();

		try
		{
			$items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

                foreach ($items as &$item) {

                    if (isset($item->request_SetExpressCheckoutDG) && $item->request_SetExpressCheckoutDG) {

                        $item->request_SetExpressCheckoutDG = json_decode($item->request_SetExpressCheckoutDG);

                        $item->request_SetExpressCheckoutDG->returnURL = urldecode($item->request_SetExpressCheckoutDG->returnURL);
                        $item->request_SetExpressCheckoutDG->cancelURL = urldecode($item->request_SetExpressCheckoutDG->cancelURL);
                    }
                    
                    if (isset($item->result_SetExpressCheckoutDG) && $item->result_SetExpressCheckoutDG) {
                        $item->result_SetExpressCheckoutDG = json_decode($item->result_SetExpressCheckoutDG);
                    }


                    if (isset($item->request_GetExpressCheckoutDetails) && $item->request_GetExpressCheckoutDetails) {
                        $item->request_GetExpressCheckoutDetails = json_decode($item->request_GetExpressCheckoutDetails);
                    }
                    
                    if (isset($item->result_GetExpressCheckoutDetails) && $item->result_GetExpressCheckoutDetails) {
                        $item->result_GetExpressCheckoutDetails = json_decode($item->result_GetExpressCheckoutDetails);
                    }

                    
                    if (isset($item->request_ConfirmPayment) && $item->request_ConfirmPayment) {
                        $item->request_ConfirmPayment = json_decode($item->request_ConfirmPayment);
                    }
                    
                    if (isset($item->result_ConfirmPayment) && $item->result_ConfirmPayment) {
                        $item->result_ConfirmPayment = json_decode($item->result_ConfirmPayment);
                    }
                }

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $this->cache[$store];
	}
}
