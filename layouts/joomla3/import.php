<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);
$i = mt_rand(11111111, 99999999);
$class_select_button = 'btn-radicalmiltifield-select-' . $i;
?>

<div class="import-wrap">

    <?php
        JLoader::register('JFormFieldQuantumupload', JPATH_ADMINISTRATOR . '/components/com_quantummanager/fields/quantumupload.php');
        $field = '<field dropAreaHidden="0" directory="' . $field_path . '" />';
        $upload = new JFormFieldQuantumupload();
        $upload->setup(new SimpleXMLElement($field), '');
        echo $upload->getInput();
    ?>

    <?php HTMLHelper::_('behavior.modal', 'button.' . $class_select_button); ?>
    <div class="button-wrap">
        <button class="btn btn-radicalmiltifield-fast-upload" type="button">
            <span class="icon-upload large-icon"></span> <?php echo Text::_('PLG_RADICAL_MULTI_FIELD_FIELD_IMPORT_UPLOAD') ?>
        </button>
        <button
                class="btn btn-radicalmiltifield-select <?php echo $class_select_button ?>"
                type="button"
                href="<?= JUri::root() ?>administrator/index.php?option=com_ajax&plugin=radicalmultifield&group=fields&format=html&tmpl=component&name=<?php echo $field_name ?>"
                rel="{handler: 'iframe', size: {x: 1450, y: 700}, classWindow: 'quantummanager-modal-sbox-window'}">
            <span class="icon-folder large-icon"></span> <?php echo Text::_('PLG_RADICAL_MULTI_FIELD_FIELD_IMPORT_SELECT') ?>
        </button>
    </div>

</div>



