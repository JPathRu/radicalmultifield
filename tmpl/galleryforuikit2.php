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


<div class="uk-grid uk-grid-width-medium-1-5">
    <?php foreach ($values as $key => $row): ?>

        <div>
            <a href="<?= $row['image']?>" data-uk-lightbox title="<?= $row['alt'] ?>">
                <img src="<?= $row['image']?>" alt="<?= $row['alt'] ?>"/>
            </a>
        </div>

    <?php endforeach; ?>
</div>