<?php

namespace wcf\system\message\unfurl\exception;

/**
 * Denotes a permanent parsing body failed. It should not be retried later.
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Message\Unfurl
 * @since       5.4
 */
class ParsingFailed extends \Exception
{
}
