<?php

namespace wcf\system\package\license\exception;

/**
 * Rejected attempt to query the license list endpoint without supplying any
 * credentials which are stored using the primary package update server.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class MissingCredentials extends \Exception
{
    public function __construct()
    {
        parent::__construct('Cannot fetch the license data without any stored credentials.');
    }
}
