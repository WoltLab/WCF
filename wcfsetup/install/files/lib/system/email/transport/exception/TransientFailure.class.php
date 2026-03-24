<?php

namespace wcf\system\email\transport\exception;

/**
 * Denotes a transient failure during delivery. It may be retried later.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TransientFailure extends \Exception
{
}
