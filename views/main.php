<?php

use zrk4939\widgets\plupload\Plupload;
use yii\bootstrap\Html;

/* @var $this yii\web\View */

/* @var $attribute string */
/* @var $params array */
/* @var $uploadedFiles array */
/* @var $uploadDir string */
/* @var $baseUrl string */
/* @var $webDir string */
/* @var $thumbnailPrefix string */
/* @var $uploadName string */
/* @var $deleteName string */
/* @var $extensions array */

/* @var string $propertiesAction */
?>
<div class="uploader-files clearfix">
    <div class="preview form-group clearfix">
        <div class="<?= "to-upload-{$attribute}" ?>"></div>
        <div class="plupload-progress hidden"></div>
    </div>

    <div class="<?= "delete-{$attribute}-wrapper" ?> hidden" data-delete-name="<?= $deleteName ?>"></div>
    <div id="error-container"></div>
    <div class="form-group">
        <p>Разрешённые к загрузке типы файлов: <i><?= implode(', ', $extensions) ?></i></p>
        <p>Максимальный размер файла: <i><?= Plupload::getPHPMaxUploadSize() ?>Мб</i></p>
        <?= Plupload::widget($params); ?>
    </div>
</div>
