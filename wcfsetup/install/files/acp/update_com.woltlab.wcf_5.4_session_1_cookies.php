<?php

/**
 * Sets the new session cookies.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\application\ApplicationHandler;
use wcf\system\form\container\GroupFormElementContainer;
use wcf\system\form\element\LabelFormElement;
use wcf\system\form\FormDocument;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;
use wcf\util\CryptoUtil;
use wcf\util\HeaderUtil;

// 1) Check whether the cookies are already in place.
$hasValidSessionCookie = false;
if (!empty($_COOKIE[COOKIE_PREFIX . "user_session"])) {
    $cookieValue = CryptoUtil::getValueFromSignedString($_COOKIE[COOKIE_PREFIX . "user_session"]);
    if ($cookieValue && \mb_strlen($cookieValue, '8bit') === 22) {
        $sessionID = \bin2hex(\mb_substr($cookieValue, 1, 20, '8bit'));
        if ($sessionID === WCF::getSession()->sessionID) {
            $hasValidSessionCookie = true;
        }
    }
}

$hasValidXsrfToken = false;
if (!empty($_COOKIE['XSRF-TOKEN'])) {
    if (
        // Check that the XSRF-TOKEN cookie is correctly signed.
        CryptoUtil::validateSignedString($_COOKIE['XSRF-TOKEN'])
        // Check that the current session value matches the cookie value.
        && WCF::getSession()->checkSecurityToken($_COOKIE['XSRF-TOKEN'])
        // Check that the 't' used for this request matches the cookie value.
        && (
            !empty($_REQUEST['t'])
            && \hash_equals($_COOKIE['XSRF-TOKEN'], $_REQUEST['t'])
        )
    ) {
        $hasValidXsrfToken = true;
    }
}

if ($hasValidSessionCookie && $hasValidXsrfToken) {
    // The process may continue;
    return;
}

// 2) Set new session cookie.
HeaderUtil::setCookie(
    "user_session",
    CryptoUtil::createSignedString(
        \pack(
            'CA20C',
            1,
            \hex2bin(WCF::getSession()->sessionID),
            0
        )
    )
);

// 3) Set new XSRF-TOKEN cookie.
$sameSite = $cookieDomain = '';
if (ApplicationHandler::getInstance()->isMultiDomainSetup()) {
    // We need to specify the cookieDomain in a multi domain set-up, because
    // otherwise no cookies are sent to subdomains.
    $cookieDomain = HeaderUtil::getCookieDomain();
    $cookieDomain = ($cookieDomain !== null ? '; domain=' . $cookieDomain : '');
} else {
    // SameSite=strict is not supported in a multi domain set-up, because
    // it breaks cross-application requests.
    $sameSite = '; SameSite=strict';
}

do {
    $bytes = \bin2hex(\random_bytes(16));
} while (\strpos(\base64_encode($bytes), '+') !== false);
$xsrfToken = CryptoUtil::createSignedString($bytes);
WCF::getSession()->register('__SECURITY_TOKEN', $xsrfToken);
\header(
    'set-cookie: XSRF-TOKEN=' . \rawurlencode($xsrfToken) . '; path=/' . $cookieDomain . (RouteHandler::secureConnection() ? '; secure' : '') . $sameSite,
    false
);

// 4) Adjust the SECURITY_TOKEN.
$container = new GroupFormElementContainer();
$container->setLabel('Set Cookies'); // TODO
$container->setDescription('Sets cookies');

$label = new LabelFormElement($container);
$label->setLabel('Set Cookies');
$label->setDescription(
    <<<EOT
<script>(function() {
var oldToken = SECURITY_TOKEN;
SECURITY_TOKEN = encodeURIComponent("{$xsrfToken}");
var oldExecute = WCF.ACP.Package.Installation.prototype._executeStep;
WCF.ACP.Package.Installation.prototype._executeStep = function (step, node, additionalData) {
	var request = this._proxy._ajaxRequest;
	request.setOption('url', request.getOption('url').replace(oldToken, SECURITY_TOKEN));

	return oldExecute.call(this, step, node, additionalData);
}
})();
</script>
EOT
);

$container->appendChild($label);

$document = new FormDocument("cookies_set");
$document->appendContainer($container);

return $document;
