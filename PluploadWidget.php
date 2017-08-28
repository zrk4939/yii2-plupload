<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 14.04.2017
 * Time: 15:10
 */

namespace zrk4939\widgets\plupload;

use yii\bootstrap\Html;
use yii\bootstrap\InputWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class PluploadWidget extends InputWidget
{
    public $deleteAttribute;

    public $params = [];
    public $extensions = [
        'jpg', 'png', 'txt', 'pdf', 'doc', 'xls'
    ];

    private $defaultParams = [
        'browseLabel' => 'Выбрать файлы...',
        'browseOptions' => ['id' => 'browse', 'class' => 'btn btn-success browse'],
        'events' => [
            'FilesAdded' => 'plupFilesAdded',
            'FileUploaded' => 'plupFileUploaded',
            'Error' => 'plupError'
        ],
    ];

    public $uploadUrl;

    private $_uploadName;
    private $_deleteName;


    public function init()
    {
        parent::init();

        $this->_uploadName = Html::getInputName($this->model, $this->attribute);
        $this->_deleteName = Html::getInputName($this->model, $this->deleteAttribute);

        $this->params = ArrayHelper::merge($this->defaultParams, $this->params);

        if (empty($this->params['url'])) {
            $this->params['url'] = 'upload';
        }

        $this->params['url'] = Url::to([$this->params['url'], 'attr' => $this->attribute, 'url' => $this->uploadUrl]);
        $this->params['browseOptions']['id'] = $this->params['browseOptions']['id'] . '_' . $this->name;
    }

    public function run()
    {
        return $this->render('main', [
            'attribute' => $this->attribute,
            'params' => $this->params,
            'deleteName' => $this->_deleteName . '[]',
            'extensions' => $this->extensions,
        ]);
    }
}