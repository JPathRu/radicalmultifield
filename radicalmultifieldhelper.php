<?php

use Joomla\CMS\Factory;
use Joomla\Filesystem\Folder;
use Gumlet\ImageResize;


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


	/**
	 * @param $fieldOrParams
	 * @param $source
	 *
	 * @return mixed|string
	 */
	public static function generateThumb(&$fieldOrParams, $source)
	{
		$source = str_replace(JPATH_ROOT . DIRECTORY_SEPARATOR, '', $source);
		$paths = explode(DIRECTORY_SEPARATOR, $source);
		$file = array_pop($paths);
		$fileSplit = explode('.', $file);
		$fileExt = mb_strtolower(array_pop($fileSplit));
		$extAccept = ['jpg', 'jpeg', 'png', 'gif'];

		if(!in_array($fileExt, $extAccept))
		{
			return $file;
		}

		$pathThumb = implode(DIRECTORY_SEPARATOR, array_merge($paths, ['_thumb']));
		$pathFileThumb = implode(DIRECTORY_SEPARATOR, array_merge($paths, ['_thumb'])) . DIRECTORY_SEPARATOR . $file;
		$fullPathThumb =  JPATH_ROOT . DIRECTORY_SEPARATOR . $pathThumb . DIRECTORY_SEPARATOR . $file;

		//если есть проевью, то отдаем ссылку на файл
		if(file_exists($fullPathThumb))
		{
			return $pathFileThumb;
		}

		//если нет, генерируем превью

		//проверяем создан ли каталог для превью
		if(!file_exists(JPATH_ROOT . DIRECTORY_SEPARATOR . $pathThumb))
		{
			//создаем каталог
			Folder::create(JPATH_ROOT . DIRECTORY_SEPARATOR . $pathThumb);
		}

		//подгружаем библиотеку для фото
		JLoader::registerNamespace('Gumlet', JPATH_SITE . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR , ['plugins', 'fields', 'radicalmultifield', 'libs', 'gumlet', 'lib']));

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

		} else
			{
			$originalFile = JPATH_ROOT . DIRECTORY_SEPARATOR . $source;
			$overlayAccept = false;
		}

		if(copy(JPATH_ROOT . DIRECTORY_SEPARATOR . $source, $fullPathThumb)) {
			$image = new ImageResize($fullPathThumb);

			$maxWidth = (int)$params['filesimportpreviewmaxwidth'];
			$maxHeight = (int)$params['filesimportpreviewmaxheight'];

			$image->resizeToBestFit($maxWidth, $maxHeight);

			if((int)$params['filesimportrezizeoverlay'] && (int)$params['filesimportreoriginal'] && $overlayAccept) {
				$file = JPATH_SITE . DIRECTORY_SEPARATOR . $params['filesimportrezizeoverlayfile'];
				$position = $params['filesimportrezizeoverlaypos'];
				$padding = $params['filesimportrezizeoverlaypadding'];

				if(file_exists($file))
				{
					$image->addFilter(function ($imageDesc) use ($file, $position, $padding, $params)
					{

						if(!file_exists($file))
						{
							return false;
						}

						$logo = imagecreatefromstring(file_get_contents($file));
						$logoWidth = imagesx($logo);
						$logoHeight = imagesy($logo);
						$imageWidth = imagesx($imageDesc);
						$imageHeight = imagesy($imageDesc);
						$imageX = $padding;
						$imageY = $padding;

						if(isset($params['filesimportrezizeoverlaypercent']) && (int)$params['filesimportrezizeoverlaypercent'])
						{
							//сжимаем водяной знак по процентному соотношению от изображения на который накладывается
							$precent = (int)$params['filesimportrezizeoverlaypercentvalue'];
							$logoWidthMax = $imageWidth / 100 * $precent;
							$logoHeightMax = $imageHeight / 100 * $precent;

							$ratio  = $logoHeight / $logoWidth;
							$tmpWidth = $logoWidthMax;
							$tmpHeight = $tmpWidth * $ratio;

							if ($tmpHeight > $logoHeightMax)
							{
								$tmpHeight = $logoHeightMax;
								$tmpWidth = $tmpHeight / $ratio;
							}

							$logoNew = imagecreatetruecolor($tmpWidth, $tmpHeight);
							imagesavealpha($logoNew, true);
							imagefill($logoNew,0,0,0x7fff0000);
							imagecopyresampled($logoNew, $logo, 0, 0, 0, 0, $tmpWidth, $tmpHeight, $logoWidth, $logoHeight);
							$logo = $logoNew;
							$logoWidth = $tmpWidth;
							$logoHeight = $tmpHeight;
							unset($logoNew);
						}

						if($logoWidth > $imageWidth && $logoHeight > $imageHeight)
						{
							return false;
						}

						switch ($position)
						{

							case "topleft":
								$imageX = $padding;
								$imageY = $padding;
								break;

							case "topcenter":
								$imageX = ($imageWidth/2) - ($logoWidth/2);
								$imageY = $padding;
								break;

							case "topright":
								$imageX = $imageWidth - $padding - $logoWidth;
								$imageY = $padding;
								break;

							case "centerleft":
								$imageX = $padding;
								$imageY = ($imageHeight/2) - ($logoHeight/2);
								break;

							case "centercenter":
								$imageX = ($imageWidth/2) - ($logoWidth/2);
								$imageY = ($imageHeight/2) - ($logoHeight/2);
								break;

							case "centerright":
								$imageX = $imageWidth - $padding - $logoWidth;
								$imageY = ($imageHeight/2) - ($logoHeight/2);
								break;

							case "bottomleft":
								$imageX = $padding;
								$imageY = $imageHeight - $padding - $logoHeight;
								break;

							case "bottomcenter":
								$imageX = ($imageWidth/2) - ($logoWidth/2);
								$imageY = $imageHeight - $padding - $logoHeight;
								break;

							case "bottomright":
								$imageX = $imageWidth - $padding - $logoWidth;
								$imageY = $imageHeight - $padding - $logoHeight;
								break;

						}

						imagecopy($imageDesc, $logo, $imageX, $imageY, 0, 0, $logoWidth, $logoHeight);
					});
				}
			}


			$image->save($fullPathThumb);
			unset($image);
		}

		return $pathFileThumb;

	}



}