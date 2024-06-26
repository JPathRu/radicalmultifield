<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

/**
 * Make thing clear
 *
 * @var JForm   $tmpl             The Empty form for template
 * @var array   $forms            Array of JForm instances for render the rows
 * @var bool    $multiple         The multiple state for the form field
 * @var int     $min              Count of minimum repeating in multiple mode
 * @var int     $max              Count of maximum repeating in multiple mode
 * @var string  $fieldname        The field name
 * @var string  $control          The forms control
 * @var string  $label            The field label
 * @var string  $description      The field description
 * @var array   $buttons          Array of the buttons that will be rendered
 * @var bool    $groupByFieldset  Whether group the subform fields by it`s fieldset
 */
extract($displayData);

HTMLHelper::_('stylesheet', 'plg_fields_radicalmultifield/cards.css', [
	'version' => filemtime ( __FILE__ ),
	'relative' => true
]);


HTMLHelper::_('script', 'plg_fields_radicalmultifield/subform-repetable-cards.js', [
	'version' => filemtime ( __FILE__ ),
	'relative' => true
]);

$sublayout = empty($groupByFieldset) ? 'section' : 'section-byfieldsets';

if ($multiple) {
	// Add script
	Factory::getApplication()
		->getDocument()
		->getWebAssetManager()
		->useScript('webcomponent.field-subform');
}

?>

<div class="subform-repeatable-wrapper subform-layout">
        <joomla-field-subform class="subform-repeatable<?php echo $class; ?> subform-repeatable-cards" name="<?php echo $name; ?>"
                              button-add=".group-add" button-remove=".group-remove" button-move="<?php echo empty($buttons['move']) ? '' : '.group-move' ?>"
                              repeatable-element=".subform-repeatable-group" minimum="<?php echo $min; ?>" maximum="<?php echo $max; ?>">

        <?php if (!empty($buttons['add'])) : ?>
            <div class="btn-toolbar">
                <div class="btn-group">
                    <button type="button" class="group-add btn button btn-success" aria-label="<?php echo Text::_('JGLOBAL_FIELD_ADD'); ?>">
                        <span class="icon-plus icon-white" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <?php
        foreach ($forms as $k => $form) :
            echo $this->sublayout(
                $sublayout,
                array(
                    'form' => $form,
                    'basegroup' => $fieldname,
                    'group' => $fieldname . $k,
                    'buttons' => $buttons,
                    'unique_subform_id' => $unique_subform_id,
                )
            );
        endforeach;
        ?>

        <?php if ($multiple) : ?>
            <template class="subform-repeatable-template-section"><?php echo trim(
                    $this->sublayout(
                        $sublayout,
                        array(
                            'form' => $tmpl,
                            'basegroup' => $fieldname,
                            'group' => $fieldname . 'X',
                            'buttons' => $buttons,
                            'unique_subform_id' => $unique_subform_id,
                        )
                    )
                ); ?></template>
        <?php endif; ?>
    </joomla-field-subform>
</div>
