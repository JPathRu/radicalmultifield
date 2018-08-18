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
     * @return string
     */
    public function getInput()
    {
        $this->multiple = true;
        $this->min = 1;
        $this->buttons =  [ 'add' => true, 'remove' => true, 'move' => true ];

        $db = Factory::getDBO();
        $query = $db->getQuery( true )
            ->select( 'fieldparams' )
            ->from( '#__fields' )
            ->where( 'name=' . $db->quote( $this->fieldname ) );
        $field = $db->setQuery( $query )->loadResult();

        $params = json_decode( $field, JSON_OBJECT_AS_ARRAY );

        $this->layout = $params[ 'aview' ];
        $this->formsource = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><form>";

        foreach ( $params[ 'listtype' ] as $param )
        {
            switch ( $param[ 'type' ] )
            {
                case 'list':
                    $required = $param[ 'required' ] ? " required=\"required\"" : '';
                    $multiple = $param[ 'multiple' ] ? " multiple=\"true\"" : '';
                    $class = $param[ 'listview' ] == 'radio' ? " class=\"btn-group\"" : '';
                    $attrs = trim( $param[ 'attrs' ] );

                    $this->formsource .= "<field name=\"{$param['name']}\" type=\"{$param['listview']}\" label=\"{$param['title']}\"{$required}{$multiple}{$class} {$attrs}>";
                    $options = explode( "\n", $param[ 'options' ] );
                    foreach ( $options as $option )
                    {
                        $value = OutputFilter::stringURLSafe( $option );
                        $this->formsource .= "<option value=\"{$value}\">{$option}</option>";
                    }
                    $this->formsource .= "</field>";
                    break;

                case 'editor':
                    $attrs = trim( $param[ 'attrs' ] );
                    $this->formsource .= "<field name=\"{$param['name']}\" type=\"{$param['type']}\" label=\"{$param['title']}\" filter=\"raw\" {$attrs}/>";
                    break;

                default:
                    $attrs = trim( $param[ 'attrs' ] );
                    $this->formsource .= "<field name=\"{$param['name']}\" type=\"{$param['type']}\" label=\"{$param['title']}\" {$attrs}/>";
            }
        }

        $this->formsource .= "</form>";

        return parent::getInput();
    }

}