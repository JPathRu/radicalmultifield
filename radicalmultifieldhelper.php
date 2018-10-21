<?php

use Joomla\CMS\Factory;
use Joomla\Filesystem\Folder;


defined('_JEXEC') or die;


/**
 * Class RadicalmultifieldHelper
 */
class RadicalmultifieldHelper
{


    /**
     * @param $field_id
     * @param $item_id
     * @return stdClass
     */
    private static function getFieldAndValue($field_id, $item_id)
    {
        $fieldAndValue = new stdClass();

        //get field
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName(array('context', 'title', 'name')))
            ->from($db->quoteName('#__fields'))
            ->where("id = " . $db->escape($field_id));
        $db->setQuery($query);
        $fieldAndValue->field = $db->loadObject();

        //get value
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName(array('field_id', 'item_id', 'value')))
            ->from($db->quoteName('#__fields_values'))
            ->where("field_id = " . $db->escape($field_id))
            ->where("item_id = " . $db->escape($item_id));
        $db->setQuery($query);
        $fieldAndValue->value = $db->loadObject();

        if(empty($fieldAndValue->value) || is_null($fieldAndValue->value))
        {
	        $fieldAndValue->value = new stdClass();
	        $fieldAndValue->value->value = '';
        }

        return $fieldAndValue;
    }


    /**
     * @param $field_id
     * @param $item_id
     * @param boolean
     */
    private static function saveFieldValue($field_id, $item_id, $value)
    {

        //get value
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName(array('field_id', 'item_id', 'value')))
            ->from($db->quoteName('#__fields_values'))
            ->where("field_id = " . $db->escape($field_id))
            ->where("item_id = " . $db->escape($item_id));
        $db->setQuery($query);
        $fieldValue = $db->loadObject();

        if(empty($fieldValue->value) || is_null($fieldValue->value))
        {
            $newValue = new stdClass();
            $newValue->field_id = (int) $field_id;
            $newValue->item_id = (int) $item_id;
            $newValue->value = $value;
            return Factory::getDbo()->insertObject('#__fields_values', $newValue);
        }
        else
        {
            $query = $db->getQuery(true);

            $fields = array(
                $db->quoteName('value') . ' = "' . $db->escape($value) . '"',
            );

            $conditions = array(
                $db->quoteName('field_id') . ' = ' . $db->escape($field_id),
                $db->quoteName('item_id') . ' = ' . $db->escape($item_id)
            );

            $query->update($db->quoteName('#__fields_values'))->set($fields)->where($conditions);
            $db->setQuery($query);
            $result = $db->execute();
            return $result;
        }

    }


    /**
     * @param $field_id
     * @param $item_id
     * @param array $data
     * @return bool
     */
    public static function add($field_id, $item_id, $data = [])
    {
        $fieldAndValue = self::getFieldAndValue($field_id, $item_id);

        $field = $fieldAndValue->field;
        $value = json_decode($fieldAndValue->value->value, JSON_OBJECT_AS_ARRAY);

        if(is_null($value))
        {
            $value = [];
        }

        $last_id = key( array_slice( $value, -1, 1, TRUE ) );
        $last_id = str_replace($field->name, '', $last_id);
        $value[(int)$last_id + 1] = $data;
        $value = json_encode($value);

        return self::saveFieldValue($field_id, $item_id, $value);
    }


	/**
	 * @param       $field_id
	 * @param       $item_id
	 * @param array $data
	 *
	 * @return bool|mixed
	 */
	public static function edit($field_id, $item_id, $data = [])
	{
		$fieldAndValue = self::getFieldAndValue($field_id, $item_id);

		$field = $fieldAndValue->field;
		$value = json_decode($fieldAndValue->value->value, JSON_OBJECT_AS_ARRAY);


		if(isset($data['_id']))
		{

			if(isset($value[$field->name . (int)$data['_id']]))
			{
				$value[$field->name . (int)$data['_id']] = $data;
			}
			else
			{
				return false;
			}

		}
		else
		{

			foreach ($value as $key => $item)
			{

				$findTmp = true;

				foreach ($data as $name => $search)
				{

					if (!isset($item[$name]) || ((string)$search !== (string)$item[$name]))
					{
						$findTmp = false;
						break;
					}

				}

				if($findTmp)
				{
					$value[$key] = $data;
					break;
				}

			}

		}

		$value = json_encode($value);

		return self::saveFieldValue($field_id, $item_id, $value);
	}


    /**
     * @param $field_id
     * @param $item_id
     * @param array $data
     * @return bool
     */
    public static function delete($field_id, $item_id, $data = [])
    {
        $fieldAndValue = self::getFieldAndValue($field_id, $item_id);

        $field = $fieldAndValue->field;
        $value = json_decode($fieldAndValue->value->value, JSON_OBJECT_AS_ARRAY);

        if(isset($data['_id'])) {

            if(isset($value[$field->name . (int)$data['_id']]))
            {
                unset($value[$field->name . (int)$data['_id']]);
            }
            else
            {
                return false;
            }

        }
        else
        {

            foreach ($value as $key => $item)
            {

                $findTmp = true;

                foreach ($data as $name => $search)
                {
                    if (!isset($item[$name]) || ((string)$search !== (string)$item[$name]))
                    {
                        $findTmp = false;
                        break;
                    }
                }

                if($findTmp)
                {
                    unset($value[$key]);
                    break;
                }
            }

        }

        $value = json_encode($value);

        return self::saveFieldValue($field_id, $item_id, $value);
    }


    /**
     * @param $field_id
     * @param $item_id
     * @param array $data
     * @param bool $column_find_all
     * @return array
     */
    public static function check($field_id, $item_id, $data = [], $column_find_all = true)
    {
        $fieldAndValue = self::getFieldAndValue($field_id, $item_id);

        $value = json_decode($fieldAndValue->value->value, JSON_OBJECT_AS_ARRAY);
        $find = false;
        $countFind = 0;

        foreach ($value as $key => $item)
        {

            if($column_find_all)
            {
                $findTmp = true;
            }
            else
            {
                $findTmp = false;
            }

            foreach ($item as $keyRow => $row)
            {

                if($column_find_all) {

                    if(!isset($data[$keyRow]) || ((string)$row !== (string)$data[$keyRow]))
                    {
                        $findTmp = false;
                        break;
                    }

                } else {

                    if(isset($data[$keyRow]) && ((string)$row == (string)$data[$keyRow]))
                    {
                        $findTmp = true;
                        break;
                    }

                }

            }

            if($findTmp)
            {
                $countFind++;
            }

            if(!$find && $findTmp)
            {
                $find = true;
            }
        }

        return [
            'find' => $find,
            'count' => $countFind,
        ];
    }


	/**
	 * @param string $fieldname
	 *
	 * @return mixed
	 */
    public static function getParams($fieldname = '')
    {
	    $db = Factory::getDBO();
	    $query = $db->getQuery( true )
		    ->select( 'fieldparams' )
		    ->from( '#__fields' )
		    ->where( 'name=' . $db->quote( $fieldname ) );
	    $field = $db->setQuery( $query )->loadResult();

	    $params = json_decode( $field, JSON_OBJECT_AS_ARRAY );

	    return $params;
    }


	/**
	 * @param $path
	 *
	 * @return array|bool|string
	 */
	public static function loadClassExtendField($path)
	{
		$path = JPATH_ROOT . DIRECTORY_SEPARATOR . $path;
		$files = Folder::files($path);
		$className = false;
		$fileListsName = [];

		foreach ($files as $file)
		{
			$name = str_replace('.php', '', $file);
			$className = 'FormField' . ucfirst($name);
			JLoader::register($className, $path . DIRECTORY_SEPARATOR . $file);

			if (!class_exists($className))
			{

				$className = 'JFormField' . ucfirst($name);
				JLoader::register($className, $path . DIRECTORY_SEPARATOR . $file);

				if (!class_exists($className))
				{
					return [];
				}
				else
				{
					$fileListsName[] = $name;
				}

			}
			else
			{
				$fileListsName[] = $name;
			}


		}

		return $fileListsName;
	}

}