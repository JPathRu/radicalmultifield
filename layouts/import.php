<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\QuantumManager\Administrator\Field\QuantumuploadField;

extract($displayData);
$i                   = mt_rand(11111111, 99999999);
$class_select_button = 'btn-radicalmiltifield-select-' . $i;
?>

<div class="import-wrap" data-modal-id="<?php echo $class_select_button ?>">

	<?php
	$field  = '<field dropAreaHidden="0" directory="' . $field_path . '" />';
	$upload = new QuantumuploadField();
	$upload->__set('scope', 'images');
	$upload->__set('directory', $field_path);
	$upload->setup(new SimpleXMLElement($field), '');
	echo $upload->getInput();

	$buttons = '<button type="button" class="btn btn-secondary button-insert" type="button">'
		. Text::_('JSELECT') . '</button>';
	$buttons .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
		. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>';

	echo LayoutHelper::render('libraries.html.bootstrap.modal.main', [
		'selector' => $class_select_button,
		'params'   => [
			'title'      => Text::_('PLG_RADICAL_MULTI_FIELD_FIELD_IMPORT_SELECT'),
			'url'        => Uri::root() . 'administrator/index.php?option=com_ajax&plugin=radicalmultifield&group=fields&format=html&tmpl=component&name=' . $field_name,
			'height'     => '250px',
			'width'      => '400px',
			'bodyHeight' => 70,
			'modalWidth' => 80,
			'footer'     => $buttons,
		],
		'body'     => ''
	]);
	?>

    <div class="button-wrap">
        <button class="btn btn-secondary btn-radicalmiltifield-fast-upload" type="button">
            <span class="icon-upload large-icon"></span> <?php echo Text::_('PLG_RADICAL_MULTI_FIELD_FIELD_IMPORT_UPLOAD') ?>
        </button>
        <button
                class="btn btn-secondary btn-radicalmiltifield-select <?php echo $class_select_button ?>"
                type="button">
            <span class="icon-folder large-icon"></span> <?php echo Text::_('PLG_RADICAL_MULTI_FIELD_FIELD_IMPORT_SELECT') ?>
        </button>
    </div>

</div>



