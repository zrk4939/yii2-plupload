<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 14.04.2017
 * Time: 13:56
 */

namespace zrk4939\widgets\plupload\assets;

use yii\web\AssetBundle;

class PluploadAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@zrk4939/widgets/plupload/assets/dist';

    /**
     * @inheritdoc
     */
    public $css = [
        'css/plupload.css'
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'js/plupload.js'
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'zrk4939\widgets\plupload\assets\PluploadLibAsset',
    ];
}