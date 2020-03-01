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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

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

    /**
     * @return string
     */
    public function getInput()
    {

        $this->multiple = true;
        $this->buttons =  [
        	'add' => true,
	        'remove' => true,
	        'move' => true
        ];

        $db = Factory::getDBO();
        $query = $db->getQuery(true)
            ->select($db->quoteName(array('params', 'fieldparams')))
            ->from('#__fields')
            ->where( 'name=' . $db->quote($this->fieldname));
        $field = $db->setQuery( $query )->loadObject();

	    $query = $db->getQuery(true)
		    ->select($db->quoteName(array('params')))
		    ->from('#__extensions')
		    ->where( 'element=' . $db->quote('radicalmultifield'));
	    $extension = $db->setQuery( $query )->loadObject();

        $fieldparams = json_decode($field->fieldparams, JSON_OBJECT_AS_ARRAY);
        $params = json_decode($extension->params, JSON_OBJECT_AS_ARRAY);

        $this->layout = $fieldparams['aview'];

		$this->min = isset($fieldparams['multiplemin']) ? $fieldparams['multiplemin'] : 0;
		$this->max = isset($fieldparams['multiplemax']) ? $fieldparams['multiplemax'] : 5;

        $this->formsource = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><form>";

        //подзагружаем кастомные поля
	    JLoader::import('radicalmultifieldhelper', JPATH_ROOT . '/plugins/fields/radicalmultifield');

	    if(isset($params['extendfield']) && !empty($params['extendfield']))
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
                    $class = $fieldparam['listview'] === 'radio' ? " class=\"btn-group\"" : '';
                    $attrs = trim($fieldparam['attrs']);

                    $this->formsource .= "<field name=\"{$fieldparam['name']}\" type=\"{$fieldparam['listview']}\" label=\"{$fieldparam['title']}\"{$required}{$multiple}{$class} {$attrs}>";
                    $options = explode( "\n", $fieldparam['options'] );

                    foreach ( $options as $option )
                    {
                        $value = OutputFilter::stringURLSafe( $option );
                        $this->formsource .= "<option value=\"{$value}\">{$option}</option>";
                    }

                    $this->formsource .= "</field>";
                    break;

                case 'editor':
                    $attrs = trim( $fieldparam['attrs'] );
                    $this->formsource .= "<field name=\"{$fieldparam['name']}\" type=\"{$fieldparam['type']}\" label=\"{$fieldparam['title']}\" filter=\"raw\" {$attrs}/>";
                    break;

                default:
                    $attrs = trim( $fieldparam['attrs'] );
                    $this->formsource .= "<field name=\"{$fieldparam['name']}\" type=\"{$fieldparam['type']}\" label=\"{$fieldparam['title']}\" {$attrs}/>";
            }
        }

        $this->formsource .= "</form>";

        $html = parent::getInput();


        if(isset($fieldparams['filesimport']))
        {

	        if((int)$fieldparams['filesimport'])
	        {
		        $allow = true;

		        $app = Factory::getApplication();
		        $admin = $app->isAdmin();

		        if((int)$fieldparams['filesimportadmin'] && !$admin)
		        {
			        $allow = false;
		        }

		        if($allow)
		        {
			        $folder = $fieldparams['filesimportpath'];
			        JLoader::register(
				        'FormFieldRadicalmultifieldtreecatalog',
				        JPATH_ROOT . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['plugins', 'fields', 'radicalmultifield', 'elements', 'radicalmultifieldtreecatalog']) . '.php'
			        );

			        $treeCatalog = new FormFieldRadicalmultifieldtreecatalog;

			        $paramsForField = [
				        'name' => 'select-directory',
				        'label' => Text::_('PLG_RADICAL_MULTI_FIELD_FIELD_IMPORT_TREECATALOG_TITLE'),
				        'class' => '',
				        'type' => 'radicalmultifieldtreecatalog',
				        'folder' => $folder,
				        'folderonly' => 'true',
				        'showroot' => 'true',
				        'exs' => $fieldparams['filesimportexc'],
				        'maxsize' => $fieldparams['filesimportmaxsize'],
				        'namefield' => $fieldparams['filesimportname'],
				        'namefile' => $fieldparams['filesimportnamefile'],
				        'importfield' => $this->fieldname,
			        ];

			        $dataAttributes = array_map(function($value, $key)
			        {
				        return $key.'="'.$value.'"';
			        }, array_values($paramsForField), array_keys($paramsForField));

			        $treeCatalog->setup(new SimpleXMLElement("<field " . implode(' ', $dataAttributes) . " />"), '');
			        $html = "<div style='margin-bottom: 15px'>" . $treeCatalog->getInput() . "</div>" . $html;

		        }

	        }

        }

        return $html;
    }

}
