<?php

namespace wcf\data\article;

use wcf\data\DatabaseObjectCollection;
use wcf\data\TCollectionReactions;
use wcf\data\TCollectionUserProfiles;

/**
 * Represents a collection of articles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<Article>
 */
class ArticleCollection extends DatabaseObjectCollection
{
    use TCollectionReactions;
    use TCollectionUserProfiles;

    #[\Override]
    protected function getReactionObjectType(): string
    {
        return 'com.woltlab.wcf.likeableArticle';
    }
}
