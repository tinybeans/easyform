<?php
/**
 * Easy Form plugin for Craft CMS 3.x
 *
 * You can make a contact form easily. Just submit a form, and an email will be sent.
 *
 * @link      https://tinbyeans.net
 * @copyright Copyright (c) 2020 Roy Okuwaki
 */

namespace tinybeans\easyform\assetbundles\easyform;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * EasyFormAsset AssetBundle
 *
 * AssetBundle represents a collection of asset files, such as CSS, JS, images.
 *
 * Each asset bundle has a unique name that globally identifies it among all asset bundles used in an application.
 * The name is the [fully qualified class name](http://php.net/manual/en/language.namespaces.rules.php)
 * of the class representing it.
 *
 * An asset bundle can depend on other asset bundles. When registering an asset bundle
 * with a view, all its dependent asset bundles will be automatically registered.
 *
 * http://www.yiiframework.com/doc-2.0/guide-structure-assets.html
 *
 * @author    Roy Okuwaki
 * @package   EasyForm
 * @since     1.0.0
 */
class EasyFormAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * Initializes the bundle.
     */
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = "@tinybeans/easyform/assetbundles/easyform/dist";

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/EasyForm.js',
        ];

        $this->css = [
            'css/EasyForm.css',
        ];

        parent::init();
    }
}
