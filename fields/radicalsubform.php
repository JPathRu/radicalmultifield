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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('subform');
JFormHelper::loadFieldClass('folderlist');

/**
 * Class JFormFieldRadicalsubform
 */
class JFormFieldRadicalsubform extends JFormFieldSubform
{


	/**
	 * @var string
	 */
	public $type = 'Radicalsubform';


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


}