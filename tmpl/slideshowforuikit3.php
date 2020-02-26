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

if (!$field->value)
{
	return;
}

$values = json_decode($field->value, JSON_OBJECT_AS_ARRAY);
$listtype = $this->getListTypeFromField($field);

?>

<div uk-slideshow="animation: push">

    <div class="uk-position-relative uk-visible-toggle uk-light">

        <ul class="uk-slideshow-items">
			<?php foreach ($values as $key => $row): ?>

                <li>
                    <img src="<?= $row['image']?>" alt="<?= isset($row['alt'])?$row['alt']:"" ?>" uk-cover/>
                </li>

			<?php endforeach; ?>
        </ul>

        <a class="uk-position-center-left uk-position-small uk-hidden-hover" href="#" uk-slidenav-previous uk-slideshow-item="previous"></a>
        <a class="uk-position-center-right uk-position-small uk-hidden-hover" href="#" uk-slidenav-next uk-slideshow-item="next"></a>

    </div>

    <ul class="uk-slideshow-nav uk-dotnav uk-flex-center uk-margin"></ul>

</div>
