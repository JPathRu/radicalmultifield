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
<div class="uk-overflow-auto">
    <table class="uk-table uk-table-divider uk-table-striped uk-table-middle uk-table-hover">

        <thead>
        <tr>
			<?php $firstRow = reset($values); ?>
			<?php foreach ($firstRow as $name => $value) : ?>
                <th><?php echo $listtype[$name]['title'] ?></th>
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
								foreach ($data as $key => $value) {
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
							$data = \Joomla\CMS\Factory::getApplication()->loadIdentity($data)->name;
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
</div>
