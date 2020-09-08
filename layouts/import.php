<?php defined('_JEXEC') or die;

?>

<div>
    <button class="btn">Загрузить</button>
    <button class="btn">Выбрать на сервере</button>
</div>


<?php
    JLoader::register('JFormFieldQuantumupload', JPATH_ADMINISTRATOR . '/components/com_quantummanager/fields/quantumupload.php');
    $field = '<field dropAreaHidden="0" directory="images/fast" />';
    $upload = new JFormFieldQuantumupload();
    $upload->setup(new SimpleXMLElement($field), '');
    echo $upload->getInput();
?>

