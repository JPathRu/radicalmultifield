<?php use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Folder;

defined('JPATH_PLATFORM') or die;
/*

Usage:

<field
	name
	label
	class
	type="fileselect"
	folder="foldername"       // default 'images'
	folderonly="true|false"   // show directories only (val 'true') or directories && files (val 'false'), default 'false'
	showroot="true|false"     // show root directori no tree (val 'true'), default 'false'
/>

*/


JFormHelper::loadFieldClass('list');

class FormFieldRadicalmultifieldtreecatalog extends JFormField
{

	public $type = 'radicalmultifieldtreecatalog';

	protected $uid;

	protected function showdir
	(
		$dir,
		$folderOnly = false,
		$showRoot = false,
		$level = 0,  // do not use!!!
		$ef = ''     // do not use!!!
	)
	{
		$html = '';
		if ((int)$level == 0)
		{
			$dir = realpath($dir);
			$ef = ($showRoot ? realpath($dir . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR : $dir . DIRECTORY_SEPARATOR);
		}

		if (!file_exists($dir)) {
			return '';
		}

		if ($showRoot && (int)$level == 0)
		{
			$html = '<ul id="' . $this->uid . '" class="av-folderlist level-0' . '">';
			$subdir = $this->showdir($dir, $folderOnly, $showRoot, $level + 1, $ef);
			$name = substr(strrchr($dir, DIRECTORY_SEPARATOR), 1);
			$html .= '<li class="av-folderlist-item av-folderlist-dir">' . ($subdir ? '<span class="av-folderlist-tree"></span>' : '') . '<span class="av-folderlist-label" path="root">root</span>' . $subdir . '</li>';
			$html .= '</ul>';
		}
		else
		{
			$list = scandir($dir);
			if (is_array($list))
			{
				$list = array_diff($list, ['.', '..']);
				if ($list)
				{
					$folders = [];
					$files = [];

					foreach ($list as $name)
					{
						if (is_dir($dir . DIRECTORY_SEPARATOR . $name)) {
							$folders[] = $name;
						}
						else
						{
							$files[] = $name;
						}
					}

					if (!($folderOnly && !$folders) || !(!$folders || !$files))
					{
						$html .= '<ul' . ((int)$level == 0 ? ' id="' . $this->uid . '"' : '') . ' class="' . ((int)$level == 0 ? 'av-folderlist ' : '') . 'level-' . (int)$level . '">';
					}

					sort($folders);
					sort($files);

					foreach ($folders as $name)
					{
						$fpath = $dir . DIRECTORY_SEPARATOR . $name;
						$subdir = $this->showdir($fpath, $folderOnly, $showRoot, $level + 1, $ef);
						$fpath = str_replace($ef, '', $fpath);
						$fpath = preg_replace("/^.*?\//isu", 'root/', $fpath);
						$html .= '<li class="av-folderlist-item av-folderlist-dir">' . ($subdir ? '<span class="av-folderlist-tree"></span>' : '') . '<span class="av-folderlist-label" path="' . $fpath . '">' . $name . '</span>' . $subdir . '</li>';
					}

					if (!$folderOnly)
					{
						foreach ($files as $name)
						{
							$fpath = $dir . DIRECTORY_SEPARATOR . $name;
							$fpath = str_replace($ef, '', $fpath);
							$ext   = substr(strrchr($name, '.'), 1);
							$html  .= '<li class="av-folderlist-item .icon-file' . ($ext ? ' .icon-file-' . $ext : '') . '"><span class="av-folderlist-label" path="' . $fpath . '">' . $name . '</span></li>';
						}
					}

					if (!($folderOnly && !$folders) || !(!$folders || !$files))
					{
						$html .= '</ul>';
					}

					unset($folders, $files, $fpath, $ext);
				}
			}
		}

		return $html;
	}


	/**
	 * @param bool $only_directories
	 *
	 * @return string
	 */
	public function getInput($only_directories = false)
	{

		HTMLHelper::_('script', 'media/plg_fields_radicalmultifield/libs/vex/dist/js/vex.combined.min.js', [
			'version' => filemtime ( __FILE__ ),
			'relative' => false,
		]);

		HTMLHelper::_('stylesheet', 'media/plg_fields_radicalmultifield/libs/vex/dist/css/vex.css', [
			'version' => filemtime ( __FILE__ ),
			'relative' => false
		]);

		HTMLHelper::_('stylesheet', 'media/plg_fields_radicalmultifield/libs/vex/dist/css/vex-theme-plain.css', [
			'version' => filemtime ( __FILE__ ),
			'relative' => false
		]);


		$app = Factory::getApplication();

		// get attributes
		$importfield = $this->getAttribute('importfield');
		$exs = $this->getAttribute('exs');
		$namefield = $this->getAttribute('namefield');
		$namefile = $this->getAttribute('namefile');
		$maxsize = $this->getAttribute('maxsize');
		$folder = $this->getAttribute('folder');
		$folder = $folder ? $folder : 'images';
		$administrator = $app->isAdmin() ? 'true' : 'false';

		if(substr_count($folder, '{user_id}'))
		{
			$user = Factory::getUser();
		}
		else
		{
			$user = new stdClass();
			$user->id = 0;
		}

		if(substr_count($folder, '{item_id}'))
		{
			$item_id = Factory::getApplication()->input->get('id', '0');
		}
		else
		{
			$item_id = '0';
		}


		$folder = str_replace([
			'{user_id}',
			'{item_id}',
			'{year}',
			'{month}',
			'{day}',
			'{hours}',
			'{minutes}',
			'{second}',
			'{unix}',
		], [
			$user->id,
			$item_id,
			date('Y'),
			date('m'),
			date('d'),
			date('h'),
			date('i'),
			date('s'),
			date('U'),
		], $folder);

		$folders = explode(DIRECTORY_SEPARATOR, $folder);
		$currentTmp = '';

		foreach ($folders as $tmpFolder)
		{
			$currentTmp .= DIRECTORY_SEPARATOR . $tmpFolder;
			if(!file_exists(JPATH_ROOT . $currentTmp))
			{
				Folder::create(JPATH_ROOT . $currentTmp);
			}
		}

		$folderOnly = $this->getAttribute('folderonly');
		$folderOnly = ($folderOnly && (strtolower($folderOnly) === 'true' || strtolower($folderOnly) === 'folderonly') ? true : false);

		$showRoot = $this->getAttribute('showroot');
		$showRoot = ($showRoot && (strtolower($showRoot) === 'true' || strtolower($showRoot) === 'showroot') ? true : false);

		// get uniq id
		$this->uid = uniqid('avfl');

		if(!$only_directories)
		{
			// include jq && css
			HTMLHelper::_('jquery.framework', false, null, false);

			HTMLHelper::_('stylesheet', 'plg_fields_radicalmultifield/core/tree.css', [
				'version' => filemtime ( __FILE__ ),
				'relative' => true
			]);

			HTMLHelper::_('script', 'plg_fields_radicalmultifield/core/import.js', [
				'version' => filemtime ( __FILE__ ),
				'relative' => true
			]);


			$html = "<div class='import-wrap'>
		        <a href=\"#impot-modal-" . $this->uid . "\" role=\"button\" class=\"btn button-open-modal\" data-administrator=\"" . $administrator . "\" data-exs=\"" . $exs . "\" data-namefile=\"" . $namefile . "\" data-namefield=\"" . $namefield . "\" data-maxsize=\"" . $maxsize . "\" data-importfield=\"" . $importfield . "\" data-importfieldpath=\"" . $folder . "\" data-toggle=\"modal\"><span class='icon-archive'></span>" . Text::_('PLG_RADICAL_MULTI_FIELD_FIELD_IMPORT_MODAL_BUTTON_OPEN') . "</a>
		         <button role=\"button\" class=\"btn speed-upload\"><span class='icon-upload'></span> " . Text::_('PLG_RADICAL_MULTI_FIELD_FIELD_IMPORT_MODAL_BUTTON_SPEED_UPLOAD') . "</button>
		         
		        <!-- Modal -->
		        <div id=\"impot-modal-" . $this->uid . "\" class=\"modal modal-small modal-import-file hide fade\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"impot-modal-" . $this->uid . "\" aria-hidden=\"true\">
		          <div class=\"modal-header\">
		            <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">×</button>
		            <h3 id=\"impot-modal-" . $this->uid . "\">". Text::_('PLG_RADICAL_MULTI_FIELD_FIELD_IMPORT_MODAL_TITLE') . "</h3>
		          </div>
		          <div class=\"modal-body\">";

					$html .= '<div class="field-wrapper">';

					// input
					$html .= '<div class="input-append">';
					$html .= '<div class="input-wrap"><input type="text" name="' . $this->name .'" id="' . $this->id . '"' . ' class="import-directory ' . $this->class . '" value="' . $this->value . '" placeholder="' . Text::_('PLG_RADICAL_MULTI_FIELD_FIELD_IMPORT_MODAL_INPUT_PATH') . '" readonly ' . ($this->required ? 'required' : '') . '/></div>';

					// modal
					$html .= '<div id="' . $this->uid . 'modal" class="av-modal">';
					$html .= '<div class="av-modal-actions"><button class="btn btn-primary btn-small create-directory">' . Text::_('PLG_RADICAL_MULTI_FIELD_FIELD_IMPORT_MODAL_BUTTON_CREATE_PATH') . '</button><button class="tree-reload"><span></span></button><div class="search"><input type="text" name="" placeholder="'. Text::_('PLG_RADICAL_MULTI_FIELD_FIELD_IMPORT_MODAL_SEARCH') . '"/><div class="results"></div></div></div>';
					$html .= $this->showdir(JPATH_ROOT . DIRECTORY_SEPARATOR . $folder, $folderOnly, $showRoot);
					$html .= '</div>';
					$html .= '</div>';
					$html .= '</div>';

					JLoader::register('FormFieldRadicalmultifieldupload', JPATH_ROOT . '/plugins/fields/radicalmultifield/elements/radicalmultifieldupload.php');
					$upload = new FormFieldRadicalmultifieldupload;
					$upload->setup(new SimpleXMLElement("<field name=\"upload-files\" label=\"Загрузка файлов\" class=\"\" type=\"radicalmultifieldupload\" />"), '');

					$html .= '<div class="field-list-files"><div class="wrap-upload">' . $upload->getInput() . '</div><div class="list"></div></div>';

					$html .=  "</div>
		          <div class=\"modal-footer\">
		            <button class=\"btn\" data-dismiss=\"modal\" aria-hidden=\"true\">". Text::_('PLG_RADICAL_MULTI_FIELD_FIELD_IMPORT_MODAL_BUTTON_CLOSE') . "</button>
		            <button class=\"btn btn-primary button-import-start\">". Text::_('PLG_RADICAL_MULTI_FIELD_FIELD_IMPORT_MODAL_BUTTON_IMPORT_START') . "</button>
		          </div>
		        </div>
		        </div>";
		}
		else
		{
			$html = $this->showdir(JPATH_ROOT . DIRECTORY_SEPARATOR . $folder, $folderOnly, $showRoot);
		}

		return $html;
	}

}
