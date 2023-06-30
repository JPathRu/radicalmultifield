<?php
/**
 * @package    quantummanager
 * @author     Dmitry Tsymbal <cymbal@delo-design.ru>
 * @copyright  Copyright © 2019 Delo Design & NorrNext. All rights reserved.
 * @license    GNU General Public License version 3 or later; see license.txt
 * @link       https://www.norrnext.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

$app = Factory::getApplication();
$app->getSession()->clear('quantummanageraddscripts');
$app->getSession()->set('quantummanagerroot', $field_path);
$app->getSession()->set('quantummanagerrootcheck', 0);

HTMLHelper::_('stylesheet', 'plg_system_quantummanagermedia/modal.css', [
	'version' => filemtime(__FILE__),
	'relative' => true
]);

HTMLHelper::_('script', 'plg_fields_radicalmultifield/joomla4/modal.js', [
    'version' => filemtime(__FILE__),
    'relative' => true
]);

?>

	<?php

	try {
		JLoader::register('JFormFieldQuantumCombine', JPATH_ROOT . '/administrator/components/com_quantummanager/fields/quantumcombine.php');
		JLoader::register('QuantummanagerHelper', JPATH_SITE . '/administrator/components/com_quantummanager/helpers/quantummanager.php');
		$folderRoot = 'root';

		$buttonsBun = [];
		$fields = [
			'quantumtreecatalogs' => [
				'label' => '',
				'directory' => $folderRoot,
				'position' => 'container-left',
			],
			'quantumtoolbar' => [
				'label' => '',
				'position' => 'container-center-top',
				'buttons' => 'all',
				'buttonsBun' => '',
				'cssClass' => 'qm-padding-small-left qm-padding-small-right qm-padding-small-top qm-padding-small-bottom',
			],
			'quantumupload' => [
				'label' => '',
				'position' => 'container-center-top',
				'maxsize' => QuantummanagerHelper::getParamsComponentValue('maxsize', '10'),
				'dropAreaHidden' => QuantummanagerHelper::getParamsComponentValue('dropareahidden', '0'),
				'directory' => $folderRoot,
				'cssClass' => 'qm-padding-small-left qm-padding-small-right qm-padding-small-bottom',
			],
			'quantumviewfiles' => [
				'label' => '',
				'position' => 'container-center-center',
				'directory' => $folderRoot,
				'view' => 'list-grid',
				'onlyfiles' => '0',
				'watermark' => QuantummanagerHelper::getParamsComponentValue('overlay' , 0) > 0 ? '1' : '0',
				'help' => QuantummanagerHelper::getParamsComponentValue('help' , '1'),
				'metafile' => QuantummanagerHelper::getParamsComponentValue('metafile' , '1'),
			],
			'quantumcropperjs' => [
				'label' => '',
				'position' => 'bottom'
			],
		];


		if((int)QuantummanagerHelper::getParamsComponentValue('unsplash', '1'))
		{
			$fields['quantumunsplash'] = [
				'label' => '',
				'position' => 'bottom'
			];
		}

		$actions = QuantummanagerHelper::getActions();
		if (!$actions->get('core.create'))
		{
			$buttonsBun[] = 'viewfilesCreateDirectory';
			unset($fields['quantumupload']);
		}

		if (!$actions->get('core.delete'))
		{
			unset($fields['quantumcropperjs']);
		}

		if (!$actions->get('core.delete'))
		{
			$buttonsBun[] = 'viewfilesDelete';
		}

		$optionsForField = [
			'name' => 'filemanager',
			'label' => '',
			'cssClass' => 'quantummanager-full-component-wrap',
			'fields' => json_encode($fields)
		];

		$field = new JFormFieldQuantumCombine();
		foreach ($optionsForField as $name => $value)
		{
			$field->__set($name, $value);
		}
		echo $field->getInput();
	}
	catch (Exception $e)
    {
		echo $e->getMessage();
	}
?>

<script type="text/javascript">
    window.QuantumwindowLang = {
        'buttonInsert': '<?php echo Text::_('COM_QUANTUMMANAGER_ACTION_SELECT'); ?>',
        'inputAlt': '<?php echo Text::_('PLG_QUANTUMMANAGERMEDIA_WINDOW_ALT'); ?>',
        'inputWidth': '<?php echo Text::_('PLG_QUANTUMMANAGERMEDIA_WINDOW_WIDTH'); ?>',
        'inputHeight': '<?php echo Text::_('PLG_QUANTUMMANAGERMEDIA_WINDOW_HEIGHT'); ?>',
        'inputHspace': '<?php echo Text::_('PLG_QUANTUMMANAGERMEDIA_WINDOW_HSPACE'); ?>',
        'inputVspace': '<?php echo Text::_('PLG_QUANTUMMANAGERMEDIA_WINDOW_VSPACES'); ?>',
        'inputAlign': '<?php echo Text::_('PLG_QUANTUMMANAGERMEDIA_WINDOW_ALIGN'); ?>',
        'inputCssClass': '<?php echo Text::_('PLG_QUANTUMMANAGERMEDIA_WINDOW_CLASS'); ?>'
    };
</script>


