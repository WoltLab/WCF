<?php

namespace wcf\system\bbcode;

use wcf\data\article\ViewableArticle;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\view\ContentNotVisibleView;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Parses the [wsa] bbcode tag.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
final class WoltLabSuiteArticleBBCode extends AbstractBBCode
{
    /**
     * @inheritDoc
     */
    public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser): string
    {
        $articleID = 0;
        if (isset($openingTag['attributes'][0])) {
            $articleID = \intval($openingTag['attributes'][0]);
        }
        if (!$articleID) {
            return '';
        }

        $article = $this->getArticle($articleID);
        if ($article === null) {
            return new ContentNotVisibleView();
        }

        if (!$article->canRead()) {
            return new ContentNotVisibleView(
                WCF::getLanguage()->getDynamicVariable('wcf.message.content.no.permission.title')
            );
        } elseif ($parser->getOutputType() == 'text/html') {
            return WCF::getTPL()->fetch('shared_bbcode_wsa', 'wcf', [
                'article' => $article,
                'articleID' => $article->articleID,
                'titleHash' => \substr(StringUtil::getRandomID(), 0, 8),
            ], true);
        }

        return StringUtil::getAnchorTag($article->getLink(), $article->getTitle());
    }

    private function getArticle(int $articleID): ?ViewableArticle
    {
        return MessageEmbeddedObjectManager::getInstance()->getObject(
            'com.woltlab.wcf.article',
            $articleID
        );
    }
}
