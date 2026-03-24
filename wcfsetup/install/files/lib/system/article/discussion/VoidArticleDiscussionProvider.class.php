<?php

namespace wcf\system\article\discussion;

use wcf\data\article\Article;

/**
 * Represents a non-existing discussion provider and is used when there is no other
 * type of discussion being available. This provider is always being evaluated last.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
class VoidArticleDiscussionProvider extends AbstractArticleDiscussionProvider
{
    #[\Override]
    public function getDiscussionCount()
    {
        return 0;
    }

    #[\Override]
    public function getDiscussionCountPhrase()
    {
        return '';
    }

    #[\Override]
    public function getDiscussionLink()
    {
        return '';
    }

    #[\Override]
    public function renderDiscussions()
    {
        return '';
    }

    #[\Override]
    public static function isResponsible(Article $article)
    {
        return true;
    }
}
