<?php

namespace wcf\system\captcha;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
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

        return WCF::getTPL()->fetch('recaptcha');
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

        // The response is attacker-controlled, an array would be encoded as `response[0]`
        // and thus verify nothing at all. The length is bounded well above the size of a
        // legitimate token to avoid relaying arbitrary amounts of data to the API.
        if (!\is_string($this->response) || \strlen($this->response) > 8192) {
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

        // The parameters are sent in the request body, because the exception message of a
        // failed request contains the request URI, leaking the secret into the log file.
        $request = new Request(
            'POST',
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            \http_build_query([
                'secret' => $key,
                'response' => $this->response,
                'remoteip' => UserUtil::getIpAddress(),
            ], '', '&', \PHP_QUERY_RFC1738)
        );

        try {
            $response = $this->getHttpClient()->send($request);
            $data = JSON::decode((string)$response->getBody());

            if ($data['success']) {
                // yeah
            } else {
                throw new UserInputException('recaptchaString', 'false');
            }
        } catch (BadResponseException $e) {
            // An error response from the API is not a failure of our connectivity,
            // therefore the captcha must not be accepted.
            \wcf\functions\exception\logThrowable($e);

            throw new UserInputException('recaptchaString', 'false');
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
