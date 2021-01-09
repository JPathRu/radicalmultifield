<?php use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\FileLayout;

defined('JPATH_PLATFORM') or die;

/**
 * Class FormFieldRadicalmultifieldupload
 */
class FormFieldRadicalmultifieldupload extends JFormField
{

	/**
	 * @var string
	 */
	public $type = 'radicalmultifieldupload';


	/**
	 * @return string
	 */
	public function getInput()
	{
		HTMLHelper::_('jquery.framework', false, null, false);

		HTMLHelper::_('stylesheet', 'plg_fields_radicalmultifield/core/upload.css', [
			'version' => filemtime ( __FILE__ ),
			'relative' => true
		]);

		$layout = new FileLayout('upload', JPATH_ROOT . '/plugins/fields/radicalmultifield/elements/tmpl');
		return $layout->render();
	}

}
