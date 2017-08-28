# yii2-plupload

A [plupload](http://www.plupload.com/) extension for the Yii2 framework

## Installation

add

```
"zrk4939/yii2-plupload": "@dev"
```
to the require section of your `composer.json` file.

and

```
{
    "type": "vcs",
    "url": "https://github.com/zrk4939/yii2-plupload.git"
}
```
to the repositories array of your `composer.json` file.

## Usage

### Action

```php
public function actions() {
    return [
        'upload' => [
            'class' => PluploadAction::className(),
            'tempPath' => 'path/to/temp/dir',
            'extensions' => ['jpg', 'png'],
            'rename' => false,
            'onComplete' => function ($filename, $params) {
                // Do something with file
            }
        ],
    ];
}
```

### Widget

```php
<?php
 use zrk4939\widgets\plupload\PluploadWidget;
 
 
 echo PluploadWidget::widget([
    'model' => $model,
    'attribute' => 'files_arr',
    'deleteAttribute' => 'files_del',
    'uploadUrl' => '/files/',

    'params' => [
        'url' => ['upload'],
        'browseLabel' => 'Upload',
        'browseOptions' => ['id' => 'browse', 'class' => 'btn btn-success'],
        'options' => [
            'filters' => [
                'mime_types' => [
                    ['title' => 'Excel files', 'extensions' => 'csv,xls,xlsx'],
                ],
            ],
        ],
        'events' => [
            'FilesAdded' => 'function(uploader, files){
                                    $("#error-container").hide();
                                    $("#browse").button("loading");
                                    uploader.start();
             }',
            'FileUploaded' => 'function(uploader, file, response){
                                    $("#browse").button("reset");
             }',
            'Error' => 'function (uploader, error) {
                                    $("#error-container").html(error.message).show();
                                    $("#browse").button("reset");
             }'
        ],
    ],
 ]);
 ?>
```