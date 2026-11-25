<?php

namespace wcf\system\message\mention;

/**
 * Provides users that are relevant for mentions in the context of a specific
 * object, e.g. the participants of a discussion. These users are suggested
 * before all other users.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
interface IMentionSuggestionProvider
{
    /**
     * Returns the ids of the users that should be suggested for the object
     * with the given id, ordered by relevance. If `$query` is not empty, only
     * users whose username starts with `$query` are returned.
     *
     * Implementations must check that the active user can access the object
     * and must not return the id of the active user.
     *
     * @return list<int>
     */
    public function getUserIDs(int $objectID, string $query, int $limit): array;
}
