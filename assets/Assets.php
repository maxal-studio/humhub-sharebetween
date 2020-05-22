<?php

namespace humhub\modules\sharebetween\assets;

use yii\web\AssetBundle;

class Assets extends AssetBundle
{

    public $publishOptions = [
        'forceCopy' => false
    ];

    public $css = [
        'css/sharebetween.css',
    ];

    public $js = [
        'js/sharebetween.js',
    ];

    public $jsOptions = [
        'position' => \yii\web\View::POS_END
    ];

    /**
     * @inheritdoc
     */
    public $sourcePath = '@sharebetween/resources';
}
