<?php

namespace wcf\system\bbcode;

use wcf\data\page\Page;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Parses the [wsp] bbcode tag.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
final class WoltLabSuitePageBBCode extends AbstractBBCode
{
    /**
     * @inheritDoc
     */
    public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser): string
    {
        $pageID = (!empty($openingTag['attributes'][0])) ? \intval($openingTag['attributes'][0]) : 0;
        if (!$pageID) {
            return '';
        }

        $title = (!empty($openingTag['attributes'][1])) ? StringUtil::trim($openingTag['attributes'][1]) : '';

        /** @var Page $page */
        $page = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.page', $pageID);
        if ($page !== null) {
            return StringUtil::getAnchorTag($page->getLink(), $title ?: $page->getTitle());
        }

        return WCF::getTPL()->fetch('shared_contentNotVisible', sandbox: true);
    }
}
