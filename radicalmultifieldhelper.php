<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\Filesystem\Folder;
use Gumlet\ImageResize;


defined('_JEXEC') or die;


/**
 * Class RadicalmultifieldHelper
 */
class RadicalmultifieldHelper
{


    /**
     * @var array
     */
    protected static $_cache_params = [];


    /**
     * @param int $field_id
     * @param int $item_id
     *
     * @return stdClass
     */
    private static function getFieldAndValue($field_id, $item_id)
    {
        $fieldAndValue = new stdClass();

        //get field
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->qn(array('context', 'title', 'name')))
            ->from($db->qn('#__fields'))
            ->where("id = " . $db->q($field_id));
        $db->setQuery($query);
        $fieldAndValue->field = $db->loadObject();

        //get value
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->qn(array('field_id', 'item_id', 'value')))
            ->from($db->qn('#__fields_values'))
            ->where("field_id = " . $db->q($field_id))
            ->where("item_id = " . $db->q($item_id));
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
     * @param int $field_id
     * @param int $item_id
     * @param mixed $value
     *
     * @return bool
     */
    private static function saveFieldValue($field_id, $item_id, $value)
    {

        //get value
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->qn(array('field_id', 'item_id', 'value')))
            ->from($db->qn('#__fields_values'))
            ->where("field_id = " . $db->q($field_id))
            ->where("item_id = " . $db->q($item_id));
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
                $db->qn('value') . ' = "' . $db->q($value) . '"',
            );

            $conditions = array(
                $db->qn('field_id') . ' = ' . $db->q($field_id),
                $db->qn('item_id') . ' = ' . $db->q($item_id)
            );

            $query->update($db->quoteName('#__fields_values'))->set($fields)->where($conditions);
            $db->setQuery($query);
            $result = $db->execute();
            return $result;
        }

    }


    /**
     * @param int $field_id
     * @param int $item_id
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
	 * @param int $field_id
	 * @param int $item_id
	 * @param array $data
	 *
	 * @return bool
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
     * @param int $field_id
     * @param int $item_id
     * @param array $data
     *
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
     * @param int $field_id
     * @param int $item_id
     * @param array $data
     * @param bool $column_find_all
     *
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

                if($column_find_all)
                {

                    if(!isset($data[$keyRow]) || ((string)$row !== (string)$data[$keyRow]))
                    {
                        $findTmp = false;
                        break;
                    }

                }
                else
                {

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
     * @return bool
     */
    public static function checkQuantumManager()
    {
        $db = Factory::getDBO();
        $query = $db->getQuery( true )
            ->select( 'extension_id' )
            ->from( '#__extensions' )
            ->where( $db->qn('type') . ' = ' . $db->q('component'))
            ->where( $db->qn('element') . ' = ' . $db->q('com_quantummanager'));
        $extension = $db->setQuery( $query )->loadObject();

        if(!empty($extension->extension_id))
        {
            return true;
        }

        return false;
    }


	/**
	 * @param string $fieldname
	 *
	 * @return mixed
	 */
    public static function getParams($fieldname = '')
    {

        if(isset(self::$_cache_params[$fieldname]))
        {
            return self::$_cache_params[$fieldname];
        }

	    $db = Factory::getDBO();
	    $query = $db->getQuery( true )
		    ->select( 'fieldparams' )
		    ->from( '#__fields' )
		    ->where( 'name=' . $db->q( $fieldname ) );
	    $field = $db->setQuery( $query )->loadResult();

	    $params = json_decode( $field, JSON_OBJECT_AS_ARRAY );
        self::$_cache_params[$fieldname] = $params;
	    return self::$_cache_params[$fieldname];
    }


	/**
	 * @param string $path
	 *
	 * @return array
	 */
	public static function loadClassExtendField($path)
	{

	    if(empty($path))
        {
            return [];
        }

		$path = Path::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $path);
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
					continue;
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


    /**
     * @param \Joomla\CMS\Form\FormField|array $fieldOrParams
     * @param string $source
     * @param string $thumb_path example: cache/my_cache
     *
     * @return string
     */
	public static function generateThumb(&$fieldOrParams, $source, $thumb_path = null)
	{
		$source = str_replace(JPATH_ROOT . DIRECTORY_SEPARATOR, '', $source);
		$paths = explode(DIRECTORY_SEPARATOR, $source);
		$file = array_pop($paths);
		$fileSplit = explode('.', $file);
		$fileExt = mb_strtolower(array_pop($fileSplit));
		$extAccept = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

		if(!in_array($fileExt, $extAccept))
		{
			return $file;
		}

		if($thumb_path === null)
        {
            $thumb_path = implode(DIRECTORY_SEPARATOR, array_merge($paths));
        }

        $params = [];

        if(is_object($fieldOrParams))
        {
            if(!isset($fieldOrParams->name))
            {
                return $source;
            }

            //подгружаем параметры поля
            $params = self::getParams($fieldOrParams->name);

        }

        if(is_array($fieldOrParams))
        {
            $params = $fieldOrParams;
        }

		if(isset($params['filesimportpreviewfolder']))
        {
            if($params['filesimportpreviewfolder'] === 'cache')
            {
                $thumb_path = Path::clean(DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'plg_fields_multifields' . DIRECTORY_SEPARATOR. $thumb_path);
            }

            if($params['filesimportpreviewfolder'] === 'generatedimages')
            {
                $thumb_path = Path::clean(DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'generatedimages' . DIRECTORY_SEPARATOR . 'plg_fields_multifields' . DIRECTORY_SEPARATOR. $thumb_path);
            }
        }

		$maxWidth = (int)$params['filesimportpreviewmaxwidth'];
        $maxHeight = (int)$params['filesimportpreviewmaxheight'];
        $algorithm = $params['filesimportpreviewalgorithm'];

		//если нет, генерируем превью
        JLoader::register('JInterventionimage', JPATH_ROOT . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['libraries', 'jinterventionimage', 'jinterventionimage.php']));
        return JInterventionimage::generateThumb($source, $maxWidth, $maxHeight, $algorithm, $thumb_path);
	}


}