<?php

namespace wcf\system\exception;

use wcf\system\box\BoxHandler;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\HtmlString;

/**
 * NamedUserException shows a (well) styled page with the given error message.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class NamedUserException extends UserException
{
    public function __construct(
        protected readonly HtmlString|string $htmlString = "",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($htmlString, $code, $previous);
    }

    /**
     * @since 6.2
     */
    public function getHtmlString(): ?HtmlString
    {
        if ($this->htmlString instanceof HtmlString) {
            return $this->htmlString;
        }

        return null;
    }

    /**
     * Shows a styled page with the given error message.
     */
    #[\Override]
    public function show()
    {
        if (!\class_exists(WCFACP::class, false)) {
            BoxHandler::disablePageLayout();
        }
        SessionHandler::getInstance()->disableTracking();

        $name = static::class;
        $exceptionClassName = \mb_substr($name, \mb_strrpos($name, '\\') + 1);

        WCF::getTPL()->assign([
            'name' => $name,
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'message' => $this->getMessage(),
            'stacktrace' => $this->getTraceAsString(),
            'templateName' => 'userException',
            'templateNameApplication' => 'wcf',
            'exceptionClassName' => $exceptionClassName,
        ]);
        WCF::getTPL()->display('userException');
    }
}
