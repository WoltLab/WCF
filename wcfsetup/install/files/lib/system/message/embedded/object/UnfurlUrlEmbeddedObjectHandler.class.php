<?php

namespace wcf\system\message\embedded\object;

use wcf\data\unfurl\url\UnfurlUrlList;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\html\node\HtmlNodeUnfurlLink;

/**
 * Represents the unfurl url embedded object handlers.
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Message\Embedded\Object
 * @since       5.4
 */
class UnfurlUrlEmbeddedObjectHandler extends AbstractMessageEmbeddedObjectHandler
{
    /**
     * @inheritDoc
     */
    public function loadObjects(array $objectIDs)
    {
        $urlList = new UnfurlUrlList();
        $urlList->getConditionBuilder()->add('unfurl_url.urlID IN (?)', [$objectIDs]);
        $urlList->readObjects();

        return $urlList->getObjects();
    }

    /**
     * @inheritDoc
     */
    public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData)
    {
        $unfurlUrlIDs = [];
        foreach ($htmlInputProcessor->getHtmlInputNodeProcessor()->getDocument()->getElementsByTagName('a') as $element) {
            /** @var \DOMElement $element */
            $id = \intval($element->getAttribute(HtmlNodeUnfurlLink::UNFURL_URL_ID_ATTRIBUTE_NAME));

            if (!empty($id)) {
                $unfurlUrlIDs[] = $id;
            }
        }

        return \array_unique($unfurlUrlIDs);
    }
}
