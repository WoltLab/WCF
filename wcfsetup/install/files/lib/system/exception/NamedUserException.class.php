<?php

namespace wcf\system\exception;

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
     * @deprecated 6.3
     */
    public function show()
    {
    }
}
