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

// import Joomla Categories library
jimport('joomla.application.categories');

abstract class LetemPayHelper {


    public static function getCategoryParents($category) {

        $categories_ids = array($category->id);

        try {

            if ($category->hasParent()) {

                $parent_category = $category->getParent();

                if($parent_category->id != 'root'){

                    $categories_ids = array_merge($categories_ids, self::getCategoryParents($parent_category));
                }
            }

        } catch (Exception $e) {

        }

        return $categories_ids;
    }


    public static function getCategoryChildrenIds($category) {

        $conter = 0;
        $subcategories_ids = array();

        if ($category->hasChildren()) {

            $categories = $category->getChildren();

            foreach ($categories as $actual_category) {

                $subcategories_ids[] = $actual_category->id;
                $subcategories_ids = array_merge($subcategories_ids, self::getCategoryChildrenIds($actual_category));
            }
        }

        return $subcategories_ids;
    }


    public static function checkIfPaid($user_id, $category_id) {

        // GET CATEGORY

            $options = array();

            $categories = JCategories::getInstance('LetemPay', $options);

            if ($category = $categories->get($category_id)) {

                $parent_categories_ids = self::getCategoryParents($category);
                // TODO : where -> IN (array) -> $conditions
            }

        // To check if purchase already exists you always need to set all of these conditions
        $conditions = array();
        $conditions['created_by'] = $user_id;
        $conditions['state'] = 1;

        // Load path to table
        JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_letempay' . DS . 'tables');

        // Initialize a transaction table
        $table_transaction = JTable::getInstance('Transaction', 'LetemPayTable');

        // Load paypal helper
        require_once dirname(__FILE__) . '/paypal/paypalfunctions.php';

        foreach ($parent_categories_ids as $parent_category_id) {

            $conditions['catid'] = $parent_category_id;

            // Check if result ended with success
            if ($table_transaction->load($conditions)) {

                // If there was successful payment
                if (isset($table_transaction->result_ConfirmPayment) && $table_transaction->result_ConfirmPayment) {

                    return PayPalHelper::getResultOfTransaction($table_transaction->result_ConfirmPayment);
                }
            }
        }

        return false;
    }
}
