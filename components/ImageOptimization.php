<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 28.08.2017
 * Time: 10:18
 */

namespace zrk4939\widgets\plupload\components;

use Yii;
use yii\base\Object;
use yii\bootstrap\Html;
use yii\helpers\Inflector;
use yii\web\UploadedFile;

/**
 * Class ImageOptimization
 * TODO Вынести в zrk4939-base repo
 */
class ImageOptimization extends Object
{
    public static function rotateOrientation($source_image_path)
    {
        list($source_image_width, $source_image_height, $source_gd_image, $source_image_type) = self::readImage($source_image_path);

        if ($source_image_type === IMAGETYPE_JPEG) {
            try {
                $exif = exif_read_data($source_image_path);
            } catch (\Exception $exp) {
                $exif = false;
            }

            if ($exif && !empty($exif['Orientation'])) {
                $reSave = false;
                switch ($exif['Orientation']) {
                    case 8:
                        $source_gd_image = imagerotate($source_gd_image, 90, 0);
                        $reSave = true;
                        break;
                    case 3:
                        $source_gd_image = imagerotate($source_gd_image, 180, 0);
                        $reSave = true;
                        break;
                    case 6:
                        $source_gd_image = imagerotate($source_gd_image, -90, 0);
                        $reSave = true;
                        break;
                }

                if ($reSave) {
                    $result = imagejpeg($source_gd_image, $source_image_path, 85);
                    if ($result) {
                        chmod($source_image_path, 0775);
                    }
                }
            }
        }
    }


    public static function optimizeWidth($src, $new_width)
    {
        if (!static::isImage($src)) {
            return;
        }

        // read image
        list($source_image_width, $source_image_height, $source_gd_image, $source_image_type) = self::readImage($src);
        if (empty($source_gd_image)) {
            return;
        }

        /* ----- */

        $saveAs_func = "imagejpeg"; // дефолт
        switch ($source_image_type) {
            case IMAGETYPE_JPEG:
                $saveAs_func = "imagejpeg";
                break;
            case IMAGETYPE_GIF:
                $saveAs_func = "imagegif";
                break;
            case IMAGETYPE_PNG:
                $saveAs_func = "imagepng";
                break;
        }

        if ($source_image_width > $new_width) {
            $koe = $source_image_width / $new_width; // вычисляем коэффициент $width это ширина которая должна быть
            $new_height = ceil($source_image_height / $koe); // с помощью коэффициента вычисляем высоту
            $newImage = imagecreatetruecolor($new_width, $new_height); // создаем картинку с нужными параметрами
            imagecopyresampled($newImage, $source_gd_image, 0, 0, 0, 0, $new_width, $new_height, $source_image_width, $source_image_height);

            if ($source_image_type == IMAGETYPE_JPEG) {
                imageinterlace($newImage, 1); // чересстрочное формирование изображение
            } else {
                imageconvolution($newImage, array( // улучшаем четкость
                    array(-1, -1, -1),
                    array(-1, 16, -1),
                    array(-1, -1, -1)),
                    8, 0);
            }

            $saveAs_func($newImage, $src, 85); // пересохраняем

            imagedestroy($source_gd_image);
            imagedestroy($newImage);
        }
    }

    public static function optimizeHeight($src, $new_height)
    {
        if (!static::isImage($src)) {
            return;
        }

        // read image
        list($source_image_width, $source_image_height, $source_gd_image, $source_image_type) = self::readImage($src);
        if (empty($source_gd_image)) {
            return;
        }

        /* ----- */

        $saveAs_func = "imagejpeg"; // дефолт
        switch ($source_image_type) {
            case IMAGETYPE_JPEG:
                $saveAs_func = "imagejpeg";
                break;
            case IMAGETYPE_GIF:
                $saveAs_func = "imagegif";
                break;
            case IMAGETYPE_PNG:
                $saveAs_func = "imagepng";
                break;
        }

        if ($source_image_height > $new_height) {
            $koe = $source_image_height / $new_height; // вычисляем коэффициент $width это ширина которая должна быть
            $new_width = ceil($source_image_width / $koe); // с помощью коэффициента вычисляем высоту
            $newImage = imagecreatetruecolor($new_width, $new_height); // создаем картинку с нужными параметрами
            imagecopyresampled($newImage, $source_gd_image, 0, 0, 0, 0, $new_width, $new_height, $source_image_width, $source_image_height);

            if ($source_image_type == IMAGETYPE_JPEG) {
                imageinterlace($newImage, 1); // чересстрочное формирование изображение
            } else {
                imageconvolution($newImage, array( // улучшаем четкость
                    array(-1, -1, -1),
                    array(-1, 16, -1),
                    array(-1, -1, -1)),
                    8, 0);
            }

            $saveAs_func($newImage, $src, 85); // пересохраняем

            imagedestroy($source_gd_image);
            imagedestroy($newImage);
        }
    }

    /**
     * @param string $string
     * @param bool $rename
     * @return string
     */
    public static function generateFileName($string, $rename = true)
    {
        $string = Html::encode($string);
        $string = strtolower($string);

        if ($rename) {
            $string = sha1($string . time());
        }

        return Inflector::slug($string);
    }

    /**
     * @param array $sortArray
     * @param array $resultArray
     * @return array
     */
    public static function filesSort($sortArray, $resultArray)
    {
        $resultArray = array_merge(array_flip($sortArray), array_flip($resultArray));
        $resultArray = array_flip($resultArray);

        return $resultArray;
    }

    /**
     * @param UploadedFile $file
     * @param $validExtensions $ext
     *
     * @return boolean
     */
    public static function validateExtensions($file, $validExtensions = ['jpg', 'png'])
    {
        return in_array($file->extension, $validExtensions);
    }

    /**
     * @param $file string
     * @return boolean
     */
    public static function isImage($file)
    {
        $result = false;
        try {
            if (function_exists('exif_imagetype')) {
                $result = exif_imagetype($file);
            } else {
                $result = getimagesize($file);
            }
        } catch (\Exception $e) {
        }

        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * @param string $source_image_path
     * @return array
     */
    protected static function readImage($source_image_path)
    {
        list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
        $source_gd_image = null;

        switch ($source_image_type) {
            case IMAGETYPE_JPEG:
                $source_gd_image = imagecreatefromjpeg($source_image_path);
                break;
            case IMAGETYPE_GIF:
                $source_gd_image = imagecreatefromgif($source_image_path);
                break;
            case IMAGETYPE_PNG:
                $source_gd_image = imagecreatefrompng($source_image_path);
                break;
        }

        return [$source_image_width, $source_image_height, $source_gd_image, $source_image_type];
    }

    public static function checkFile($filePath, $fileName, $extension)
    {
        $webDir = Yii::getAlias('@webroot');

        if (file_exists($webDir . $filePath . $fileName . '.' . $extension)) {
            return "{$fileName}_" . date('Y-m-d-G-H');
        }

        return $fileName;
    }

    /**
     * @param $files
     * @param $rootPath
     */
    public static function deleteFiles($files, $rootPath)
    {
        $thumbs = ThumbnailHelper::getThumbs();

        foreach ($files as $filename) {
            if (file_exists($rootPath . $filename) && is_file($rootPath . $filename)) {
                unlink($rootPath . $filename);
            }

            foreach ($thumbs as $thumbName => $properties) {
                if (file_exists($rootPath . $thumbName . '_' . $filename)) {
                    unlink($rootPath . $thumbName . '_' . $filename);
                }
            }
        }
    }
}
