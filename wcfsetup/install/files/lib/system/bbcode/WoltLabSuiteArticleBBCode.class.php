<?php

namespace wcf\system\bbcode;

use wcf\data\article\ViewableArticle;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Parses the [wsa] bbcode tag.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Bbcode
 * @since       5.2
 */
class WoltLabSuiteArticleBBCode extends AbstractBBCode
{
    /**
     * @inheritDoc
     */
    public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser)
    {
        $objectID = 0;
        if (isset($openingTag['attributes'][0])) {
            $objectID = \intval($openingTag['attributes'][0]);
        }
        if (!$objectID) {
            return '';
        }

        /** @var ViewableArticle $object */
        $object = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.article', $objectID);
        if ($object !== null && $object->canRead() && $parser->getOutputType() == 'text/html') {
            return WCF::getTPL()->fetch('articleBBCode', 'wcf', [
                'article' => $object,
                'articleID' => $object->articleID,
                'titleHash' => \substr(StringUtil::getRandomID(), 0, 8),
            ], true);
        }

        return StringUtil::getAnchorTag(LinkHandler::getInstance()->getLink('Article', [
            'id' => $objectID,
        ]));
    }
}
