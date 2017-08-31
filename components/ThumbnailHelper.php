<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 27.02.2017
 * Time: 15:16
 */

namespace zrk4939\widgets\plupload\components;


use yii\base\InvalidParamException;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Class ThumbnailHelper
 * TODO Вынести в zrk4939-base repo
 */
class ThumbnailHelper extends Object
{
    public static function getThumbs()
    {
        return [
            'preview' => [
                'width' => 150,
                'height' => 150,
                'cropAndCenter' => true
            ],
            'small' => [
                'width' => 240,
                'height' => 269,
                'cropAndCenter' => true
            ],
            'extra_small' => [
                'width' => 80,
                'height' => 80,
                'cropAndCenter' => true
            ],
            'slider' => [
                'width' => 480,
                'height' => 538,
                'cropAndCenter' => true
            ],
        ];
    }

    /**
     * @param $imagesDir
     * @param $filename
     * @param string $prefix
     * @return string
     */
    public static function getImageThumbNail($imagesDir, $filename, $prefix = 'preview')
    {
        if (!key_exists($prefix, self::getThumbs())) {
            throw new InvalidParamException();
        }

        $thumbFileName = $prefix . '_' . $filename;
        $sizes = ArrayHelper::getValue(self::getThumbs(), $prefix);

        if (file_exists($imagesDir . $thumbFileName) || ThumbnailHelper::generateImageThumbnail($imagesDir . $filename, $imagesDir . $thumbFileName, $sizes['width'], $sizes['height'], $sizes['cropAndCenter'])){
            return $thumbFileName;
        }

        return $filename;
    }

    public static function createImagesThumbnails($imagesDir, $webDir, $valueArray, $thumbNames = [])
    {
        if (!empty($valueArray) && is_array($valueArray)) {
            foreach ($valueArray as $item) {
                foreach (self::getThumbs() as $prefix => $sizes) {
                    if (in_array($prefix, $thumbNames)) {
                        $thumbFileName = $prefix . '_' . $item;
                        if (!file_exists($imagesDir . $thumbFileName)) {
                            ThumbnailHelper::generateImageThumbnail($imagesDir . $item, $imagesDir . $thumbFileName, $sizes['width'], $sizes['height'], $sizes['cropAndCenter']);
                        }
                    }
                }
            }
        }
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
     * @param string $thumbnail_image_path
     * @param string $width
     * @param string $height
     * @param bool $cropAndCenter
     * @return bool
     */
    protected static function generateImageThumbnail($source_image_path, $thumbnail_image_path, $width, $height, $cropAndCenter = true)
    {
        if (!static::isImage($source_image_path)) {
            return false;
        }
        // read image
        list($source_image_width, $source_image_height, $source_gd_image, $source_image_type) = self::readImage($source_image_path);
        if (empty($source_gd_image)) {
            return false;
        }

        /* ----- */

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
                    $result = imagejpeg($source_gd_image, $source_image_path, 95);
                    if ($result) {
                        chmod($source_image_path, 0775);
                    }
                    list($source_image_width, $source_image_height, $source_gd_image) = self::readImage($source_image_path);
                }
            }
        }

        if ($cropAndCenter) {
            $ratio_orig = $source_image_width / $source_image_height;

            if ($width / $height > $ratio_orig) {
                $new_height = $width / $ratio_orig;
                $new_width = $width;
            } else {
                $new_width = $height * $ratio_orig;
                $new_height = $height;
            }

            $x_mid = $new_width / 2;  //horizontal middle
            $y_mid = $new_height / 2; //vertical middle

            $process = imagecreatetruecolor(round($new_width), round($new_height));
            imagealphablending($process, false);
            imagesavealpha($process, true);
            imagecopyresampled($process, $source_gd_image, 0, 0, 0, 0, $new_width, $new_height, $source_image_width, $source_image_height);
            $thumbnail_gd_image = imagecreatetruecolor($width, $height);
            imagealphablending($thumbnail_gd_image, false);
            imagesavealpha($thumbnail_gd_image, true);
            imagecopyresampled($thumbnail_gd_image, $process, 0, 0, ($x_mid - ($width / 2)), ($y_mid - ($height / 2)), $width, $height, $width, $height);
        } else {
            $source_aspect_ratio = $source_image_width / $source_image_height;
            $thumbnail_aspect_ratio = $width / $height;

            if ($source_image_width <= $width && $source_image_height <= $height) {
                $thumbnail_image_width = $source_image_width;
                $thumbnail_image_height = $source_image_height;
            } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
                $thumbnail_image_width = (int)($height * $source_aspect_ratio);
                $thumbnail_image_height = $height;
            } else {
                $thumbnail_image_width = $width;
                $thumbnail_image_height = (int)($width / $source_aspect_ratio);
            }

            $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
            imagealphablending($thumbnail_gd_image, false);
            imagesavealpha($thumbnail_gd_image, true);
            imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
        }

        if ($source_image_type === IMAGETYPE_GIF) {
            $result = imagegif($thumbnail_gd_image, $thumbnail_image_path);
        } else if ($source_image_type === IMAGETYPE_PNG) {
            $result = imagepng($thumbnail_gd_image, $thumbnail_image_path, 1);
        } else {
            $result = imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 95);
        }

        if ($result) {
            chmod($thumbnail_image_path, 0775);
        }
        imagedestroy($source_gd_image);
        imagedestroy($thumbnail_gd_image);
        return true;
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
}
