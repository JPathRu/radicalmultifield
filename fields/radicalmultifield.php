<?php
/**
 * @package    Radical MultiField
 *
 * @author     delo-design.ru <info@delo-design.ru>
 * @copyright  Copyright (C) 2018 "Delo Design". All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://delo-design.ru
 */

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('subform');
JFormHelper::loadFieldClass('folderlist');

/**
 * Class JFormFieldRadicalmultifield
 */
class JFormFieldRadicalmultifield extends JFormFieldSubform
{


	/**
	 * @var string
	 */
	public $type = 'RadicalMultiField';


	protected $_cache_field = null;


	protected $_cache_field_params = null;


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

		$db                 = Factory::getDBO();
		$query              = $db->getQuery(true)
			->select($db->quoteName(['name', 'params', 'fieldparams']))
			->from('#__fields')
			->where('name=' . $db->quote($this->fieldname));
		$this->_cache_field = $db->setQuery($query)->loadObject();

		return $this->_cache_field;
	}


	protected function getFieldParams()
	{
		if ($this->_cache_field_params !== null)
		{
			return $this->_cache_field_params;
		}

		$db                        = Factory::getDBO();
		$query                     = $db->getQuery(true)
			->select($db->quoteName(['params']))
			->from('#__extensions')
			->where('element=' . $db->quote('radicalmultifield'));
		$this->_cache_field_params = $db->setQuery($query)->loadObject();

		return $this->_cache_field_params;
	}


	protected function getFormsource()
	{
		if ($this->formsource)
		{
			return $this->formsource;
		}

		$formsource = '';
		$field      = $this->getField();
		$extension  = $this->getFieldParams();

		$fieldparams = json_decode($field->fieldparams, JSON_OBJECT_AS_ARRAY);
		$params      = json_decode($extension->params, JSON_OBJECT_AS_ARRAY);

		$this->layout = $fieldparams['aview'];

		$this->min = isset($fieldparams['multiplemin']) ? $fieldparams['multiplemin'] : 0;
		$this->max = isset($fieldparams['multiplemax']) ? $fieldparams['multiplemax'] : 5;

		$formsource = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><form>";

		//подзагружаем кастомные поля
		Factory::getDocument()->addScriptDeclaration("window.siteUrl = '" . Uri::root() . "'");
		JLoader::register('RadicalmultifieldHelper', JPATH_ROOT . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['plugins', 'fields', 'radicalmultifield', 'radicalmultifieldhelper']) . '.php');

		if (isset($params['extendfield']) && !empty($params['extendfield']))
		{

			$extendField = explode("\n", $params['extendfield']);
			foreach ($extendField as $extend)
			{
				RadicalmultifieldHelper::loadClassExtendField($extend);
			}

		}


		foreach ($fieldparams['listtype'] as $fieldparam)
		{
			switch ($fieldparam['type'])
			{
				case 'list':
					$required = $fieldparam['required'] ? " required=\"required\"" : '';
					$multiple = $fieldparam['multiple'] ? " multiple=\"true\"" : '';
					$class    = $fieldparam['listview'] === 'radio' ? " class=\"btn-group\"" : '';
					$attrs    = trim($fieldparam['attrs']);

					$formsource .= "<field name=\"{$fieldparam['name']}\" type=\"{$fieldparam['listview']}\" label=\"{$fieldparam['title']}\"{$required}{$multiple}{$class} {$attrs}>";
					$options    = explode("\n", $fieldparam['options']);

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
					$attrs      = trim($fieldparam['attrs']);
					$formsource .= "<field name=\"{$fieldparam['name']}\" type=\"{$fieldparam['type']}\" label=\"{$fieldparam['title']}\" filter=\"raw\" {$attrs}/>";
					break;

				case 'custom':
					if (!empty($fieldparam['customxml']))
					{
						$formsource .= $fieldparam['customxml'];
					}
					break;

				default:
					$attrs      = trim($fieldparam['attrs']);
					$formsource .= "<field name=\"{$fieldparam['name']}\" type=\"{$fieldparam['type']}\" label=\"{$fieldparam['title']}\" {$attrs}/>";
			}
		}

		$formsource .= "</form>";

		return $this->formsource = $formsource;
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

		if (isset($fieldparams['filesimport']) && RadicalmultifieldHelper::checkQuantumManager())
		{
			if ((int) $fieldparams['filesimport'])
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

				$field_path       = !empty($fieldparams['filesimportpath']) ? $fieldparams['filesimportpath'] : 'images';
				$params_for_field = [
					'namefield' => $fieldparams['filesimportname'],
					'namefile'  => $fieldparams['filesimportnamefile'],
				];
				$html             =
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
