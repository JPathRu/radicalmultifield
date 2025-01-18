<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;

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
<tr
        class="subform-repeatable-group subform-repeatable-group-<?php echo $unique_subform_id; ?>"
        data-base-name="<?php echo $basegroup; ?>"
        data-group="<?php echo $group; ?>"
>
	<?php foreach ($form->getGroup('') as $field) : ?>
	<td data-column="<?php echo strip_tags($field->label); ?>">
		<?php echo $field->renderField(array('hiddenLabel' => true)); ?>
	</td>
	<?php endforeach; ?>
	<?php if (!empty($buttons)) : ?>
	<td class="uk-text-center">
		<div class="uk-button-group">
			<?php if (!empty($buttons['add'])) : ?><a class="group-add-<?php echo $unique_subform_id; ?> uk-button uk-button-small uk-button-primary" aria-label="<?php echo Text::_('JGLOBAL_FIELD_ADD'); ?>"><span uk-icon="icon: plus;ratio: 0.9"></span></a><?php endif; ?>
			<?php if (!empty($buttons['remove'])) : ?><a class="group-remove-<?php echo $unique_subform_id; ?> uk-button uk-button-small uk-button-danger" aria-label="<?php echo Text::_('JGLOBAL_FIELD_REMOVE'); ?>"><span uk-icon="icon: minus;ratio: 0.9"></span></a><?php endif; ?>
			<?php if (!empty($buttons['move'])) : ?><a class="group-move-<?php echo $unique_subform_id; ?>  uk-button uk-button-small uk-button-secondary" aria-label="<?php echo Text::_('JGLOBAL_FIELD_MOVE'); ?>"><span uk-icon="icon: more-vertical;ratio: 0.9"></span></a><?php endif; ?>
		</div>
	</td>
	<?php endif; ?>
</tr>
