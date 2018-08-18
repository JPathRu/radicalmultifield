<?php
/**
 * @package    Radical MultiField
 *
 * @author     Aleksey A. Morozov (AlekVolsk) <https://github.com/AlekVolsk>
 * @copyright  Copyright (C) 2018 Aleksey A. Morozov (AlekVolsk). All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://alekvolsk.pw
 *
 */

defined('_JEXEC') or die;

if (!$field->value)
{
    return;
}

$values = json_decode($field->value, JSON_OBJECT_AS_ARRAY);

$listtype = $this->getListTypeFromField($field);

?>
<table class="table">

    <thead>
        <tr>
            <?php foreach ($values[$field->name . '0'] as $name => $value) { ?>
            <th><?php echo $listtype[$name]['title']; ?></th>
            <?php } ?>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($values as $row => $column) { ?>
        <tr>
            <?php
            foreach ($column as $name => $data)
            {
                switch ($listtype[$name]['type'])
                {
                    case 'list':
                        if (is_array($data))
                        {
                            $data = '<ul><li>' . implode('</li><li>', $data) . '</li></ul>';
                        }
                        break;

                    case 'media':
                        $data = "<img src=\"{$data}\" alt=\"\">";
                        break;

                    case 'user':
                        $data = \Joomla\CMS\Factory::getUser($data)->name;
                        break;

                    case 'color':
                        $data = "<span style=\"display:inline-block;width:1em;height:1em;background-color:{$data}\"></span> " . $data;
                        break;

                    default:
                }
            ?>
            <td><?php echo $data; ?></td>
            <?php } ?>
        </tr>
        <?php } ?>
    </tbody>

</table>
