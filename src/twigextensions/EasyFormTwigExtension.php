<?php
/**
 * Easy Form plugin for Craft CMS 3.x
 *
 * You can make a contact form easily. Just submit a form, and an email will be sent.
 *
 * @link      https://tinbyeans.net
 * @copyright Copyright (c) 2020 Roy Okuwaki
 */

namespace tinybeans\easyform\twigextensions;

use tinybeans\easyform\EasyForm;

use Craft;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Twig can be extended in many ways; you can add extra tags, filters, tests, operators,
 * global variables, and functions. You can even extend the parser itself with
 * node visitors.
 *
 * http://twig.sensiolabs.org/doc/advanced.html
 *
 * @author    Roy Okuwaki
 * @package   EasyForm
 * @since     1.0.0
 */
class EasyFormTwigExtension extends AbstractExtension
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'EasyForm';
    }

    /**
     * Returns an array of Twig filters, used in Twig templates via:
     *
     *      {{ 'something' | someFilter }}
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter('someFilter', [$this, 'someInternalFunction']),
        ];
    }

    /**
     * Returns an array of Twig functions, used in Twig templates via:
     *
     *      {% set this = someFunction('something') %}
     *
    * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('easyFormAttributes', [$this, 'easyFormAttributes'], ['is_safe' => ['html']]),
            new TwigFunction('easyFormHiddenInput', [$this, 'easyFormHiddenInput'], ['is_safe' => ['html']]),
            new TwigFunction('easyFormPreventBotScript', [$this, 'easyFormPreventBotScript'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Output a form attributes for Easy Form
     *
     * @return string
     */
    public function easyFormAttributes()
    {
        return 'method="post" accept-charset="UTF-8" enctype="multipart/form-data" data-easyform';
    }

    /**
     * Output hidden input tags for Easy Form
     *
     * @return string
     */
    public function easyFormHiddenInput()
    {
        return 
            '<input type="hidden" name="focus">' .
            '<input type="hidden" name="keyup">' .
            '<input type="email" name="email" class="d-none">';
    }

    /**
     * Our function called via Twig; it can do anything you want
     *
     * @return string
     */
    public function easyFormPreventBotScript()
    {
        $language = Craft::$app->getSites()->getCurrentSite()->language;
        $alertMessage = 'You might have typed only a few characters. Please type more, otherwise, you may be judged as a bot.';
        if (preg_match('/^ja/', $language)) {
            $alertMessage = '数文字しか入力していないようです。ボットと判定される可能性がありますので、さらに文字を入力してください。';
        }
        
        return <<<EOD
            <script>
            let focusCount = 0;
            let keyupCount = 0;
            const form = document.querySelector('[data-easyform]');
            form.addEventListener('focus', handleFocus, true);
            form.addEventListener('keyup', handleKeyup, true);
            form.addEventListener('submit', handleSubmit);
            function handleFocus(event) {
                focusCount++;
            }
            function handleKeyup(event) {
                keyupCount++;
            }
            function handleSubmit(event) {
                document.querySelector('[name="focus"]').value = focusCount;
                document.querySelector('[name="keyup"]').value = keyupCount;
                if (focusCount <= 1 || keyupCount < 5) {
                    alert('{$alertMessage}');
                    event.preventDefault();
                    return false;
                }
                return true;
            }
            </script>
EOD;
    }
}
