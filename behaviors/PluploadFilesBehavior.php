<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 14.04.2017
 * Time: 14:31
 */

namespace zrk4939\widgets\plupload\behaviors;


use zrk4939\widgets\plupload\components\ImageOptimization;
use Yii;
use yii\base\Behavior;
use yii\base\InvalidValueException;
use yii\db\ActiveRecord;

class PluploadFilesBehavior extends Behavior
{
    public $models;
    public $filesArr;
    public $deleteArr;
    public $sortArr;

    public $moveFrom;
    public $moveTo;
    public $moveToPostfix;

    public $dirNameAttr = 'id';

    public function init()
    {
        parent::init();
        if (empty($this->models)) {
            throw new InvalidValueException('Public property ' . self::className() . '::models can not be empty.');
        }
        if (empty($this->filesArr)) {
            throw new InvalidValueException('Public property ' . self::className() . '::arrayAttr can not be empty.');
        }
    }

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'checkFiles',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'checkFiles',
            ActiveRecord::EVENT_AFTER_VALIDATE => 'sortFiles',
            ActiveRecord::EVENT_AFTER_INSERT => 'moveUploadedFiles',
            ActiveRecord::EVENT_AFTER_UPDATE => 'moveUploadedFiles',
        ];
    }

    public function checkFiles()
    {
        $models         = $this->owner->{$this->models} ?: [];
        $deleteFiles    = $this->owner->{$this->deleteArr} ?: [];
        $allFiles       = $this->owner->{$this->filesArr} ?: [];

        foreach ($models as $model){
            if(in_array($model->image, $deleteFiles)){
                $model->delete();
            }
            $index = array_search($model->image, $allFiles);
            if($index > -1){
                unset($allFiles[$index]);
            }
        }
        $this->owner->{$this->filesArr} = $allFiles;
    }

    public function sortFiles()
    {
        if (!empty($this->sortArr) && !empty($this->owner->{$this->sortArr})) {
            $models     = $this->owner->{$this->models} ?: [];
            $sortArray  = $this->owner->{$this->sortArr} ?: [];

            foreach ($models as $model){
                $index = array_search($model->image, $sortArray);
                if($index > -1){
                    $model->index = $index;
                    $model->update(true, ['index']);
                }
            }
        }
    }

    public function moveUploadedFiles()
    {
        $moveFrom = Yii::getAlias($this->moveFrom);
        $moveTo = Yii::getAlias($this->moveTo . $this->owner->{$this->dirNameAttr} . $this->moveToPostfix);

        if (!is_dir($moveFrom)) {
            mkdir($moveFrom, 0775, true);
        }

        if (!is_dir($moveTo)) {
            mkdir($moveTo, 0775, true);
        }

        $files = $this->owner->{$this->filesArr};
        foreach ($files as $key => $fileName) {
            $fileFrom = $moveFrom . DIRECTORY_SEPARATOR . $fileName;
            $fileTo = $moveTo . DIRECTORY_SEPARATOR . $fileName;

            if (file_exists($fileFrom)) {
                rename($fileFrom, $fileTo);
            }
        }

        $deleteFiles = $this->owner->{$this->deleteArr} ?: [];
        if (!empty($deleteFiles)) {
            ImageOptimization::deleteFiles($deleteFiles, $moveTo . DIRECTORY_SEPARATOR);
            ImageOptimization::deleteFiles($deleteFiles, $moveFrom . DIRECTORY_SEPARATOR);
        }
    }
}
