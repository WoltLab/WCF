<?php

namespace wcf\system\cache\builder;

use wcf\data\reaction\type\ReactionTypeList;

/**
 * Caches the reaction type data.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class ReactionTypeCacheBuilder extends AbstractCacheBuilder
{
    #[\Override]
    public function rebuild(array $parameters)
    {
        $reactionTypeList = new ReactionTypeList();
        $reactionTypeList->sqlOrderBy = 'showOrder ASC';
        $reactionTypeList->readObjects();

        return $reactionTypeList->getObjects();
    }
}
