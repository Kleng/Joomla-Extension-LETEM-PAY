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

<li>

    <?php if ($params->get('show_category', 0)): ?>
        <h3><?php echo $category->title; ?></h3>
    <?php endif; ?>

    <?php if ($params->get('show_category_desc', 0)): ?>
        <p><?php echo $category->description; ?></p>
    <?php endif; ?>

    <?php
        // Get List of files
        $files = modLetemPayHelper::getFileList($category->id);
    ?>

    <?php if($files) : ?>
        <ul class="files_download">
        <?php foreach ($files as $key => $file) : ?>
            <li>
                <?php
                    $filename = '';

                    if ($params->get('show_file_title', 0)) {
                        $filename .= JText::_($file->file_title).'|';
                    }

                    $filename .= JText::_($file->file_name);

                    echo JHtml::_('link', JRoute::_('?option=com_letempay&view=getfile&format=raw&id='.(int) $file->id), $filename, array());
                ?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php $categoryChildren = $category->getChildren(); ?>

    <?php if($categoryChildren) : ?>
        <ul>
    <?php endif; ?>

    <?php foreach ($categoryChildren as $key => $category) : ?>
            <?php require JModuleHelper::getLayoutPath('mod_letempay', 'default_showfiles'); ?>
    <?php endforeach; ?>

    <?php if($categoryChildren) : ?>
        </ul>
    <?php endif; ?>
</li>
