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
<dl>
    <?php
    foreach ($values as $row => $column)
    {
        foreach ($column as $name => $data)
        {
            $columnName = $listtype[$name]['title'];

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
    
    <dt><?php echo $columnName; ?></dt>
    <dd><?php echo $data; ?></dd>
    
    <?php
        }
    }
    ?>
</dl>
