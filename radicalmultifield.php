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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
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


        // Add to cache and return the data
        $types_cache[ $this->_type . $this->_name ] = $types;

        HTMLHelper::_( 'jquery.framework' );


	    HTMLHelper::script('plg_fields_radicalmultifield/core/radicalmultifield.js', [
		    'version' => filemtime ( __FILE__ ),
		    'relative' => true,
	    ]);

        return $types;
    }


    /**
     * @since   3.7.0
     */
    public function onCustomFieldsPrepareDom( $field, DOMElement $parent, JForm $form )
    {

    	$fieldNode = parent::onCustomFieldsPrepareDom( $field, $parent, $form );
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
        $path = PluginHelper::getLayoutPath( 'fields', $field->type, $fieldParams->get( 'template' ) );


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
	 * @param   JForm     $form  The form
	 * @param   stdClass  $data  The data
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function onContentPrepareForm(JForm $form, $data)
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

		$path = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/params/' . $type . '.xml';

		// Check if params file exists
		if (!file_exists($path))
		{
			return;
		}

		JLoader::import('radicalmultifieldhelper', JPATH_ROOT . '/plugins/fields/radicalmultifield');

		$paramsfield = file_get_contents($path);
		$paramsfieldXml = new SimpleXMLElement($paramsfield);

		$extendfield = explode("\n", $this->params->get('extendfield'));
		if(count($extendfield))
		{
			//TODO переписать, это упарывание какое-то
			for ($i = 0; $i < count($paramsfieldXml->fields->fieldset->field); $i++)
			{
				$attr = $paramsfieldXml->fields->fieldset->field[$i]->attributes();
				foreach ($paramsfieldXml->fields->fieldset->field[$i]->attributes() as $a => $b)
				{
					if ((string) $a === 'name' && (string) $b === 'listtype')
					{
						for ($j = 0; $j < count($paramsfieldXml->fields->fieldset->field[$i]->form->field); $j++)
						{
							foreach ($paramsfieldXml->fields->fieldset->field[$i]->form->field[$j]->attributes() as $c => $d)
							{
								if ((string) $c === 'type' && (string) $d === 'list')
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


	/**
	 * @return array|string
	 */
    public function onAjaxRadicalmultifield()
    {
    	$app = Factory::getApplication();
    	$data = $app->input->getArray();
	    $admin = $app->isAdmin();
	    $allow = true;

	    JLoader::register('RadicalmultifieldHelper', JPATH_SITE . '/plugins/fields/radicalmultifield/radicalmultifieldhelper.php');

	    $params = RadicalmultifieldHelper::getParams($data['importfield']);

	    if(isset($params['filesimportadmin']))
	    {

		    if((int)$params['filesimportadmin'] && !$admin)
		    {
			    $allow = false;
		    }

	    }
	    else
	    {
	    	$allow = false;
	    }


	    if(!$allow)
	    {
		    $output = [];
		    $output["error"] = Text::_('PLG_RADICAL_MULTI_FIELD_IMPORT_ERROR_UPLOAD');
			return $output;
	    }


	    if(!isset($data['importfieldpath']))
    	{
		    $data['importfieldpath'] = '';
	    }

	    if(!isset($data['importfield']))
	    {
		    $data['importfield'] = '';
	    }

    	if($data['type'] === 'get_files')
    	{
    		$exs = explode(',', $params['filesimportexc']);
    		$directory = ($data['importfieldpath'] ? $data['importfieldpath'] . '/' : '') . preg_replace('/^root/isu', '', $data['directory']);
		    $directory = str_replace("//", '/', $directory);
		    $filesOutput = [];
		    $files = Folder::files(JPATH_ROOT . '/' .(string) $directory);
		    foreach ($files as $file)
		    {
		    	$tmpExs = explode('.', $file);

		    	if(!isset($tmpExs[1]))
		    	{
		    		continue;
			    }

			    if(in_array(array_pop($tmpExs), $exs))
			    {
				    $filesOutput[] = $file;
			    }
		    }

    		return $filesOutput;
	    }

	    if($data['type'] === 'upload_file')
	    {
		    $output = [];
    		$files = $app->input->files->getArray();
		    foreach ($files as $file)
		    {

			    if ($file['error'] == 4)
			    {
				    continue;
			    }

			    if ($file['error'])
			    {

				    switch ($file['error'])
				    {
					    case 1:
						    $output["error"] = Text::_('PLG_RADICAL_MULTI_FIELD_IMPORT_FILE_TO_LARGE_THAN_PHP_INI_ALLOWS');
						    break;

					    case 2:
						    $output["error"] = Text::_('PLG_RADICAL_MULTI_FIELD_IMPORT_FILE_TO_LARGE_THAN_HTML_FORM_ALLOWS');
						    break;

					    case 3:
						    $output["error"] = Text::_('PLG_RADICAL_MULTI_FIELD_IMPORT_ERROR_PARTIAL_UPLOAD');
				    }

			    }
			    else
		        {
				    $lang = Factory::getLanguage();
				    $nameSplit = explode('.', $file['name']);
				    $nameExs = array_pop($nameSplit);
				    $nameSafe = File::makeSafe($lang->transliterate(implode('-', $nameSplit)), ['#^\.#', '#\040#']);
				    $uploadedFileName =  $nameSafe . '_' . rand(11111, 99999) . '.' . $nameExs;

				    $exs = explode(',', $params['filesimportexc']);
				    $type = preg_replace("/^.*?\//isu", '', $file['type']);

				    if(!in_array($type, $exs))
				    {
					    $output["error"] = Text::_('PLG_RADICAL_MULTI_FIELD_IMPORT_ERROR_MIME_TYPE_UPLOAD') . ' (' . Text::_('PLG_RADICAL_MULTI_FIELD_IMPORT_ERROR_MIME_TYPE_LABEL'). ': ' . $type.')';
					    return $output;
				    }

			        $data['name'] = isset($data['name']) ? $data['name'] : '';

				    $path = JPATH_ROOT . '/' . ($data['importfieldpath'] ? $data['importfieldpath'] . '/' : '') . preg_replace('/^root/isu', '', $data['path']) . '/' . $data['name'];
				    $path = str_replace("//", '/', $path);

				    if (!file_exists($path))
				    {
					    Folder::create($path);
				    }

				    if (File::upload($file['tmp_name'], $path . "/" . $uploadedFileName))
				    {

				    	if(in_array($type, ['jpg', 'gif', 'png', 'jpeg', 'jpg', 'webp']))
				    	{
						    if((int)$params['filesimportresize'])
						    {

							    JLoader::registerNamespace('Gumlet', JPATH_SITE . '/' . implode(DIRECTORY_SEPARATOR , ['plugins', 'fields', 'radicalmultifield', 'libs', 'gumlet', 'lib']));

							    $image = new ImageResize($path . "/" . $uploadedFileName);

							    $maxWidth = (int)$params['filesimportrezizemaxwidth'];
							    $maxHeight = (int)$params['filesimportrezizemaxheight'];

							    //resize
							    $image->resizeToBestFit($maxWidth, $maxHeight);

							    //overlay
							    if((int)$params['filesimportrezizeoverlay'])
							    {

								    $file = JPATH_SITE . '/' . $params['filesimportrezizeoverlayfile'];
								    $position = $params['filesimportrezizeoverlaypos'];
								    $padding = $params['filesimportrezizeoverlaypadding'];

								    if(file_exists($file))
								    {

									    $image->addFilter(function ($imageDesc) use ($file, $position, $padding)
									    {

									    	if(!file_exists($file))
									    	{
									    	    return false;
										    }

										    $logo = imagecreatefromstring(file_get_contents($file));
										    $logoWidth = imagesx($logo);
										    $logoHeight = imagesy($logo);
										    $imageWidth = imagesx($imageDesc);
										    $imageHeight = imagesy($imageDesc);
										    $imageX = $padding;
										    $imageY = $padding;

										    if($logoWidth > $imageWidth && $logoHeight > $imageHeight)
										    {
											    return false;
										    }

										    switch ($position)
										    {

											    case "topleft":
												    $imageX = $padding;
												    $imageY = $padding;
												    break;

											    case "topcenter":
												    $imageX = ($imageWidth/2) - ($logoWidth/2);
												    $imageY = $padding;
												    break;

											    case "topright":
												    $imageX = $imageWidth - $padding - $logoWidth;
												    $imageY = $padding;
												    break;

											    case "centerleft":
												    $imageX = $padding;
												    $imageY = ($imageHeight/2) - ($logoHeight/2);
												    break;

											    case "centercenter":
												    $imageX = ($imageWidth/2) - ($logoWidth/2);
												    $imageY = ($imageHeight/2) - ($logoHeight/2);
												    break;

											    case "centerright":
												    $imageX = $imageWidth - $padding - $logoWidth;
												    $imageY = ($imageHeight/2) - ($logoHeight/2);
												    break;

											    case "bottomleft":
												    $imageX = $padding;
												    $imageY = $imageHeight - $padding - $logoHeight;
												    break;

											    case "bottomcenter":
												    $imageX = ($imageWidth/2) - ($logoWidth/2);
												    $imageY = $imageHeight - $padding - $logoHeight;
												    break;

											    case "bottomright":
												    $imageX = $imageWidth - $padding - $logoWidth;
												    $imageY = $imageHeight - $padding - $logoHeight;
												    break;

										    }

										    imagecopy($imageDesc, $logo, $imageX, $imageY, 0, 0, $logoWidth, $logoHeight);
									    });

								    }

							    }

							    $image->save($path . "/" . $uploadedFileName);

						    }
					    }

					    $output["name"] = $uploadedFileName;
				    }
				    else
			        {
					    $output["error"] = Text::_('PLG_RADICAL_MULTI_FIELD_IMPORT_ERROR_UPLOAD');
				    }
			    }
		    }

		    return $output;

	    }

	    if($data['type'] === 'create_directory')
	    {
		    $lang = Factory::getLanguage();
	    	$data['name'] = JFILE::makeSafe($lang->transliterate($data['name']));
    		$path = JPATH_ROOT . '/' . ($data['importfieldpath'] ? $data['importfieldpath'] . '/' : '') . preg_replace('/^root/isu', '', $data['path']) . '/' . $data['name'];
		    $path = str_replace("//", '/', $path);
    		Folder::create($path);

		    JLoader::register('FormFieldRadicalmultifieldtreecatalog', JPATH_ROOT . '/plugins/fields/radicalmultifield/elements/radicalmultifieldtreecatalog.php');
		    $treeCatalog = new FormFieldRadicalmultifieldtreecatalog;
		    $paramsForField = [
		    	'name' => 'select-directory',
		    	'label' => Text::_('PLG_RADICAL_MULTI_FIELD_FIELD_IMPORT_TREECATALOG_TITLE'),
		    	'class' => '',
		    	'type' => 'radicalmultifieldtreecatalog',
		    	'folder' => $data['importfieldpath'],
		    	'folderonly' => 'true',
		    	'showroot' => 'true',
		    ];

		    $dataAttributes = array_map(function($value, $key)
		    {
			    return $key . '="' . $value . '"';
		    }, array_values($paramsForField), array_keys($paramsForField));

		    $treeCatalog->setup(new SimpleXMLElement("<field " . implode(' ', $dataAttributes) . " />"), '');
		    return $treeCatalog->getInput(true);
	    }



	    
	    return [];
    }


}
