<?php

namespace wcf\system\html\input\node;

use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * Processes `<kbd>` and ensures that they only contain raw text.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
class HtmlInputNodeKbd extends AbstractHtmlInputNode
{
    /**
     * @inheritDoc
     */
    protected $tagName = 'kbd';

    /**
     * @inheritDoc
     */
    public function isAllowed(AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            if (DOMUtil::isRemoved($element) || !$element->parentNode) {
                continue;
            }

            $containsElements = false;
            for ($i = 0, $length = $element->childNodes->length; $i < $length; $i++) {
                if ($element->childNodes->item($i)->nodeType !== \XML_TEXT_NODE) {
                    $containsElements = true;
                    break;
                }
            }

            if ($containsElements) {
                // Check if the *entire* content is striked through and wrap it.
                $isStrikedThrough = false;
                $strikethroughs = $htmlNodeProcessor->getXPath()->query("./s", $element);
                if ($strikethroughs->length === 1 && $strikethroughs->item(0)->textContent === $element->textContent) {
                    $isStrikedThrough = true;
                }

                $newElement = $element->ownerDocument->createElement('kbd');
                $newElement->appendChild(
                    $element->ownerDocument->createTextNode(
                        StringUtil::trim($element->textContent)
                    )
                );

                DOMUtil::replaceElement($element, $newElement, false);

                if ($isStrikedThrough) {
                    $strikethrough = $newElement->ownerDocument->createElement("s");
                    $newElement->parentNode->insertBefore($strikethrough, $newElement);
                    $strikethrough->append($newElement);
                }
            }
        }
    }
}
