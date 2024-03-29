<?php

namespace wcf\system\html\output\node;

use wcf\system\bbcode\HtmlBBCodeParser;
use wcf\system\html\node\AbstractHtmlNodeProcessor;

/**
 * Processes bbcodes represented by `<woltlab-metacode>`.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
class HtmlOutputNodeWoltlabMetacode extends AbstractHtmlOutputNode
{
    /**
     * @inheritDoc
     */
    protected $tagName = 'woltlab-metacode';

    /**
     * @inheritDoc
     */
    public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $name = $element->getAttribute('data-name');
            $attributes = $element->getAttribute('data-attributes');

            [$nodeIdentifier, $tagName] = $htmlNodeProcessor->getWcfNodeIdentifer();

            $element = $htmlNodeProcessor->renameTag($element, $tagName);

            $htmlNodeProcessor->addNodeData($this, $nodeIdentifier, [
                'name' => $name,
                'attributes' => $htmlNodeProcessor->parseAttributes($attributes),
                'element' => $element,
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function replaceTag(array $data)
    {
        HtmlBBCodeParser::getInstance()->setOutputType($this->outputType);

        return HtmlBBCodeParser::getInstance()->getHtmlOutput($data['name'], $data['attributes'], $data['element']);
    }
}
