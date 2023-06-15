<?php use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die;

echo LayoutHelper::render(
	'repeatable-cards', $displayData,
	__DIR__ . '/' . (RadicalmultifieldHelper::isJoomla4() ? 'joomla4' : 'joomla3')
);
