<?php
/**
 * @package    Radical MultiField
 *
 * @author     delo-design.ru <info@delo-design.ru>
 * @copyright  Copyright (C) 2018 "Delo Design". All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://delo-design.ru
 */

use Gumlet\ImageResize;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

defined('_JEXEC') or die;

JLoader::import( 'components.com_fields.libraries.fieldsplugin', JPATH_ADMINISTRATOR );

/**
 * Radical MultiField plugin.
 *
 * @package  radicalmultifield
 * @since    1.0
 */
class PlgFieldsRadicalmultifield extends FieldsPlugin
{

    /**
     * Returns the custom fields types.
     *
     * @return  string[][]
     *
     * @since   3.7.0
     */
    public function onCustomFieldsGetTypes()
    {
        // Cache filesystem access / checks
        static $types_cache = [];

        if ( isset( $types_cache[ $this->_type . $this->_name ] ) )
        {
            return $types_cache[ $this->_type . $this->_name ];
        }

        $types = [];

        // The root of the plugin
        $root = realpath( JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name );

        $layout = 'radicalmultifield';

        // Strip the extension
        $layout = str_replace( '.php', '', $layout );

        // The data array
        $data = [];

        // The language key
        $key = strtoupper( $layout );

        if ( $key != strtoupper( $this->_name ) )
        {
            $key = strtoupper( $this->_name ) . '_' . $layout;
        }

        // Needed attributes
        $data['type'] = $layout;

        if ( Factory::getLanguage()->hasKey( 'PLG_FIELDS_' . $key . '_LABEL' ) )
        {
            $data[ 'label' ] = Text::sprintf( 'PLG_FIELDS_' . $key . '_LABEL', strtolower( $key ) );

            // Fix wrongly set parentheses in RTL languages
            if ( Factory::getLanguage()->isRTL() )
            {
                $data[ 'label' ] = $data[ 'label' ] . '&#x200E;';
            }
        }
        else
        {
            $data[ 'label' ] = $key;
        }

        $path = $root . '/fields';

        // Add the path when it exists
        if ( file_exists( $path ) )
        {
            $data[ 'path' ] = $path;
        }

        $path = $root . '/rules';

        // Add the path when it exists
        if ( file_exists( $path ) )
        {
            $data[ 'rules' ] = $path;
        }

        $types[] = $data;


	    PluginHelper::importPlugin('radicalmultifield');
	    $radicalmultifieldPlugins = PluginHelper::getPlugin('radicalmultifield');
	    $language = Factory::getLanguage();

        foreach ($radicalmultifieldPlugins as $radicalmultifieldPlugin)
        {
	        $language->load('plg_radicalmultifield_' . $radicalmultifieldPlugin->name, JPATH_ROOT . '/plugins/radicalmultifield/' . $radicalmultifieldPlugin->name, $language->getTag(), true);

	        $types[] = [
		        'type' => 'radicalmultifield_' . $radicalmultifieldPlugin->name,
		        'label' => 'Radical MultiField ' . Text::_('PLG_RADICALMULTIFIELD_PLUGIN_NAME_' . mb_strtoupper($radicalmultifieldPlugin->name)),
		        'path' => JPATH_ROOT . '/plugins/radicalmultifield/' . $radicalmultifieldPlugin->name,
	        ];

        }

        // Add to cache and return the data
        $types_cache[ $this->_type . $this->_name ] = $types;

        return $types;
    }


    /**
     * @since   3.7.0
     */
    public function onCustomFieldsPrepareDom($field, DOMElement $parent, Form $form )
    {

    	$fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form);
        if ( !$fieldNode )
        {
            return $fieldNode;
        }

        $path = URI::base( true ) . '/templates/' . Factory::getApplication()->getTemplate() . '/';

        $fieldNode->setAttribute('template', $path);
        
        return $fieldNode;
    }


    /**
     * Prepares the field value.
     *
     * @param   string    $context  The context.
     * @param   stdclass  $item     The item.
     * @param   stdclass  $field    The field.
     *
     * @return  string
     *
     * @since   3.7.0
     */
    public function onCustomFieldsPrepareField( $context, $item, $field )
    {
        // Check if the field should be processed by us
        if ( !$this->isTypeSupported( $field->type ) )
        {
            return;
        }

        // Merge the params from the plugin and field which has precedence
        $fieldParams = clone $this->params;
        $fieldParams->merge( $field->fieldparams );


	    // Get the path for the layout file
	    if(substr_count($field->type, '_') > 0)
	    {
	    	$tmp = explode('_', $field->type);
		    $path = PluginHelper::getLayoutPath( 'radicalmultifield', $tmp[1], $fieldParams->get( 'template' ) );
	    }
	    else
	    {
		    $path = PluginHelper::getLayoutPath( 'fields', $field->type, $fieldParams->get( 'template' ) );
	    }


        // Render the layout
        ob_start();
        include $path;
        $output = ob_get_clean();

        // Return the output
        return $output;
    }


	/**
	 * The form event. Load additional parameters when available into the field form.
	 * Only when the type of the form is of interest.
	 *
	 * @param   Form     $form  The form
	 * @param   stdClass  $data  The data
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function onContentPrepareForm(Form $form, $data)
	{


		// Check if the field form is calling us
		if (strpos($form->getName(), 'com_fields.field') !== 0)
		{
			return;
		}

		// Ensure it is an object
		$formData = (object) $data;

		// Gather the type
		$type = $form->getValue('type');

		if (!empty($formData->type))
		{
			$type = $formData->type;
		}

		// Not us
		if (!$this->isTypeSupported($type))
		{
			return;
		}

		if(substr_count($type, '_') > 0) {
			$tmp = explode('_', $type);
			$type = $tmp[0];
			$pluginType = $tmp[1];
			$pathType = JPATH_PLUGINS . DIRECTORY_SEPARATOR . 'radicalmultifield' . DIRECTORY_SEPARATOR . $pluginType . DIRECTORY_SEPARATOR . 'params';
		}
		else
		{
			$pluginType = '';
			$pathType = '';
		}

		$path = JPATH_PLUGINS . DIRECTORY_SEPARATOR . $this->_type . DIRECTORY_SEPARATOR . $this->_name . DIRECTORY_SEPARATOR . 'params' . DIRECTORY_SEPARATOR . $type . '.xml';

		// Check if params file exists
		if (!file_exists($path))
		{
			return;
		}

		JLoader::register('RadicalmultifieldHelper', JPATH_ROOT . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['plugins', 'fields', 'radicalmultifield', 'radicalmultifieldhelper']) . '.php');

		$paramsfield = file_get_contents($path);

		if(!empty($pluginType)) {

			if(file_exists($pathType . DIRECTORY_SEPARATOR . 'newparams.xml')) {
				$newParams = file_get_contents($pathType . DIRECTORY_SEPARATOR . 'newparams.xml');
				$paramsfield = str_replace('</fieldset>', $newParams . '</fieldset>', $paramsfield);
			}

		}


		$paramsfieldXml = new SimpleXMLElement($paramsfield);

		$extendfield = explode("\n", $this->params->get('extendfield'));
		if(count($extendfield))
		{
			//TODO переписать, это упарывание какое-то
			for ($i=0;$i<count($paramsfieldXml->fields->fieldset->field);$i++)
			{
				$attr = $paramsfieldXml->fields->fieldset->field[$i]->attributes();
				foreach ($paramsfieldXml->fields->fieldset->field[$i]->attributes() as $a => $b)
				{
					if ((string)$a === 'name' && (string)$b === 'listtype')
					{
						for ($j=0;$j< count($paramsfieldXml->fields->fieldset->field[$i]->form->field);$j++)
						{
							foreach ($paramsfieldXml->fields->fieldset->field[$i]->form->field[$j]->attributes() as $c => $d)
							{
								if ((string)$c === 'type' && (string)$d === 'list')
								{
									foreach ($extendfield as $extend)
									{
										$extend = str_replace(["\r","\n"], '', $extend);
										$fileLists = RadicalmultifieldHelper::loadClassExtendField($extend);
										foreach ($fileLists as $file)
										{
											$newOption = $paramsfieldXml->fields->fieldset->field[$i]->form->field[$j]->addChild('option', trim(ucfirst($file)));
											$newOption->addAttribute('value', $file);
										}
									}
								}
							}
						}
					}
				}
			}
		}


		// Load the specific plugin parameters
		$form->load($paramsfieldXml, true, '/form/*');

		if(!empty($pluginType))
		{

			if(file_exists($pathType))
			{
				$paramsfieldValues = file_get_contents($pathType . DIRECTORY_SEPARATOR . 'default.xml');
				$paramsfieldValuesXML = new SimpleXMLElement($paramsfieldValues);

				for ($i = 0; $i < count($paramsfieldValuesXML->fields->fieldset->field); $i++)
				{

					$count = count($paramsfieldXml->fields->fieldset->field);

					for ($j = 0; $j < $count; $j++)
					{

						if((string) $paramsfieldXml->fields->fieldset->field[$j]['name'] == (string) $paramsfieldValuesXML->fields->fieldset->field[$i]['name'])
						{
							$form->setFieldAttribute((string) $paramsfieldXml->fields->fieldset->field[$j]['name'], 'default', $paramsfieldValuesXML->fields->fieldset->field[$i]['default'], 'fieldparams');
							break;
						}

					}

				}

			}

		}

		HTMLHelper::_( 'jquery.framework' );

		HTMLHelper::script('plg_fields_radicalmultifield/fix.js', [
			'version' => filemtime ( __FILE__ ),
			'relative' => true,
		]);

	}

    /**
     * Returns an array of key values to put in a list from the given field.
     *
     * @param   stdClass  $field  The field.
     *
     * @return  array
     *
     * @since   3.7.0
     */
    public function getListTypeFromField( $field )
    {
        $data = [];

        // Fetch the options from the plugin
        $params = clone $this->params;
        $params->merge( $field->fieldparams );

        foreach ($params->get( 'listtype', []) as $option)
        {
            $data[$option['name']] = [
                "title" => $option['title'],
                "type" => $option['type'],
                "options" => $option['options'],
            ];
        }

        return $data;
    }


    public function onAjaxRadicalmultifield()
    {
        $app = Factory::getApplication();
        if($app->isClient('administrator'))
        {
            JLoader::register('QuantummanagerHelper', JPATH_ROOT . '/administrator/components/com_quantummanager/helpers/quantummanager.php');
            QuantummanagerHelper::loadlang();
            $name = $app->input->get('name', '');

            if(empty($name))
            {
                return;
            }

            $db = Factory::getDBO();
            $query = $db->getQuery(true)
                ->select($db->quoteName(array('params', 'fieldparams')))
                ->from('#__fields')
                ->where( 'name=' . $db->quote($name));
            $field = $db->setQuery( $query )->loadObject();
            $fieldparams = json_decode($field->fieldparams, JSON_OBJECT_AS_ARRAY);
            $field_path = !empty($fieldparams['filesimportpath']) ? $fieldparams['filesimportpath'] : 'images';
            $layout = new FileLayout('quantummanager', JPATH_ROOT . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, [
                    'plugins', 'fields', 'radicalmultifield', 'layouts',
                ]));
            echo $layout->render(['field_path' => $field_path]);
        }
    }


}
