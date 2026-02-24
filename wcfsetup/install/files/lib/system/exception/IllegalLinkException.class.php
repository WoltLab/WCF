<?php

namespace wcf\system\exception;

use wcf\system\WCF;

/**
 * IllegalLinkException shows the unknown link error page.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class IllegalLinkException extends NamedUserException
{
    /**
     * Creates a new IllegalLinkException object.
     */
    public function __construct()
    {
        parent::__construct(WCF::getLanguage()->getDynamicVariable('wcf.page.error.illegalLink'));
    }

    /**
     * @deprecated 6.3
     */
    public function show()
    {
    }
}
