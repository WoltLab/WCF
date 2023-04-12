<?php

namespace wcf\http\error;

use Psr\Http\Message\StreamInterface;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Renders an nice HTML error page.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class HtmlErrorRenderer
{
    public function render(
        string $title,
        string $message,
        ?\Throwable $exception = null,
        bool $showLogin = false
    ): StreamInterface {
        return $this->renderHtmlMessage(
            $title,
            StringUtil::encodeHTML($message),
            $exception,
            $showLogin
        );
    }

    public function renderHtmlMessage(
        string $title,
        string $message,
        ?\Throwable $exception = null,
        bool $showLogin = false
    ): StreamInterface {
        return HeaderUtil::parseOutputStream(WCF::getTPL()->fetchStream(
            'error',
            'wcf',
            [
                'title' => $title,
                'message' => $message,
                'exception' => $exception,
                'showLogin' => $showLogin,
                'templateName' => 'error',
                'templateNameApplication' => 'wcf',
            ]
        ));
    }
}
