<?php
/**
 * Easy Form plugin for Craft CMS 3.x
 *
 * You can make a contact form easily. Just submit a form, and an email will be sent.
 *
 * @link      https://tinbyeans.net
 * @copyright Copyright (c) 2020 Roy Okuwaki
 */

namespace tinybeans\easyform\models;

use tinybeans\easyform\EasyForm;

use Craft;
use craft\base\Model;

/**
 * EasyForm Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Roy Okuwaki
 * @package   EasyForm
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================
    
    public array $forms = [];
    public bool $allowEmptyEmail = false;

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            ['forms', 'array'],
            ['forms', 'default', 'value' => []],
        ];
    }
}
