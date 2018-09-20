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
            <?php $firstRow = reset($values); ?>
            <?php foreach ($firstRow as $name => $value) : ?>
                <th><?= $listtype[$name]['title']; ?></th>
            <?php endforeach; ?>
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
			                $options = explode( "\n", $listtype[$name]['options']);
			                foreach ($data as $key => $value)
			                {
				                foreach ( $options as $option )
				                {
					                $sef = Joomla\CMS\Filter\OutputFilter::stringURLSafe( $option );
					                if($sef === $data[$key]) {
						                $data[$key] = $option;
					                }
				                }
			                }
			                $data = '<ul><li>' . implode('</li><li>', $data) . '</li></ul>';
		                }
		                else
		                {
			                $options = explode( "\n", $listtype[$name]['options']);
			                foreach ( $options as $option )
			                {
				                $value = Joomla\CMS\Filter\OutputFilter::stringURLSafe( $option );
				                if($value === $data) {
					                $data = $option;
				                }
			                }
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
