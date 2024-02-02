<?php namespace Joomla\Plugin\Fields\RadicalMultiField\Field;

/**
 * @package    Radical MultiField
 *
 * @author     delo-design.ru <info@delo-design.ru>
 * @copyright  Copyright (C) 2018 "Delo Design". All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://delo-design.ru
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Field\SubformField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Plugin\Fields\RadicalMultiField\Helper\RadicalMultiFieldHelper;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Class RadicalmultifieldField
 */
class RadicalmultifieldField extends SubformField
{


	/**
	 * @var string
	 */
	public $type = 'RadicalMultiField';


	protected $_cache_field = null;


	protected $_cache_extension = null;


	/**
	 * Allow to override renderer include paths in child fields
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	protected function getLayoutPaths()
	{
		return [
			JPATH_ROOT . '/plugins/fields/radicalmultifield/layouts',
			JPATH_ROOT . '/layouts'
		];
	}


	public function loadSubForm()
	{
		$this->multiple = true;
		$this->getFormsource();

		return parent::loadSubForm();
	}


	protected function getField()
	{
		if ($this->_cache_field !== null)
		{
			return $this->_cache_field;
		}

		$db    = Factory::getDBO();
		$query = $db->getQuery(true)
			->select($db->quoteName(['name', 'params', 'fieldparams']))
			->from('#__fields')
			->where('name=' . $db->quote($this->fieldname));

		$this->_cache_field              = $db->setQuery($query)->loadObject();
		$this->_cache_field->params      = new Registry($this->_cache_field->params);
		$this->_cache_field->fieldparams = new Registry($this->_cache_field->fieldparams);

		return $this->_cache_field;
	}


	protected function getExtensionsParams()
	{
		if ($this->_cache_extension !== null)
		{
			return $this->_cache_extension;
		}

		$db    = Factory::getDBO();
		$query = $db->getQuery(true)
			->select($db->quoteName(['params']))
			->from('#__extensions')
			->where('element=' . $db->quote('radicalmultifield'));

		$this->_cache_extension         = $db->setQuery($query)->loadObject();
		$this->_cache_extension->params = new Registry($this->_cache_field->params);

		return $this->_cache_extension;
	}


	protected function getFormsource()
	{
		if ($this->formsource)
		{
			return $this->formsource;
		}

		$formsource   = '';
		$field        = $this->getField();
		$extension    = $this->getExtensionsParams();
		$this->layout = $field->fieldparams->get('aview');
		$this->min    = $field->fieldparams->get('multiplemin', 0);
		$this->max    = $field->fieldparams->get('multiplemax', 50);
		$formsource   = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><form>";

		//подзагружаем кастомные поля
		Factory::getDocument()->addScriptDeclaration("window.siteUrl = '" . Uri::root() . "'");

		if (!empty($extension->params->get('extendfield')) && !empty($extension->params->get('extendfield')))
		{

			$extendField = explode("\n", $extension->params->get('extendfield'));
			foreach ($extendField as $extend)
			{
				RadicalmultifieldHelper::loadClassExtendField($extend);
			}

		}

		foreach ($field->fieldparams->get('listtype', []) as $fieldparam)
		{
			switch ($fieldparam->type)
			{
				case 'list':
					$required = $fieldparam->required ? " required=\"required\"" : '';
					$multiple = $fieldparam->multiple ? " multiple=\"true\"" : '';
					$class    = $fieldparam->listview === 'radio' ? " class=\"btn-group\"" : '';
					$attrs    = trim($fieldparam->attrs ?? '');

					$formsource .= "<field name=\"{$fieldparam->name}\" type=\"{$fieldparam->listview}\" label=\"{$fieldparam->title}\"{$required}{$multiple}{$class} {$attrs}>";
					$options    = explode("\n", $fieldparam->options);

					foreach ($options as $option)
					{
						$label = $option;
						$value = OutputFilter::stringURLSafe($option);

						if (strpos($option, ';') !== false)
						{
							[$label, $value] = explode(';', $option);
						}

						$formsource .= "<option value=\"{$value}\">{$label}</option>";
					}

					$formsource .= "</field>";
					break;

				case 'editor':
					$attrs      = trim($fieldparam->attrs);
					$formsource .= "<field name=\"{$fieldparam->name}\" type=\"{$fieldparam->type}\" label=\"{$fieldparam->title}\" filter=\"raw\" {$attrs}/>";
					break;

				case 'custom':
					if (!empty($fieldparam->customxml))
					{
						$formsource .= $fieldparam->customxml;
					}
					break;

				default:
					$attrs      = trim($fieldparam->attrs);
					$formsource .= "<field name=\"{$fieldparam->name}\" type=\"{$fieldparam->type}\" label=\"{$fieldparam->title}\" {$attrs}/>";
			}
		}

		$formsource       .= "</form>";
		$this->formsource = $formsource;

		return $this->formsource;
	}


	public function filter($value, $group = null, Registry $input = null)
	{
		// Make sure there is a valid SimpleXMLElement.
		if (!($this->element instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::filter `element` is not an instance of SimpleXMLElement', \get_class($this)));
		}

		// Get the field filter type.
		$filter = (string) $this->element['filter'];

		if ($filter !== '')
		{
			return parent::filter($value, $group, $input);
		}

		// Dirty way of ensuring required fields in subforms are submitted and filtered the way other fields are
		$subForm = $this->loadSubForm();

		// Subform field may have a default value, that is a JSON string
		if ($value && is_string($value))
		{
			$value = json_decode($value, true);

			// The string is invalid json
			if (!$value)
			{
				return null;
			}
		}

		if ($this->multiple)
		{
			$return = [];

			if ($value)
			{
				foreach ($value as $key => $val)
				{
					$return[$key] = $subForm->filter($val);
				}
			}
		}
		else
		{
			$return = $subForm->filter($value);
		}

		return $return;
	}

	/**
	 * @return string
	 */
	public function getInput()
	{
		$this->multiple = true;
		$this->getFormsource();
		$this->buttons = [
			'add'    => true,
			'remove' => true,
			'move'   => true
		];
		$field         = $this->getField();
		$html          = parent::getInput();

		if (RadicalmultifieldHelper::checkQuantumManager())
		{
			if ((int) $field->fieldparams->get('filesimport', 0))
			{

				HTMLHelper::stylesheet('plg_fields_radicalmultifield/import.css', [
					'version'  => filemtime(__FILE__),
					'relative' => true,
				]);

				HTMLHelper::script('plg_fields_radicalmultifield/buttons.js', [
					'version'  => filemtime(__FILE__),
					'relative' => true,
				]);

				HTMLHelper::script('plg_fields_radicalmultifield/import.js', [
					'version'  => filemtime(__FILE__),
					'relative' => true,
				]);

				$field_path       = $field->fieldparams->get('filesimportpath', 'images');
				$params_for_field = [
					'namefield' => $field->fieldparams->get('filesimportname'),
					'namefile'  => $field->fieldparams->get('filesimportnamefile'),
				];

				$html =
					"<div class='radicalmultifield-import' data-options='" . json_encode($params_for_field) . "'>" .
					LayoutHelper::render('import', [
						'field_name' => $field->name,
						'field_path' => $field_path
					], JPATH_ROOT . '/plugins/fields/radicalmultifield/layouts')
					. $html .
					"</div>";

			}
		}

		return $html;
	}


}
