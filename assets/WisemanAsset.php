<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Geolocation asset bundle.
 *
 * @author Sergio Casizzone <jambtc@gmail.com>
 * @since 2.0
 */
class WisemanAsset extends AssetBundle
{
    public $basePath = '@webroot/bundles/wiseman';
    public $baseUrl = '@web/bundles/wiseman';
    public $css = [
    ];
    public $js = [
        'script.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset'
    ];
}
