<?php

namespace wcf\system\html\upcast\node;

use wcf\data\bbcode\BBCodeCache;
use wcf\system\html\metacode\upcast\IMetacodeUpcast;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\DOMUtil;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class HtmlUpcastNodeWoltlabMetacode extends AbstractHtmlUpcastNode
{
    /**
     * @inheritDoc
     */
    protected $tagName = 'woltlab-metacode';

    #[\Override]
    public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        /** @var IMetacodeUpcast[] $upcasters */
        $upcasters = [];
        $nodes = [];

        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            if (DOMUtil::isRemoved($element) || $element->parentNode === null) {
                // ignore elements that existed, but have been removed
                // from the DOM due to action taken by an upcaster
                continue;
            }

            $name = $element->getAttribute('data-name');
            if ($name === 'abstract') {
                continue;
            }

            $attributes = $htmlNodeProcessor->parseAttributes($element->getAttribute('data-attributes'));
            if ($attributes === []) {
                $element->removeAttribute('data-attributes');
            }

            // check for upcast
            $upcast = $upcasters[$name] ?? null;
            if ($upcast === null) {
                $className = 'wcf\\system\\html\\metacode\\upcast\\' . \ucfirst($name) . 'MetacodeUpcast';
                if (\class_exists($className)) {
                    $upcast = new $className();

                    $upcasters[$name] = $upcast;
                }
            }
            if ($upcast === null) {
                continue;
            }

            $upcast->cacheObject($attributes);
            $nodes[] = [$element, $name, $upcast, $attributes];
        }

        foreach ($nodes as [$element, $name, $upcast, $attributes]) {
            if ($upcast->hasValidAttributes($attributes)) {
                $fragment = DOMUtil::childNodesToFragment($element);
                if (!$fragment->hasChildNodes()) {
                    $fragment->appendChild($fragment->ownerDocument->createTextNode(''));
                }

                $newElement = $upcast->upcast($fragment, $attributes);
                if (!($newElement instanceof \DOMElement)) {
                    throw new \UnexpectedValueException("Expected a valid DOMElement as return value.");
                }

                DOMUtil::replaceElement($element, $newElement);
                unset($fragment);
            } else {
                // Replace this with a text node
                $bbcode = BBCodeCache::getInstance()->getBBCodeByTag($name);
                if ($bbcode !== null) {
                    $newElement = $element->ownerDocument->createElement($bbcode->isBlockElement ? 'p' : 'span');
                } else {
                    $newElement = $element->ownerDocument->createElement('p');
                }
                /** @see HtmlBBCodeParser::buildBBCodeTag() */
                $attributes = \array_filter($attributes, fn($value) => $value !== null);

                if (!empty($attributes)) {
                    foreach ($attributes as &$attribute) {
                        $attribute = "'" . \addcslashes($attribute, "'") . "'";
                    }
                    unset($attribute);

                    $attributes = '=' . \implode(",", $attributes);
                } else {
                    $attributes = '';
                }

                $newElement->textContent = \sprintf('[%s%s][/%s]', $name, $attributes, $name);

                DOMUtil::replaceElement($element, $newElement);
            }
        }
    }

    #[\Override]
    public function replaceTag(array $data)
    {
        return $data['parsedTag'];
    }
}
