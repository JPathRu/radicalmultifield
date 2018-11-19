<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Make thing clear
 *
 * @var JForm   $form       The form instance for render the section
 * @var string  $basegroup  The base group name
 * @var string  $group      Current group name
 * @var array   $buttons    Array of the buttons that will be rendered
 */
extract($displayData);

?>

<div
	class="subform-card subform-repeatable-group-<?php echo $unique_subform_id; ?>"
	data-base-name="<?php echo $basegroup; ?>"
	data-group="<?php echo $group; ?>"
>
    <div class="subform-card-tile">

        <div class="subform-card-tile-background">
            <div class="subform-card-tile-title"></div>
        </div>

        <?php if (!empty($buttons)) : ?>
        <div class="btn-toolbar">
            <div class="btn-group">
                <?php if (!empty($buttons['add'])) : ?>
                    <a class="btn btn-mini button btn-success group-add-<?php echo $unique_subform_id; ?>" aria-label="<?php echo JText::_('JGLOBAL_FIELD_ADD'); ?>">
                        <span class="icon-plus" aria-hidden="true"></span>
                    </a>
                <?php endif; ?>
                <?php if (!empty($buttons['remove'])) : ?>
                    <a class="btn btn-mini button btn-danger group-remove-<?php echo $unique_subform_id; ?>" aria-label="<?php echo JText::_('JGLOBAL_FIELD_REMOVE'); ?>">
                        <span class="icon-minus" aria-hidden="true"></span>
                    </a>
                <?php endif; ?>
                <?php if (!empty($buttons['move'])) : ?>
                    <a class="btn btn-mini button btn-primary group-move-<?php echo $unique_subform_id; ?>" aria-label="<?php echo JText::_('JGLOBAL_FIELD_MOVE'); ?>">
                        <span class="icon-move" aria-hidden="true"></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="subform-card-content" style="display: none">

        <div class="subform-card-content-toolbar">
            <a class="btn button btn-primary button-subform-card-title-show" >
                <span class="icon-arrow-left"></span> <span>Назад</span>
            </a>
        </div>

        <div class="subform-card-content-body">
            <?php foreach ($form->getGroup('') as $field) : ?>
                <?php echo $field->renderField(); ?>
            <?php endforeach; ?>
        </div>

        <div class="subform-card-content-foot">
            <a class="btn button btn-primary button-subform-card-title-show" >
                <span class="icon-arrow-left"></span> <span>Назад</span>
            </a>
        </div>

    </div>

</div>
