<?php

namespace wcf\system\html\node;

use wcf\data\unfurl\url\UnfurlUrlAction;
use wcf\util\DOMUtil;
use wcf\util\Url;

/**
 * Helper class to unfurl link objects.
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Node
 * @since       5.4
 */
class HtmlNodeUnfurlLink extends HtmlNodePlainLink
{
    public const UNFURL_URL_ID_ATTRIBUTE_NAME = "data-unfurl-url-id";

    /**
     * Marks a link element with the UnfurlUrlID.
     */
    public static function setUnfurl(HtmlNodePlainLink $link): void
    {
        if ($link->isStandalone() && Url::is($link->href) && !Url::parse($link->href)['port']) {
            self::removeStyling($link);

            $object = new UnfurlUrlAction([], 'findOrCreate', [
                'data' => [
                    'url' => $link->href,
                ],
            ]);
            $returnValues = $object->executeAction();

            $link->link->setAttribute(self::UNFURL_URL_ID_ATTRIBUTE_NAME, $returnValues['returnValues']->urlID);
        }
    }

    private static function removeStyling(HtmlNodePlainLink $element): void
    {
        foreach ($element->topLevelParent->childNodes as $child) {
            DOMUtil::removeNode($child);
        }

        $element->topLevelParent->appendChild($element->link);
    }
}
