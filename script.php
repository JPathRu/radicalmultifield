<?php defined('_JEXEC') or die;

/**
 * @package    Radical MultiField
 *
 * @author     delo-design.ru <info@delo-design.ru>
 * @copyright  Copyright (C) 2018 "Delo Design". All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://delo-design.ru
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * Class plgSystemRadicalmultifieldInstallerScript
 */
class plgFieldsRadicalmultifieldInstallerScript
{

	/**
	 * @param $type
	 * @param $parent
	 *
	 * @throws Exception
	 */
	function postflight($type, $parent)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->update('#__extensions')
			->set('enabled=1')
			->where('type=' . $db->q('plugin'))
			->where('element=' . $db->q('radicalmultifield'));
		$db->setQuery($query)->execute();
	}

	/**
	 * @param $type
	 * @param $parent
	 *
	 * @throws Exception
	 */
	function preflight($type, $parent)
	{
		if ((version_compare(PHP_VERSION, '5.6.0') < 0))
		{
			Factory::getApplication()->enqueueMessage(Text::_('PLG_RADICAL_MULTI_FIELD_WRONG_PHP'), 'error');

			return false;
		}
	}
}
