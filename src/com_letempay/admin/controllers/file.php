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
 * @package     LetemPay
 * @subpackage  com_letempay
 * @since       2.5
 */
class LetemPayControllerFile extends JControllerForm
{

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_LETEMPAY_FILE';    
    
	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   12.2
	 */
	public function save($key = null, $urlVar = null)
	{
            // http://stackoverflow.com/questions/12928849/unable-to-upload-a-zip-file-correctly-in-a-blob-field-in-mysql-from-joomla-2-5-f
            // http://mirificampress.com/permalink/saving_a_file_into_mysql

            $jinput = &JFactory::getApplication()->input;
            $data = $jinput->get('jform', array(), 'array');
            $files = $jinput->files->get('jform');

            if ($files) {

                $data['file_upload'] = $files['file_upload']['name'];

                JRequest::setVar('jform', $data, 'POST', TRUE);
                // TODO : J3.0 -> $jinput->set('jform', $data);
            }

            return parent::save();
        }
}
