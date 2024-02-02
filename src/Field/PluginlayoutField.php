<?php namespace Joomla\Plugin\Fields\RadicalMultiField\Field;

/**
 * @package    Radical MultiField
 *
 * @author     Aleksey A. Morozov (AlekVolsk) <https://github.com/AlekVolsk>
 * @copyright  Copyright (C) 2018 Aleksey A. Morozov (AlekVolsk). All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://alekvolsk.pw
 *
 */

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

defined('_JEXEC') or die;

/**
 *
 * Form Field to display a list of the layouts for plugin display from the plugin or template overrides.
 *
 * @since  1.6
 *
 * Class PluginlayoutField
 */
class PluginlayoutField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $type = 'Pluginlayout';

	/**
	 * Method to get the field input for plugin layouts.
	 *
	 * @return  string  The field input.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		// Get the client id.
		$clientId = $this->element['client_id'];

		if ($clientId === null && $this->form instanceof Form)
		{
			$clientId = $this->form->getValue('client_id');
		}

		$clientId = (int) $clientId;

		$client = ApplicationHelper::getClientInfo($clientId);

		// Get the plugin.
		if (($this->form instanceof Form))
		{
			$plugin = $this->form->getValue('type');
		}

		if (substr_count($plugin, '_') > 0)
		{
			$tmp            = explode('_', $plugin);
			$plugin         = $tmp[1];
			$pluginOriginal = $tmp[0];
			$plugin         = preg_replace('#\W#', '', $plugin);
			$folder         = 'radicalmultifield';
			$pluginFullName = 'plg_' . $folder . '_' . $plugin;
		}
		else
		{
			$plugin         = preg_replace('#\W#', '', $plugin);
			$folder         = 'fields';
			$pluginFullName = 'plg_' . $folder . '_' . $plugin;
		}

		// Get the template.
		$template = (string) $this->element['template'];
		$template = preg_replace('#\W#', '', $template);

		// Get the style.
		$template_style_id = '';
		if ($this->form instanceof Form)
		{
			$template_style_id = $this->form->getValue('template_style_id');
			$template_style_id = preg_replace('#\W#', '', $template_style_id);
		}

		// If an extension and view are present build the options.
		if ($plugin && $client)
		{
			// Load language file
			$lang = Factory::getLanguage();
			$lang->load($plugin . '.sys', $client->path, null, false, true)
			|| $lang->load($plugin . '.sys', $client->path . '/plugins/' . $folder . '/' . $plugin, null, false, true);

			// Get the database object and a new query object.
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			// Build the query.
			$query->select('element, name')
				->from('#__extensions as e')
				->where('e.client_id = ' . (int) $clientId)
				->where('e.type = ' . $db->quote('template'))
				->where('e.enabled = 1');

			if ($template)
			{
				$query->where('e.element = ' . $db->quote($template));
			}

			if ($template_style_id)
			{
				$query->join('LEFT', '#__template_styles as s on s.template=e.element')
					->where('s.id=' . (int) $template_style_id);
			}

			// Set the query and load the templates.
			$db->setQuery($query);
			$templates = $db->loadObjectList('element');

			// Build the search paths for plugin layouts.
			$plugin_path = realpath(Path::clean($client->path . '/plugins/' . $folder . '/' . $plugin . '/tmpl'));

			// Prepare array of component layouts
			$plugin_layouts = [];

			// Prepare the grouped list
			$groups = [];

			// Add the layout options from the plugin path.
			if (is_dir($plugin_path) && ($plugin_layouts = Folder::files($plugin_path, '^[^_]*\.php$')))
			{
				// Create the group for the plugin
				$groups['_']          = [];
				$groups['_']['id']    = $this->id . '__';
				$groups['_']['text']  = Text::sprintf('JOPTION_FROM_PLUGIN');
				$groups['_']['items'] = [];

				foreach ($plugin_layouts as $file)
				{
					// Add an option to the plugin group
					$value                  = basename($file, '.php');
					$text                   = $lang->hasKey($key = strtoupper($plugin . '_LAYOUT_' . $value)) ? Text::_($key) : $value;
					$groups['_']['items'][] = HTMLHelper::_('select.option', '_:' . $value, $text);
				}
			}

			// Loop on all templates
			if ($templates)
			{
				foreach ($templates as $template)
				{
					// Load language file
					$lang->load('tpl_' . $template->element . '.sys', $client->path, null, false, true)
					|| $lang->load('tpl_' . $template->element . '.sys', $client->path . '/templates/' . $template->element, null, false, true);

					$template_path = Path::clean($client->path . '/templates/' . $template->element . '/html/plg_' . $folder . '_' . $plugin);

					// Add the layout options from the template path.
					if (is_dir($template_path) && ($files = Folder::files($template_path, '^[^_]*\.php$')))
					{
						foreach ($files as $i => $file)
						{
							// Remove layout that already exist in component ones
							if (in_array($file, $plugin_layouts))
							{
								unset($files[$i]);
							}
						}

						if (count($files))
						{
							// Create the group for the template
							$groups[$template->element]          = [];
							$groups[$template->element]['id']    = $this->id . '_' . $template->element;
							$groups[$template->element]['text']  = Text::sprintf('JOPTION_FROM_TEMPLATE', $template->name);
							$groups[$template->element]['items'] = [];

							foreach ($files as $file)
							{
								// Add an option to the template group
								$value                                 = basename($file, '.php');
								$text                                  = $lang->hasKey($key = strtoupper('TPL_' . $template->element . '_' . $plugin . '_LAYOUT_' . $value))
									? Text::_($key) : $value;
								$groups[$template->element]['items'][] = HTMLHelper::_('select.option', $template->element . ':' . $value, $text);
							}
						}
					}
				}
			}
			// Compute attributes for the grouped list
			$attr = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
			$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

			// Prepare HTML code
			$html = [];

			// Compute the current selected values
			$selected = [$this->value];

			// Add a grouped list
			$html[] = HTMLHelper::_(
				'select.groupedlist', $groups, $this->name,
				['id' => $this->id, 'group.id' => 'id', 'list.attr' => $attr, 'list.select' => $selected]
			);

			return implode($html);
		}
		else
		{
			return '';
		}
	}
}
