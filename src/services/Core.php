<?php
/**
 * Easy Form plugin for Craft CMS 3.x
 *
 * You can make a contact form easily. Just submit a form, and an email will be sent.
 *
 * @link      https://tinbyeans.net
 * @copyright Copyright (c) 2020 Roy Okuwaki
 */

namespace tinybeans\easyform\services;

use tinybeans\easyform\EasyForm;

use Craft;
use craft\base\Component;
use craft\mail\Message;
use craft\helpers\App;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\InvalidConfigException;


/**
 * Core Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Roy Okuwaki
 * @package   EasyForm
 * @since     1.0.0
 */
class Core extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     EasyForm::$plugin->core->contactForm()
     *
     * @return array
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function contactForm(): array
    {
        $app = Craft::$app;
        $request = $app->request;
        $requestUrl = $request->getReferrer();
        $parseUrl = parse_url($requestUrl);
        $params = $request->getBodyParams();
        $formHandle = $params['handle'];
        $redirectUrl = $params['redirect'];
        $toEmail = $params['toEmail'];

        // =========================================================================
        // Initialise Email configuration
        // =========================================================================
        $settings = EasyForm::$plugin->getSettings()->forms;
        if (empty($settings)) {
            return [
                'error' => [
                    'code' => 404,
                    'message' => 'Settings Not Found.',
                ],
                'redirect' => $parseUrl['path'] . '?noSettings=1'
            ];
        }
        $currentSettings = $settings[$formHandle];
        if (empty($currentSettings)) {
            return [
                'error' => [
                    'code' => 404,
                    'message' => 'Current Settings Not Found.',
                ],
                'redirect' => $parseUrl['path'] . '?noCurrentSettings=1'
            ];
        }

        if (empty($formHandle)) {
            return [
                'error' => [
                    'code' => 500,
                    'message' => 'The handle parameter is required.',
                ],
                'redirect' => $parseUrl['path'] . '?formHandleMissing=1'
            ];
        }

        if (empty($toEmail) && empty($currentSettings['allowEmptyEmail'])) {
            return [
                'error' => [
                    'code' => 500,
                    'message' => 'The toEmail parameter is required.',
                ],
                'redirect' => $parseUrl['path'] . '?toEmailMissing=1'
            ];
        }

        // =========================================================================
        // Block spam
        // =========================================================================

        // Honeypot
        $email = $request->getBodyParam('email');
        if (!empty($email)) {
            return [
                'error' => [
                    'code' => 400,
                    'message' => 'Bad Request.',
                ],
                'redirect' => $parseUrl['path'] . '?honeypot=1'
            ];
        }

        // User events
        $focusCount = $request->getBodyParam('focus');
        $keyupCount = $request->getBodyParam('keyup');
        if (empty($focusCount) || empty($keyupCount)) {
            return [
                'error' => [
                    'code' => 400,
                    'message' => 'Bad Request.',
                ],
                'redirect' => $parseUrl['path'] . '?bot=1'
            ];
        }

        // =========================================================================
        // Send an email
        // =========================================================================
        $systemSettings = $app->projectConfig->get('email');
        $fromEmail = $this->getEnvValue($systemSettings['fromEmail']);
        $fromName = $this->getEnvValue($systemSettings['fromName']);

        // To Customer
        if (!empty($toEmail) && empty($currentSettings['allowEmptyEmail'])) {
            if ($toCustomerSettings = $currentSettings['toCustomer']) {
                $messageToCustomer = new Message();
                // From
                $messageToCustomer->setFrom([$fromEmail => $fromName]);
                // To
                $messageToCustomer->setTo($toEmail);
                // Reply to
                if (empty($toCustomerSettings['replyTo'])) {
                    $messageToCustomer->setReplyTo($fromEmail);
                } else {
                    $messageToCustomer->setReplyTo($toCustomerSettings['replyTo']);
                }
                // Subject
                $messageToCustomer->setSubject($toCustomerSettings['subject']);
                // Email body
                $params['toAdmin'] = false;
                $params['toCustomer'] = true;
                $emailBody = $app->getView()->renderTemplate($toCustomerSettings['template'], $params);
                if ($toCustomerSettings['format'] === 'text') {
                    $messageToCustomer->setTextBody($emailBody);
                } else {
                    $messageToCustomer->setHtmlBody($emailBody);
                }
                if (!$app->mailer->send($messageToCustomer)) {
                    return [
                        'error' => [
                            'code' => 500,
                            'message' => 'Could not send an email to ' . $toEmail
                        ],
                        'redirect' => $parseUrl['path'] . '?emailFailed=1'
                    ];
                }
            } else {
                return [
                    'error' => [
                        'code' => 500,
                        'message' => 'The "toCustomer" setting is missing in your config file.'
                    ],
                    'redirect' => $parseUrl['path'] . '?toCustomerMissing=1'
                ];
            }
        }

        // To Admin
        if ($toAdminSettings = $currentSettings['toAdmin']) {
            $messageToAdmin = new Message();
            // From
            $messageToAdmin->setFrom([$fromEmail => $fromName]);
            // To
            if (empty($toAdminSettings['to'])) {
                $messageToAdmin->setTo($fromEmail);
            } else {
                $messageToAdmin->setTo($toAdminSettings['to']);
            }
            // Cc
            if (!empty($toAdminSettings['cc'])) {
                $messageToAdmin->setCc($toAdminSettings['cc']);
            }
            // Reply to
            if (!empty($toEmail)) {
                $messageToAdmin->setReplyTo($toEmail);
            }
            // Subject
            $messageToAdmin->setSubject($toAdminSettings['subject']);
            // Email body
            $params['toAdmin'] = true;
            $params['toCustomer'] = false;
            $emailBody = $app->getView()->renderTemplate($toAdminSettings['template'], $params);
            if ($toAdminSettings['format'] === 'text') {
                $messageToAdmin->setTextBody($emailBody);
            } else {
                $messageToAdmin->setHtmlBody($emailBody);
            }
            if (!$app->mailer->send($messageToAdmin)) {
                return [
                    'error' => [
                        'code' => 500,
                        'message' => 'Could not send an email to "' . $toEmail . '".'
                    ],
                    'redirect' => $parseUrl['path'] . '?emailFailed=1'
                ];
            }
        } else {
            return [
                'error' => [
                    'code' => 500,
                    'message' => 'The "toAdmin" setting is missing in your config file.'
                ],
                'redirect' => $parseUrl['path'] . '?toAdminMissing=1'
            ];
        }

        // =========================================================================
        // Response
        // =========================================================================
        return [
            'success' => true,
            'code' => 200,
            'redirect' => $redirectUrl
        ];
    }

    /**
     * Send an Email
     *
     * @param array $settings 'to', 'replyTo', 'subject', 'template', 'format'
     * @param array $params
     * @return array
     */
    public function sendEmail($settings = [], $params = [])
    {
        $app = Craft::$app;
        $request = $app->request;
        $systemSettings = $app->projectConfig->get('email');
        $fromEmail = $this->getEnvValue($systemSettings['fromEmail']);
        $fromName = $this->getEnvValue($systemSettings['fromName']);

        $message = new Message();
        // From
        $message->setFrom([$fromEmail => $fromName]);
        // To
        $message->setTo($settings['to']);
        // Cc
        if (isset($settings['cc'])) {
            $message->setCc($settings['cc']);
        }
        // Reply to
        if (empty($settings['replyTo'])) {
            $message->setReplyTo($fromEmail);
        } else {
            $message->setReplyTo($settings['replyTo']);
        }
        // Subject
        $message->setSubject($settings['subject']);
        // Email body
        $emailBody = $app->getView()->renderTemplate($settings['template'], $params);
        if ($settings['format'] === 'text') {
            $message->setTextBody($emailBody);
        } else {
            $message->setHtmlBody($emailBody);
        }
        return $app->mailer->send($message);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getEnvValue($name)
    {
        if (preg_match('/^\$/', $name)) {
            $name = preg_replace('/^\$/u', '', $name);
            return App::env($name);
        }
        return $name;
    }

    /**
     * Print custom logs
     *
     * @param string $customLog
     */
    public function customLog($customLog = '')
    {
        $date =
            "---------------------------------------" . PHP_EOL .
            date('c') . PHP_EOL .
            '---------------------------------------' . PHP_EOL;
        file_put_contents(CRAFT_BASE_PATH . '/storage/logs/easyform.log', $date . $customLog . "\n", FILE_APPEND);
    }

}
