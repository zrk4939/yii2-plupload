<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 14.04.2017
 * Time: 13:35
 */

namespace zrk4939\widgets\plupload\actions;

use zrk4939\widgets\plupload\components\ChunkUploader;
use zrk4939\helpers\ImageOptimization;
use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;
use yii\web\Response;

/**
 * Class PluploadAction
 */
class PluploadAction extends Action
{
    /**
     * @var callable success callback with signature: `function($filename, $params)`
     */
    public $validate;

    public $rename = true;
    public $extensions = ['jpg', 'png'];
    public $tempPath = '/uploads/temp';

    protected $response = [
        'status' => 'ko',
        'message' => 'Unexpected error.',
    ];

    /**
     * @var string file input name.
     */
    public $inputName = 'file';
    /**
     * @var integer the permission to be set for newly created cache files.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * If not set, the permission will be determined by the current environment.
     */
    public $fileMode;
    /**
     * @var integer the permission to be set for newly created directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    public $dirMode = 0775;
    /**
     * @var callable success callback with signature: `function($filename, $params)`
     */
    public $onComplete;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->tempPath = Yii::getAlias('@approot' . $this->tempPath);

        Yii::$app->response->format = Response::FORMAT_JSON;
        $this->tempPath = Yii::getAlias($this->tempPath);
        if (!is_dir($this->tempPath)) {
            FileHelper::createDirectory($this->tempPath, $this->dirMode, true);
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $uploadedFile = UploadedFile::getInstanceByName($this->inputName);
        $params = ArrayHelper::merge(Yii::$app->request->getBodyParams(), Yii::$app->request->get());
        $error = null;

        if ($this->validate) {
            $error = call_user_func($this->validate, $uploadedFile, $params, $this->response);
        } else {
            if (!ImageOptimization::validateExtensions($uploadedFile, $this->extensions)) {
                $error = Yii::t(
                    'yii',
                    'Only files with these extensions are allowed: {extensions}.',
                    ['extensions' => implode(', ', $this->extensions)]
                );
            }
        }

        if ($error) {
            $this->response['message'] = $error;
            return $this->response;
        }

        $params['name'] = ImageOptimization::generateFileName($uploadedFile->baseName, $this->rename);
        $params['name'] .= '.' . $uploadedFile->extension;

        $tempFile = $this->getTempFileName($params);
        $params['name'] = preg_replace("/.*?(\/|\\\)([_A-Za-z0-9-.]+)\.\w+$/msi", '$2', $tempFile);
        $params['name'] .= '.' . $uploadedFile->extension;

        $isUploadComplete = ChunkUploader::process($uploadedFile, $tempFile);

        ImageOptimization::rotateOrientation($tempFile);
        ImageOptimization::optimizeWidth($tempFile, 1280);
        ImageOptimization::optimizeHeight($tempFile, 1280);

        if ($isUploadComplete) {
            if ($this->onComplete) {
                return call_user_func($this->onComplete, $tempFile, $params);
            } else {
                return [
                    'filename' => $tempFile,
                    'params' => $params,
                ];
            }
        }

        return $this->response;
    }

    /**
     * Returns an unused file path by adding a filename suffix if necessary.
     * @param string $path
     * @return string
     */
    protected function getUnusedPath($path)
    {
        $newPath = $path;
        $info = pathinfo($path);
        $suffix = 1;
        while (file_exists($newPath)) {
            $newPath = $info['dirname'] . DIRECTORY_SEPARATOR . "{$info['filename']}_{$suffix}";
            if (isset($info['extension'])) {
                $newPath .= ".{$info['extension']}";
            }
            $suffix++;
        }
        return $newPath;
    }

    /**
     * @param $params
     * @return string
     */
    private function getTempFileName($params)
    {
        $tempFile = $newFile = $this->getUnusedPath($this->tempPath . DIRECTORY_SEPARATOR . $params['name']);
        if (!empty($params['url']) && $params['url'] != '0') {
            $uploadPath = Yii::getAlias('@approot' . $params['url']);

            if (is_dir($uploadPath)) {
                $newFile = $this->getUnusedPath($uploadPath . DIRECTORY_SEPARATOR . $params['name']);

                $newInfo = pathinfo($newFile);
                $tempFile = $this->getUnusedPath($this->tempPath . DIRECTORY_SEPARATOR . $newInfo['basename']);
            }
        }

        return $tempFile;
    }
}
