<?php

namespace wcf\system\html\output\node;

use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\StringUtil;

/**
 * Removes empty paragraphs that were used to emulate paragraphs in earlier versions.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class HtmlOutputNodeP extends AbstractHtmlOutputNode
{
    /**
     * @inheritDoc
     */
    protected $tagName = 'p';

    /**
     * @inheritDoc
     */
    public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            if ($element->childElementCount === 1 && $element->firstElementChild) {
                $child = $element->firstElementChild;
                if ($child->tagName === 'br' && $child->getAttribute('data-cke-filler') !== 'true') {
                    // This is most likely a legacy paragraph that was inserted
                    // in earlier versions and is not longer required. We need
                    // to verify that there is no other text inside the node
                    // before removing it.
                    if (StringUtil::trim($element->textContent) === '') {
                        $element->remove();
                    }
                }
            }
        }
    }
}
