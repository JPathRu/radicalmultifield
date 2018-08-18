<?php
/**
 * @package    Radical MultiField
 *
 * @author     delo-design.ru <info@delo-design.ru>
 * @copyright  Copyright (C) 2018 "Delo Design". All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://delo-design.ru
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;

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
        Factory::getDocument()->addScript('/plugins/fields/radicalmultifield/assets/js/radicalmultifield.js');

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

        foreach ( $params->get( 'listtype', [] ) as $option )
        {
            $data[ $option[ 'name' ] ] = [
                "title" => $option[ 'title' ],
                "type" => $option[ 'type' ],
            ];
        }

        return $data;
    }



}
