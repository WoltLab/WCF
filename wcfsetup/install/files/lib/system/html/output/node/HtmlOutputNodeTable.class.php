<?php

namespace wcf\system\html\output\node;

use wcf\system\html\node\AbstractHtmlNodeProcessor;

/**
 * Adds wrapper div for tables to allow content overflow with scrollbars.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output\Node
 * @since       3.0
 */
class HtmlOutputNodeTable extends AbstractHtmlOutputNode
{
    /**
     * @inheritDoc
     */
    protected $tagName = 'table';

    /**
     * @inheritDoc
     */
    public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        if ($this->outputType === 'text/html' || $this->outputType === 'text/simplified-html') {
            /** @var \DOMElement $element */
            foreach ($elements as $element) {
                // Detect cells which are visually in the last row of a table due to their
                // `rowspan` property.
                /** @var \DOMElement $td */
                foreach ($element->getElementsByTagName('td') as $td) {
                    $rowspan = $td->getAttribute('rowspan');
                    if ($rowspan) {
                        $nextTrCount = 0;
                        $nextSibling = $td->parentNode->nextSibling;
                        while ($nextSibling) {
                            if ($nextSibling->nodeType === \XML_ELEMENT_NODE && $nextSibling->tagName === "tr") {
                                $nextTrCount++;
                            }
                            $nextSibling = $nextSibling->nextSibling;
                        }

                        if ($rowspan - 1 === $nextTrCount) {
                            $class = $td->getAttribute('class');
                            if ($class) {
                                $class .= " ";
                            }
                            $class .= "lastRow";

                            $td->setAttribute('class', $class);
                        }
                    }
                }

                // check if table is not contained within another table
                $parent = $element;
                while ($parent = $parent->parentNode) {
                    if ($parent->nodeName === 'table') {
                        continue 2;
                    }
                }

                $div = $element->ownerDocument->createElement('div');
                $div->setAttribute('class', 'messageTableOverflow');

                $element->parentNode->insertBefore($div, $element);
                $div->appendChild($element);
            }
        }
    }
}
