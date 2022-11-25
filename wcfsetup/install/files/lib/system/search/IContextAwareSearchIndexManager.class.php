<?php

namespace wcf\system\search;

/**
 * Context aware search index managers are able to support
 * the parent id and container id of a message.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Search
 * @since 6.0
 */
interface IContextAwareSearchIndexManager extends ISearchIndexManager
{
    /**
     * Upserts a message into the search index.
     */
    public function setWithContext(
        string $objectType,
        int $objectID,
        int $parentID,
        int $containerID,
        string $message,
        string $subject,
        int $time,
        ?int $userID,
        string $username,
        ?int $languageID = null,
        string $metaData = ''
    ): void;
}
