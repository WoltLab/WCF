<?php

namespace wcf\system\html\output\node;

use wcf\data\bbcode\media\provider\BBCodeMediaProvider;
use wcf\system\bbcode\HtmlBBCodeParser;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

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
            $attributes = $htmlNodeProcessor->parseAttributes($element->getAttribute('data-attributes'));

            if ($name === 'media' && $this->outputType === 'text/html') {
                $attributes = $this->liftMediaOutOfParagraph($element, $attributes);
            }

            [$nodeIdentifier, $tagName] = $htmlNodeProcessor->getWcfNodeIdentifier();

            $element = $htmlNodeProcessor->renameTag($element, $tagName);

            $htmlNodeProcessor->addNodeData($this, $nodeIdentifier, [
                'name' => $name,
                'attributes' => $attributes,
                'element' => $element,
            ]);
        }
    }

    /**
     * Moves an embedded media element out of its parent paragraph, because the
     * output is a `<div>` which may not be placed inside a `<p>`. The alignment
     * of the paragraph is carried over unless the media has its own alignment.
     *
     * @param list<mixed> $attributes
     * @return list<mixed>
     */
    private function liftMediaOutOfParagraph(\DOMElement $element, array $attributes): array
    {
        $paragraph = DOMUtil::closest($element, 'p');
        if ($paragraph === null) {
            return $attributes;
        }

        // Unsupported URLs are rendered as plain text and can stay inline.
        $url = StringUtil::trim($element->textContent);
        if (!\preg_match('~^https?://~i', $url) || !BBCodeMediaProvider::isMediaURL($url)) {
            return $attributes;
        }

        \assert($paragraph instanceof \DOMElement);
        if (($attributes[0] ?? 'none') === 'none') {
            $classNames = \explode(' ', $paragraph->getAttribute('class'));
            if (\in_array('text-center', $classNames)) {
                $attributes[0] = 'center';
            } elseif (\in_array('text-right', $classNames)) {
                $attributes[0] = 'right';
            }
        }

        $ancestor = $paragraph->parentNode;
        \assert($ancestor instanceof \DOMElement);

        DOMUtil::splitParentsUntil($element, $ancestor);
        $paragraph = DOMUtil::splitParentsUntil($element, $ancestor, false);

        DOMUtil::insertBefore($element, $paragraph);
        $ancestor->removeChild($paragraph);

        return $attributes;
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
