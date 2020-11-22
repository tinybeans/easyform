<?php
/**
 * Easy Form plugin for Craft CMS 3.x
 *
 * You can make a contact form easily. Just submit a form, and an email will be sent.
 *
 * @link      https://tinbyeans.net
 * @copyright Copyright (c) 2020 Roy Okuwaki
 */

namespace tinybeans\easyform\controllers;

use tinybeans\easyform\EasyForm;

use Craft;
use craft\web\Controller;

/**
 * Email Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Roy Okuwaki
 * @package   EasyForm
 * @since     1.0.0
 */
class EmailController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index', 'do-something'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/easy-form/email
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $response = EasyForm::$plugin->core->contactForm();
        if (isset($response['success'])) {
            return Craft::$app->getResponse()->redirect(Craft::$app->getSecurity()->validateData($response['redirect']))->send();
        }
        return Craft::$app->getResponse()->redirect($response['redirect'])->send();
    }

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/easy-form/email/do-something
     *
     * @return mixed
     */
    public function actionDoSomething()
    {
        $result = 'Welcome to the EmailController actionDoSomething() method';

        return $result;
    }
}
