<?php
defined('_JEXEC') or die;
    $i = mt_rand(11111111, 99999999);
    $class_select_button = 'btn-radicalmiltifield-select-' . $i;
?>

<div class="import-wrap">

    <?php
        JLoader::register('JFormFieldQuantumupload', JPATH_ADMINISTRATOR . '/components/com_quantummanager/fields/quantumupload.php');
        $field = '<field dropAreaHidden="0" directory="images/fast" />';
        $upload = new JFormFieldQuantumupload();
        $upload->setup(new SimpleXMLElement($field), '');
        echo $upload->getInput();
    ?>

    <?php
        JHtml::_('behavior.modal', 'button.' . $class_select_button);

    ?>
    <div class="button-wrap">
        <button class="btn btn-radicalmiltifield-fast-upload" type="button">
            <span class="icon-upload large-icon"></span> Загрузить
        </button>
        <button
                class="btn btn-radicalmiltifield-select <?php echo $class_select_button?>"
                type="button"
                href="/administrator/index.php?option=com_ajax&plugin=radicalmultifield&group=fields&format=html&tmpl=component"
                rel="{handler: 'iframe', size: {x: 1450, y: 700}, classWindow: 'quantummanager-modal-sbox-window'}">
            <span class="icon-folder large-icon"></span> Выбрать на сервере
        </button>
    </div>

</div>



