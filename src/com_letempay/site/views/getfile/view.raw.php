<?php
/**
 * @package Let'em pay for Joomla! 3.1
 * @author Ing. Peter Vavro
 * @copyright (C) 2013 - Ing. Peter Vavro
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * More info at http://joomla.vavro.me
**/


defined('_JEXEC') or die;

require_once JPATH_COMPONENT.DS.'helpers'.DS.'letempay.php';

/**
 * Helper Class
 *
 * @static
 * @package     Let'em pay
 * @subpackage	com_letempay
 * @since 1.6
 */
class LetemPayViewGetFile extends JViewLegacy {

    /**
     * Get file
     */
    public function display($tpl = null) {

        $user = JFactory::getUser();

        if($user->id != 0){

            $model = $this->getModel();
            $item = $this->get('Item');

            $content = null;
            $basename = null;
            $mimetype = null;

            if (LetemPayHelper::checkIfPaid($user->id, $item->catid)) {

                $basename = $item->file_name;
                $mimetype = $item->file_type;
                $content = $item->file_blob;

                // Check for errors.
                if (count($errors = $this->get('Errors'))) {
                    JError::raiseError(500, implode("\n", $errors));
                    return false;
                }
            }

            $document = JFactory::getDocument();
            $document->setMimeEncoding($mimetype);

            header("Content-Disposition: attachment; filename=\"$basename\"");
            header('Content-Length: '.strlen($content));


            print_r($content);
            
            // Hits counter
            $model->hit($item->id);
        }
    }
}
