<?php
/**
 * @package Let'em pay for Joomla! 3.1
 * @author Ing. Peter Vavro
 * @copyright (C) 2013 - Ing. Peter Vavro
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * More info at http://joomla.vavro.me
**/


// no direct access
defined('_JEXEC') or die;
?>

<?php if ($category != null) : ?>

    <?php if ($alreadyPurchased) : ?>
        <ul>
            <?php require JModuleHelper::getLayoutPath('mod_letempay', 'default_showfiles'); ?>
        </ul>
    <?php else: ?>

        <?php require JModuleHelper::getLayoutPath('mod_letempay', 'default_paypal_button'); ?>

    <?php endif; ?>

<?php endif; ?>
