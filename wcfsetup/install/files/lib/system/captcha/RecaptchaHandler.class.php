<?php

namespace wcf\system\captcha;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use wcf\system\exception\UserInputException;
use wcf\system\io\HttpFactory;
use wcf\system\WCF;
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

        return WCF::getTPL()->render('wcf', 'shared_recaptcha', [
            'recaptchaLegacyMode' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function isAvailable()
    {
        if (RECAPTCHA_PUBLICKEY_V3 && RECAPTCHA_PRIVATEKEY_V3) {
            return true;
        }

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

        $type = $this->challenge ?: 'v3';

        $key = match ($type) {
            'v3' => RECAPTCHA_PRIVATEKEY_V3,
            'v2' => RECAPTCHA_PRIVATEKEY,
            'invisible' => RECAPTCHA_PRIVATEKEY_INVISIBLE,
            default => throw new UserInputException('recaptchaString', 'false'),
        };

        $request = new Request(
            'GET',
            \sprintf(
                'https://www.recaptcha.net/recaptcha/api/siteverify?%s',
                \http_build_query([
                    'secret' => $key,
                    'response' => $this->response,
                    'remoteip' => UserUtil::getIpAddress(),
                ], '', '&')
            )
        );

        try {
            $response = $this->getHttpClient()->send($request);
            $data = \json_decode((string)$response->getBody(), true, flags: \JSON_THROW_ON_ERROR);

            if ($data['success']) {
                // reCaptcha v3 score ranges from 1.0(very likely a good interaction) and 0.0(very likely a bot),
                // with 0.5 as the threshold for passing
                if ($type === 'v3' && $data['score'] < 0.5) {
                    throw new UserInputException('recaptchaString', 'false');
                }
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
