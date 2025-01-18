<?php
/**
 * @package    Radical MultiField
 *
 * @author     delo-design.ru <info@delo-design.ru>
 * @copyright  Copyright (C) 2018 "Delo Design". All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://delo-design.ru
 */

defined('_JEXEC') or die;

use Joomla\Plugin\Fields\RadicalMultiField\Helper\RadicalMultiFieldHelper;

if (!$field->value)
{
	return;
}

$values   = json_decode($field->value, JSON_OBJECT_AS_ARRAY);
$listtype = $this->getListTypeFromField($field);

?>


<div class="uk-grid uk-grid-width-medium-1-5" data-uk-grid-match="{target:'img'}" data-uk-grid-margin>
	<?php foreach ($values as $key => $row): ?>

		<?php
		$preview = '';
		if (preg_match("/^(https?\:\/\/)?(www\.youtube\.com|youtu\.?be)\/.+$/isu", $row['image']))
		{
			$link    = preg_replace("|.*?watch\?v\=|isu", '', $row['image']);
			$link    = preg_replace("|.*?\.be\/|isu", '', $link); //для коротких ссылок
			$link    = preg_replace("|[\&\?]{1,}.+|isu", '', $link);
			$preview = 'https://img.youtube.com/vi/' . $link . '/hqdefault.jpg';
		}
		else
		{
			$preview = RadicalMultiFieldHelper::generateThumb($field, $row['image']);
		}
		?>

        <div>
            <a href="<?php $row['image'] ?>" data-uk-lightbox="{group:'group-fields-<?php $field->id ?>'}"
               title="<?php $row['alt'] ?>">
                <img src="<?php $preview ?>" alt="<?php $row['alt'] ?>"/>
            </a>
        </div>

	<?php endforeach; ?>
</div>