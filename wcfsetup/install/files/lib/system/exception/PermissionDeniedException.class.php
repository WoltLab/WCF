<?php

namespace wcf\system\exception;

use wcf\system\WCF;

/**
 * A PermissionDeniedException is thrown when a user has no permission to access
 * to a specific area.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PermissionDeniedException extends UserException
{
    /**
     * Creates a new PermissionDeniedException object.
     *
     * @param string|null $message custom error message
     */
    public function __construct($message = null)
    {
        if ($message === null) {
            $message = WCF::getLanguage()->getDynamicVariable('wcf.page.error.permissionDenied');
        }
        parent::__construct($message);
    }

    /**
     * @deprecated 6.3
     */
    public function show()
    {
    }
}
