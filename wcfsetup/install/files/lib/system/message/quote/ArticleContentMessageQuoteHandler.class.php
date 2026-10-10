<?php

namespace wcf\system\message\quote;

use wcf\data\article\content\ArticleContent;
use wcf\data\IMessage;

/**
 * IMessageQuoteHandler implementation for article contents.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ArticleContentMessageQuoteHandler extends AbstractMessageQuoteHandler
{
    #[\Override]
    public function getMessage(int $objectID): ?IMessage
    {
        $articleContent = new ArticleContent($objectID);
        if ($articleContent->isNil()) {
            return null;
        }

        if (!$articleContent->isVisible()) {
            return null;
        }

        return $articleContent;
    }
}
