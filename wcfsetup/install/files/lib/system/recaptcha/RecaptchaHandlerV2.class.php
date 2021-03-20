<?php

namespace wcf\system\recaptcha;

use wcf\system\exception\UserInputException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\HTTPRequest;
use wcf\util\JSON;
use wcf\util\UserUtil;

/**
 * Handles reCAPTCHA V2 support.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Recaptcha
 * @deprecated  5.4 - This was an implementation detail of wcf\system\captcha\RecaptchaHandler.
 */
class RecaptchaHandlerV2 extends SingletonFactory
{
    /**
     * Validates response.
     *
     * @param string $response
     * @param string $type
     * @throws  UserInputException
     */
    public function validate($response, $type = 'v2')
    {
        // fail if response is empty to avoid sending api requests
        if (empty($response)) {
            throw new UserInputException('recaptchaString', 'false');
        }

        if ($type === 'v2') {
            $key = RECAPTCHA_PRIVATEKEY;
        } elseif ($type === 'invisible') {
            $key = RECAPTCHA_PRIVATEKEY_INVISIBLE;
        } else {
            // The bot modified the `recaptcha-type` form field.
            throw new UserInputException('recaptchaString', 'false');
        }

        $request = new HTTPRequest(
            \sprintf(
                'https://www.google.com/recaptcha/api/siteverify?secret=%s&response=%s&remoteip=%s',
                \rawurlencode($key),
                \rawurlencode($response),
                \rawurlencode(UserUtil::getIpAddress())
            ),
            ['timeout' => 10]
        );

        try {
            $request->execute();
            $reply = $request->getReply();
            $data = JSON::decode($reply['body']);

            if ($data['success']) {
                // yeah
            } else {
                throw new UserInputException('recaptchaString', 'false');
            }
        } catch (\Exception $e) {
            if ($e instanceof UserInputException) {
                throw $e;
            }

            // log error, but accept captcha
            \wcf\functions\exception\logThrowable($e);
        }

        WCF::getSession()->register('recaptchaDone', true);
    }
}
