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
 * Item Model for an File.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_letempay
 * @since       1.6
 */
class LetemPayModelFile extends JModelAdmin
{
	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_LETEMPAY_FILE';

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   type      The table type to instantiate
	 * @param   string    A prefix for the table class name. Optional.
	 * @param   array     Configuration array for model. Optional.
	 *
	 * @return  JTable    A database object
	 */
	public function getTable($type = 'File', $prefix = 'LetemPayTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array      $data        Data for the form.
	 * @param   boolean    $loadData    True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
            // Get the form.
            $form = $this->loadForm('com_letempay.file', 'file', array('control' => 'jform', 'load_data' => $loadData));

            if (empty($form))
            {
                return false;
            }

            return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 * @since   1.6
	 */
	protected function loadFormData()
	{
            // Check the session for previously entered form data.
            $app = JFactory::getApplication();
            $data = $app->getUserState('com_letempay.edit.file.data', array());

            if (empty($data))
            {
                $data = $this->getItem();

                // Prime some default values.
                if ($this->getState('file.id') == 0)
                {
                    $data->set('catid', $app->input->getInt('catid', $app->getUserState('com_letempay.files.filter.category_id')));
                }
            }

            $this->preprocessData('com_letempay.file', $data);

            return $data;
	}
}
