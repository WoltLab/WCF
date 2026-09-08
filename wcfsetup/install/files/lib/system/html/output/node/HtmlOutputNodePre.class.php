<?php

namespace wcf\system\html\output\node;

use wcf\system\code\SourceCodeRenderer;
use wcf\system\event\EventHandler;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\WCF;

/**
 * Processes code listings.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class HtmlOutputNodePre extends AbstractHtmlOutputNode
{
    /**
     * @inheritDoc
     */
    protected $tagName = 'pre';

    private ?SourceCodeRenderer $renderer = null;

    #[\Override]
    public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            if ($element->getAttribute('class') === 'woltlabHtml') {
                [$nodeIdentifier, $tagName] = $htmlNodeProcessor->getWcfNodeIdentifier();
                $htmlNodeProcessor->addNodeData($this, $nodeIdentifier, ['rawHTML' => $element->textContent]);

                $htmlNodeProcessor->renameTag($element, $tagName);
                continue;
            }

            switch ($this->outputType) {
                case 'text/html':
                    \assert($htmlNodeProcessor instanceof HtmlOutputNodeProcessor);
                    $context = $htmlNodeProcessor->getHtmlProcessor()->getContext();
                    $prefix = '';
                    // Create a unique prefix if possible
                    $prefix = \str_replace('.', '_', $context['objectType']) . '_' . $context['objectID'] . '_';
                    [$nodeIdentifier, $tagName] = $htmlNodeProcessor->getWcfNodeIdentifier();
                    $htmlNodeProcessor->addNodeData($this, $nodeIdentifier, [
                        'content' => $element->textContent,
                        'file' => $element->getAttribute('data-file'),
                        'highlighter' => $element->getAttribute('data-highlighter'),
                        'line' => $element->hasAttribute('data-line') ? $element->getAttribute('data-line') : 1,
                        'skipInnerContent' => true,
                        'prefix' => $prefix,
                    ]);

                    $htmlNodeProcessor->renameTag($element, $tagName);
                    break;

                case 'text/simplified-html':
                case 'text/plain':
                    $htmlNodeProcessor->replaceElementWithText(
                        $element,
                        WCF::getLanguage()->getDynamicVariable(
                            'wcf.bbcode.code.simplified',
                            ['lines' => \substr_count($element->nodeValue, "\n") + 1]
                        ),
                        true
                    );
                    break;
            }
        }
    }

    #[\Override]
    public function replaceTag(array $data)
    {
        // HTML bbcode
        if (isset($data['rawHTML'])) {
            return $data['rawHTML'];
        }

        $content = $this->getRenderer()->trimContent($data['content']);
        $highlighter = $this->getRenderer()->getHighlighter($content, $data['highlighter']);

        $eventData = [
            'highlighter' => $highlighter,
            'data' => $data,
            'content' => $content,
        ];
        EventHandler::getInstance()->fireAction($this, 'selectHighlighter', $eventData);

        return $this->getRenderer()->render(
            $content,
            $eventData['highlighter'],
            $data['file'],
            (int)$data['line'],
            $data['prefix'] ?? ''
        );
    }

    private function getRenderer(): SourceCodeRenderer
    {
        return $this->renderer ??= new SourceCodeRenderer();
    }

    /**
     * Returns a likely highlighter for the given content.
     *
     * @return string
     */
    public function guessHighlighter(string $content)
    {
        return $this->getRenderer()->guessHighlighter($content);
    }

    /**
     * Returns a unique ID for this code block.
     *
     * @return  string
     */
    protected function getCodeID(string $prefix, string $code)
    {
        return $this->getRenderer()->getCodeID($prefix, $code);
    }
}
