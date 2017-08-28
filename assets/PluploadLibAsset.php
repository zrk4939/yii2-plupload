<?php

namespace zrk4939\widgets\plupload\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for the Plupload script files.
 */
class PluploadLibAsset extends AssetBundle
{
    public $sourcePath = '@vendor/moxiecode/plupload/js';
    public $js = [
        'plupload.full.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}