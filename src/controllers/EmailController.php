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
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\Response;

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
    protected array|bool|int $allowAnonymous = ['index', 'test'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/easy-form/email
     *
     * @return mixed
     * @throws Exception
     * @throws InvalidConfigException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function actionIndex(): Response
    {
        $response = EasyForm::$plugin->core->contactForm();
        if (isset($response['success'])) {
            return $this->redirect(Craft::$app->getSecurity()->validateData($response['redirect']));
        }
        return $this->redirect($response['redirect']);
    }

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/easy-form/email/test
     *
     * @return mixed
     */
    public function actionTest()
    {
        $settings = EasyForm::$plugin->getSettings();
        $currentUser = Craft::$app->getUser()->getIdentity();
        $toEmail = $currentUser->email;
        $result = EasyForm::$plugin->core->sendEmail([
            'to' => $toEmail,
            'replyTo' => $toEmail,
            'subject' => 'Easy Form Test',
            'template' => '_emails/easy-form-test.twig',
            'format' => 'text',
        ], [
            'toEmail' => $toEmail,
            'settings' => $settings,
        ]);

        return json_encode($result);
    }
}
