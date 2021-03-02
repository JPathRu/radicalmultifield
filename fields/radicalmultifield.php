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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;

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
            ->select($db->quoteName(['name', 'params', 'fieldparams']))
            ->from('#__fields')
            ->where( 'name=' . $db->quote($this->fieldname));
        $field = $db->setQuery( $query )->loadObject();

	    $query = $db->getQuery(true)
		    ->select($db->quoteName(['params']))
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
        Factory::getDocument()->addScriptDeclaration("window.siteUrl = '". Uri::root() . "'");
        JLoader::register('RadicalmultifieldHelper', JPATH_ROOT . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['plugins', 'fields', 'radicalmultifield', 'radicalmultifieldhelper']) . '.php');

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

                    foreach ($options as $option)
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

                case 'custom':
                    if(!empty($fieldparam['customxml']))
                    {
                        $this->formsource .= $fieldparam['customxml'];
                    }
                    break;

                default:
                    $attrs = trim( $fieldparam['attrs'] );
                    $this->formsource .= "<field name=\"{$fieldparam['name']}\" type=\"{$fieldparam['type']}\" label=\"{$fieldparam['title']}\" {$attrs}/>";
            }
        }

        $this->formsource .= "</form>";
        $html = parent::getInput();

        if(isset($fieldparams['filesimport']) && RadicalmultifieldHelper::checkQuantumManager())
        {
	        if((int)$fieldparams['filesimport'])
	        {
                HTMLHelper::stylesheet('plg_fields_radicalmultifield/import.css', [
                    'version' => filemtime ( __FILE__ ),
                    'relative' => true,
                ]);

                HTMLHelper::script('plg_fields_radicalmultifield/buttons.js', [
                    'version' => filemtime ( __FILE__ ),
                    'relative' => true,
                ]);

                HTMLHelper::script('plg_fields_radicalmultifield/import.js', [
                    'version' => filemtime ( __FILE__ ),
                    'relative' => true,
                ]);

                $field_path = !empty($fieldparams['filesimportpath']) ? $fieldparams['filesimportpath'] : 'images';
                $params_for_field = [
                    'namefield' => $fieldparams['filesimportname'],
                    'namefile' => $fieldparams['filesimportnamefile'],
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
