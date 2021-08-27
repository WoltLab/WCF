<?php

namespace wcf\system\search\exception;

/**
 * Thrown when a search fails, e.g. because the index is unavailable due to a
 * network partition.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Search\Exception
 * @since   5.5
 */
final class SearchFailed extends \RuntimeException
{
    public function __construct($message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
