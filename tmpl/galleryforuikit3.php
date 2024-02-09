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
$values = json_decode($field->value, JSON_OBJECT_AS_ARRAY);
$listtype = $this->getListTypeFromField($field);
$height = (int)$field->fieldparams->get('filesimportpreviewmaxheight');
if($height === 0) {
    $height = 250;
}

?>

<div class="uk-child-width-1-4@m" uk-grid  uk-lightbox="animation: slide">
	<?php foreach ($values as $key => $row): ?>

        <?php
            $preview = '';
            if(preg_match("/^(https?\:\/\/)?(www\.youtube\.com|youtu\.?be)\/.+$/isu", $row['image']))
            {
                $link = preg_replace("|.*?watch\?v\=|isu", '', $row['image']);
                $link = preg_replace("|.*?\.be\/|isu", '', $link); //для коротких ссылок
                $link = preg_replace("|[\&\?]{1,}.+|isu", '', $link);
	            $preview = 'https://img.youtube.com/vi/'. $link . '/hqdefault.jpg';
            }
            else
            {
	            $preview = RadicalMultiFieldHelper::generateThumb($field, $row['image']);
            }
	    ?>

        <div>
            <div class="uk-transition-toggle uk-inline-clip">
                <canvas width="400" height="<?= $height ?>"></canvas>
                <img class="uk-cover uk-responsive-height" src="<?= $preview ?>" alt="<?= $row['alt'] ?>"/>
                <div class="uk-position-cover uk-overlay uk-overlay-primary uk-transition-fade"></div>
                <a class="uk-position-cover" href="<?= $row['image'] ?>" data-caption="<?= $row['alt'] ?>"></a>
            </div>
        </div>

	<?php endforeach; ?>
</div>