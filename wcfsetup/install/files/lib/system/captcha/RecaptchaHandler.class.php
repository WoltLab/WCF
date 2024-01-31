<?php

namespace wcf\system\captcha;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use wcf\system\exception\UserInputException;
use wcf\system\io\HttpFactory;
use wcf\system\WCF;
use wcf\util\JSON;
use wcf\util\UserUtil;

/**
 * Captcha handler for reCAPTCHA.
 *
 * @author  Tim Duesterhus, Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class RecaptchaHandler implements ICaptchaHandler
{
    /**
     * recaptcha challenge
     * @var string
     */
    public $challenge = '';

    /**
     * response to the challenge
     * @var string
     */
    public $response = '';

    /**
     * ACP option override
     * @var bool
     */
    public static $forceIsAvailable = false;

    /**
     * @inheritDoc
     */
    public function getFormElement()
    {
        if (WCF::getSession()->getVar('recaptchaDone')) {
            return '';
        }

        WCF::getTPL()->assign([
            'recaptchaLegacyMode' => true,
        ]);

        return WCF::getTPL()->fetch('shared_recaptcha');
    }

    /**
     * @inheritDoc
     */
    public function isAvailable()
    {
        if (!RECAPTCHA_PUBLICKEY || !RECAPTCHA_PRIVATEKEY) {
            // OEM keys are no longer supported, disable reCAPTCHA
            if (self::$forceIsAvailable) {
                // work-around for the ACP option selection
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        if (isset($_POST['recaptcha-type'])) {
            $this->challenge = $_POST['recaptcha-type'];
        } elseif (isset($_POST['parameters']['recaptcha-type'])) {
            $this->challenge = $_POST['parameters']['recaptcha-type'];
        }
        if (isset($_POST['g-recaptcha-response'])) {
            $this->response = $_POST['g-recaptcha-response'];
        } elseif (isset($_POST['parameters']['g-recaptcha-response'])) {
            $this->response = $_POST['parameters']['g-recaptcha-response'];
        }
    }

    /**
     * @inheritDoc
     */
    public function reset()
    {
        WCF::getSession()->unregister('recaptchaDone');
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        if (WCF::getSession()->getVar('recaptchaDone')) {
            return;
        }

        // fail if response is empty to avoid sending api requests
        if (empty($this->response)) {
            throw new UserInputException('recaptchaString', 'false');
        }

        $type = $this->challenge ?: 'v2';

        if ($type === 'v2') {
            $key = RECAPTCHA_PRIVATEKEY;
        } elseif ($type === 'invisible') {
            $key = RECAPTCHA_PRIVATEKEY_INVISIBLE;
        } else {
            // The bot modified the `recaptcha-type` form field.
            throw new UserInputException('recaptchaString', 'false');
        }

        $request = new Request(
            'GET',
            \sprintf(
                'https://www.google.com/recaptcha/api/siteverify?%s',
                \http_build_query([
                    'secret' => $key,
                    'response' => $this->response,
                    'remoteip' => UserUtil::getIpAddress(),
                ], '', '&')
            )
        );

        try {
            $response = $this->getHttpClient()->send($request);
            $data = JSON::decode((string)$response->getBody());

            if ($data['success']) {
                // yeah
            } else {
                throw new UserInputException('recaptchaString', 'false');
            }
        } catch (ClientExceptionInterface $e) {
            // log error, but accept captcha
            \wcf\functions\exception\logThrowable($e);
        }

        WCF::getSession()->register('recaptchaDone', true);
    }

    private function getHttpClient(): ClientInterface
    {
        return HttpFactory::makeClientWithTimeout(5);
    }
}
