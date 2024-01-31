<?php

namespace wcf\system\html\input\node;

use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyCache;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\bbcode\HtmlBBCodeParser;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\util\DOMUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Processes `<img>` to handle embedded attachments.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
class HtmlInputNodeImg extends AbstractHtmlInputNode
{
    /**
     * number of found smilies
     * @var int
     */
    protected $smiliesFound = 0;

    /**
     * @inheritDoc
     */
    protected $tagName = 'img';

    /**
     * @inheritDoc
     */
    public function isAllowed(AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        if (BBCodeHandler::getInstance()->isAvailableBBCode('img')) {
            return [];
        }

        $foundImage = false;

        // check if there are only attachments, media or smilies
        /** @var \DOMElement $element */
        foreach ($htmlNodeProcessor->getDocument()->getElementsByTagName('img') as $element) {
            $class = $element->getAttribute('class');
            if (!\preg_match('~\b(?:woltlabAttachment|woltlabSuiteMedia|smiley)\b~', $class)) {
                $foundImage = true;
                break;
            }
        }

        if (!$foundImage) {
            return [];
        }

        return ['img'];
    }

    /**
     * @inheritDoc
     */
    public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        $this->smiliesFound = 0;

        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            $this->mirrorWidthAttribute($element);
            $this->moveClassNameFromFigureToImage($element);

            $class = $element->getAttribute('class');
            if (\preg_match('~\bwoltlabAttachment\b~', $class)) {
                $this->handleAttachment($element, $class);
            } elseif (\preg_match('~\bwoltlabSuiteMedia\b~', $class)) {
                $this->handleMedium($element, $class);
            } elseif (\preg_match('~\bsmiley\b~', $class)) {
                $this->handleSmiley($element);
            }
        }
    }

    /**
     * Returns the number of smilies found within the message.
     *
     * @return      int
     */
    public function getSmileyCount()
    {
        return $this->smiliesFound;
    }

    /**
     * Replaces image element with attachment metacode element.
     *
     * @param \DOMElement $element
     * @param string $class
     */
    protected function handleAttachment(\DOMElement $element, $class)
    {
        $attachmentID = \intval($element->getAttribute('data-attachment-id'));
        if (!$attachmentID) {
            return;
        }

        $float = 'none';
        $thumbnail = false;

        if (\strpos($element->getAttribute('src'), 'thumbnail=1') !== false) {
            $thumbnail = true;
        }

        $replaceElement = $element;
        $figure = $this->getParentFigure($element);
        if ($figure !== null) {
            if (\preg_match('~\b(?<float>image-style-side-left|image-style-side)\b~', $figure->getAttribute('class'), $matches)) {
                $float = ($matches['float'] === 'image-style-side-left') ? 'left' : 'right';
            } else {
                $float = 'center';
            }
            $replaceElement = $figure;

            if (($element->parentNode instanceof \DOMElement) && $element->parentNode->nodeName === "a") {
                DOMUtil::replaceElement($figure, $element->parentNode, false);
                $replaceElement = $element;
            }
        }

        $width = $element->getAttribute("data-width");
        if (\preg_match('~(?<width>\d+)px$~', $width, $matches)) {
            $width = (int)$matches['width'];
        } else {
            $width = "auto";
        }

        if ($width !== "auto") {
            $thumbnail = $width;
        }

        $attributes = [
            $attachmentID,
            $float,
            $thumbnail,
        ];

        $newElement = $element->ownerDocument->createElement('woltlab-metacode');
        $newElement->setAttribute('data-name', 'attach');
        $newElement->setAttribute('data-attributes', \base64_encode(JSON::encode($attributes)));
        DOMUtil::replaceElement($replaceElement, $newElement, false);
    }

    /**
     * Replaces image element with media metacode element.
     *
     * @param \DOMElement $element
     * @param string $class
     */
    protected function handleMedium(\DOMElement $element, $class)
    {
        $mediumID = \intval($element->getAttribute('data-media-id'));
        if (!$mediumID) {
            return;
        }

        $float = 'none';
        $thumbnail = 'original';
        $width = $element->getAttribute("data-width");
        if (\preg_match('~(?<width>\d+)px$~', $width, $matches)) {
            $width = (int)$matches['width'];
        } else {
            $width = "auto";
        }

        if (
            \preg_match(
                '~thumbnail=(?P<thumbnail>tiny|small|large|medium)\b~',
                $element->getAttribute('src'),
                $matches
            )
        ) {
            $thumbnail = $matches['thumbnail'];
        }

        $replaceElement = $element;
        $parent = $this->getParentFigure($element);
        if ($parent !== null) {
            if (\preg_match('~\b(?<float>image-style-side-left|image-style-side)\b~', $parent->getAttribute('class'), $matches)) {
                $float = ($matches['float'] === 'image-style-side-left') ? 'left' : 'right';
            } else {
                $float = 'center';
            }

            $replaceElement = $parent;
        }

        $attributes = [
            $mediumID,
            $thumbnail,
            $float,
            $width,
        ];

        $newElement = $element->ownerDocument->createElement('woltlab-metacode');
        $newElement->setAttribute('data-name', 'wsm');
        $newElement->setAttribute('data-attributes', \base64_encode(JSON::encode($attributes)));
        DOMUtil::replaceElement($replaceElement, $newElement, false);

        // The media bbcode is a block element that may not be placed inside inline elements.
        $parent = $newElement;
        $blockLevelParent = null;
        $blockElements = HtmlBBCodeParser::getInstance()->getBlockBBCodes();
        while ($parent = $parent->parentNode) {
            \assert($parent instanceof \DOMElement);

            switch ($parent->nodeName) {
                case 'a':
                    // Permit the media element to be placed inside a link.
                    break 2;

                case 'blockquote':
                case 'body':
                case 'code':
                case 'div':
                case 'li':
                case 'p':
                case 'td':
                case 'woltlab-quote':
                case 'woltlab-spoiler':
                    $blockLevelParent = $parent;
                    break 2;

                case 'woltlab-metacode':
                    if (\in_array($parent->getAttribute('data-name'), $blockElements)) {
                        $blockLevelParent = $parent;
                        break 2;
                    }
                    break;
            }
        }

        if ($blockLevelParent !== null) {
            \assert($parent instanceof \DOMElement);
            $element = DOMUtil::splitParentsUntil($newElement, $parent);
            if ($element !== $newElement) {
                DOMUtil::insertBefore($newElement, $element);
            }
        }
    }

    /**
     * Replaces image element with smiley metacode element.
     *
     * @param \DOMElement $element
     */
    protected function handleSmiley(\DOMElement $element)
    {
        $code = $element->getAttribute('alt');

        /** @var Smiley $smiley */
        $smiley = SmileyCache::getInstance()->getSmileyByCode($code);
        if ($smiley === null || $this->smiliesFound === 50) {
            $element->parentNode->insertBefore($element->ownerDocument->createTextNode($code), $element);
            $element->parentNode->removeChild($element);
        } else {
            // enforce database values for src, srcset and style
            $element->setAttribute('src', $smiley->getURL());

            if ($smiley->getHeight()) {
                $element->setAttribute('height', (string)$smiley->getHeight());
            } else {
                $element->removeAttribute('height');
            }

            if ($smiley->smileyPath2x) {
                $element->setAttribute('srcset', $smiley->getURL2x() . ' 2x');
            } else {
                $element->removeAttribute('srcset');
            }

            $this->smiliesFound++;
        }
    }

    /**
     * @since 6.0
     */
    protected function mirrorWidthAttribute(\DOMElement $element): void
    {
        $width = $element->getAttribute("data-width");
        if ($width && \preg_match('~^\d+px$~', $width)) {
            $style = $element->getAttribute("style");
            if ($style !== "") {
                $style .= "; ";
            }

            $style .= "width: {$width}";
            $element->setAttribute("style", $style);
        } else {
            $element->removeAttribute("data-width");
        }
    }

    /**
     * Setting attachments or embedded media to float will cause the CSS class
     * name to appear on the `<figure>` rather than the `<img>` itself.
     *
     * @since 6.0
     */
    protected function moveClassNameFromFigureToImage(\DOMElement $img): void
    {
        $figure = $this->getParentFigure($img);
        if ($figure === null) {
            return;
        }

        $classNames = \array_filter(
            \array_map(
                fn (string $className) => StringUtil::trim($className),
                \explode(
                    ' ',
                    $figure->getAttribute("class")
                ),
            ),
            static function (string $className) use ($img) {
                if ($className !== 'woltlabSuiteMedia' && $className !== 'woltlabAttachment') {
                    return true;
                }

                $img->setAttribute("class", $img->getAttribute("class") . " {$className}");

                return false;
            }
        );

        $figure->setAttribute("class", \implode(' ', $classNames));
    }

    private function getParentFigure(\DOMElement $img): ?\DOMElement
    {
        $parent = $img->parentNode;
        if ($parent instanceof \DOMElement) {
            if ($parent->nodeName === 'figure') {
                return $parent;
            }
            if ($parent->nodeName === 'a') {
                $parent = $parent->parentNode;
                if ($parent instanceof \DOMElement && $parent->nodeName === 'figure') {
                    return $parent;
                }
            }
        }

        return null;
    }
}
