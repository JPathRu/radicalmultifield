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


    protected static $_cache_params = [];


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
     * @param $field_id
     * @param $item_id
     * @param boolean
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

        if(self::$_cache_params[$fieldname])
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
	 * @param $path
	 *
	 * @return array|bool|string
	 */
	public static function loadClassExtendField($path)
	{
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
     * @param $fieldOrParams
     * @param $source
     * @param null $thumb_path - example: cache/my_cache
     * @return mixed|string|string[]
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
            $pathThumb = implode(DIRECTORY_SEPARATOR, array_merge($paths, ['_thumb']));
            $pathFileThumb = implode(DIRECTORY_SEPARATOR, array_merge($paths, ['_thumb'])) . DIRECTORY_SEPARATOR . $file;
        }
		else
        {
            $pathThumb = Path::clean($thumb_path . DIRECTORY_SEPARATOR . '_thumb');
            $pathFileThumb = Path::clean($thumb_path . DIRECTORY_SEPARATOR . '_thumb' . DIRECTORY_SEPARATOR . $file);
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

		if(isset($params['filesimportpreviewfolder']) && ($params['filesimportpreviewfolder'] === 'cache'))
        {
            $pathThumb = Path::clean(DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'plg_fields_multifields' . DIRECTORY_SEPARATOR. $pathThumb);
            $pathFileThumb = Path::clean(DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'plg_fields_multifields' . DIRECTORY_SEPARATOR . $pathFileThumb);
        }

		$fullPathThumb =  JPATH_ROOT . DIRECTORY_SEPARATOR . $pathThumb . DIRECTORY_SEPARATOR . $file;

		//если есть превью, то отдаем ссылку на файл
		if(file_exists($fullPathThumb))
		{
			return $pathFileThumb;
		}

		//если нет, генерируем превью

        //проверяем создан ли каталог для превью
        $pathThumbSplit = explode(DIRECTORY_SEPARATOR, $pathThumb);
		$pathThumbCurrent = '';
		foreach ($pathThumbSplit as $pathCurrentCheck)
        {
            $pathThumbCurrent .= DIRECTORY_SEPARATOR . $pathCurrentCheck;
            $pathThumbCheck = Path::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $pathThumbCurrent);
            if(!file_exists($pathThumbCheck))
            {
                //создаем каталог
                Folder::create($pathThumbCheck);
            }
        }


		//подгружаем библиотеку для фото
        JLoader::register('JInterventionimage', JPATH_LIBRARIES . DIRECTORY_SEPARATOR . 'jinterventionimage' . DIRECTORY_SEPARATOR . 'jinterventionimage.php');

		if(!isset($params['filesimportpreviewmaxwidth']) || !isset($params['filesimportpreviewmaxheight']))
		{
			return $source;
		}

		$overlayAccept = true;

		if((int)$params['filesimportreoriginal'])
		{
			$originalFile = implode(DIRECTORY_SEPARATOR, array_merge($paths, ['_original'])) . DIRECTORY_SEPARATOR . $source;

			if(!file_exists($originalFile))
			{
				$originalFile = JPATH_ROOT . DIRECTORY_SEPARATOR . $source;
				$overlayAccept = false;
			}

		}
		else
        {
			$originalFile = JPATH_ROOT . DIRECTORY_SEPARATOR . $source;
			$overlayAccept = false;
		}

		if(copy(JPATH_ROOT . DIRECTORY_SEPARATOR . $source, $fullPathThumb)) {

			$maxWidth = (int)$params['filesimportpreviewmaxwidth'];
			$maxHeight = (int)$params['filesimportpreviewmaxheight'];

            $manager = JInterventionimage::getInstance(['driver' => self::getNameDriver()]);
            $manager
                ->make($fullPathThumb)
                ->fit($maxWidth, $maxHeight, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->save($fullPathThumb);

			unset($manager);
		}

		return $pathFileThumb;

	}


    /**
     *
     * @return string
     *
     * @since version
     */
    public static function getNameDriver()
    {
        if (extension_loaded('imagick'))
        {
            return 'imagick';
        }

        return 'gd';
    }


}