<?php

namespace wcf\system\html\toc;

use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Generates a table of contents for a message.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
class HtmlToc
{
    /**
     * @param \DOMDocument $document source document
     * @param string $idPrefix prefix for all generated ids, must not end with a delimiter
     * @return      string          the HTML of the generated table of contents or an empty string if there are too few headings
     */
    public static function forMessage(\DOMDocument $document, $idPrefix)
    {
        $titleRegex = new Regex('[^\p{L}\p{N}]+', Regex::UTF_8);

        // fetch all headings in their order of appearance
        $usedIDs = [];
        /** @var HtmlTocItem[] $headings */
        $headings = [];
        /** @var \DOMElement $hElement */
        foreach ((new \DOMXPath($document))->query('//h2 | //h3 | //h4') as $hElement) {
            $title = StringUtil::trim($hElement->textContent);
            if (empty($title)) {
                continue;
            }

            // remove illegal characters
            $id = \trim($titleRegex->replace($title, '-'), '-');

            // trim to 80 characters
            $id = \rtrim(\mb_substr($id, 0, 80), '-');
            $id = \mb_strtolower($id);

            if (isset($usedIDs[$id])) {
                $i = 2;
                do {
                    $newID = $id . '--' . ($i++);
                } while (isset($usedIDs[$newID]));
                $id = $newID;
            }
            $usedIDs[$id] = $id;

            // Using the 'normalized' title allows for human-readable anchors that will remain
            // valid even when new headings are being added or existing ones are removed. This
            // also covers the relocation of a heading to a new position.
            //
            // Unfortunately, making changes to the actual text of the heading will invalidate
            // existing anchors, because there is no other reliable way to identify them.
            //
            // The only solution for this problem would be the ability for user-defined ids, but
            // this comes with a whole lot of other issues, such as misleading anchors due to
            // significant changes to the phrasing. Worst of all, it would no longer work without
            // the need of user interactions, making it a somewhat cumbersome experience. KISS.
            $id = "{$idPrefix}-{$id}";

            $classes = $hElement->getAttribute('class');
            if (!empty($classes)) {
                $classes .= ' ';
            }
            $hElement->setAttribute('class', $classes . 'anchorFixedHeader');

            $hElement->setAttribute('id', $id);

            $headings[] = new HtmlTocItem(\intval(\substr($hElement->tagName, 1, 1)), $id, $title);
        }

        if (\count($headings) < 3) {
            // do not generate a table of contents for only one or two headings in total
            return '';
        }

        // we cannot expect the headings to be set-up in a nice hierarchy, e. g. there might
        // be no top-level ("2") heading, but instead all headings are level 3 or below
        $toc = new HtmlTocItem(0, '', '');
        $lastItem = $toc;
        foreach ($headings as $heading) {
            // find the parent heading based on the order
            while ($lastItem->getLevel() >= $heading->getLevel()) {
                $lastItem = $lastItem->getParent();
            }

            $depth = 3;
            if ($lastItem->getLevel() === 0) {
                $depth = 1;
            } else {
                if ($lastItem->getParent()->getLevel() === 0) {
                    $depth = 2;
                }
            }
            $heading->setDepth($depth);

            /** @noinspection PhpParamsInspection */
            $lastItem->addChild($heading);
            $lastItem = $heading;
        }

        return WCF::getTPL()->fetch('shared_messageTableOfContents', 'wcf', [
            'idPrefix' => $idPrefix,
            'items' => $toc->getIterator(),
        ]);
    }
}
