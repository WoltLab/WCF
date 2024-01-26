<?php

namespace wcf\system\html\output\node;

use wcf\system\bbcode\BBCodeHandler;
use wcf\system\event\EventHandler;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\Regex;
use wcf\system\WCF;

/**
 * Processes code listings.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
class HtmlOutputNodePre extends AbstractHtmlOutputNode
{
    /**
     * @inheritDoc
     */
    protected $tagName = 'pre';

    /**
     * already used ids for line numbers to prevent duplicate ids in the output
     * @var string[]
     */
    private static $codeIDs = [];

    /**
     * @inheritDoc
     */
    public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor)
    {
        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            if ($element->getAttribute('class') === 'woltlabHtml') {
                [$nodeIdentifier, $tagName] = $htmlNodeProcessor->getWcfNodeIdentifer();
                $htmlNodeProcessor->addNodeData($this, $nodeIdentifier, ['rawHTML' => $element->textContent]);

                $htmlNodeProcessor->renameTag($element, $tagName);
                continue;
            }

            switch ($this->outputType) {
                case 'text/html':
                    $context = $htmlNodeProcessor->getHtmlProcessor()->getContext();
                    $prefix = '';
                    // Create a unique prefix if possible
                    if (isset($context['objectType']) && isset($context['objectID'])) {
                        $prefix = \str_replace('.', '_', $context['objectType']) . '_' . $context['objectID'] . '_';
                    }
                    [$nodeIdentifier, $tagName] = $htmlNodeProcessor->getWcfNodeIdentifer();
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

    /**
     * @inheritDoc
     */
    public function replaceTag(array $data)
    {
        // HTML bbcode
        if (isset($data['rawHTML'])) {
            return $data['rawHTML'];
        }

        $content = \preg_replace('/^\s*\n/', '', $data['content']);
        $content = \preg_replace('/\n\s*$/', '', $content);

        $file = $data['file'];
        $highlighter = $data['highlighter'];
        $line = ($data['line'] < 1) ? 1 : $data['line'];

        switch ($highlighter) {
            case 'js':
                $highlighter = 'javascript';
                break;
            case 'c++':
                $highlighter = 'cpp';
                break;
            case 'tex':
                $highlighter = 'latex';
                break;
            case 'shell':
                $highlighter = 'bash';
                break;
        }

        if (!$highlighter) {
            $highlighter = $this->guessHighlighter($content);
        }
        $eventData = [
            'highlighter' => $highlighter,
            'data' => $data,
            'content' => $content,
        ];
        EventHandler::getInstance()->fireAction($this, 'selectHighlighter', $eventData);
        $highlighter = $eventData['highlighter'];

        $meta = BBCodeHandler::getInstance()->getHighlighterMeta();
        $title = WCF::getLanguage()->get('wcf.bbcode.code');
        if (isset($meta[$highlighter])) {
            $title = $meta[$highlighter]['title'];
        } else {
            $highlighter = null;
        }

        $splitContent = \explode("\n", $content);
        $last = \array_pop($splitContent);
        $splitContent = \array_map(static function ($item) {
            return $item . "\n";
        }, $splitContent);
        $splitContent[] = $last;

        // show template
        /** @noinspection PhpUndefinedMethodInspection */
        WCF::getTPL()->assign([
            'codeID' => $this->getCodeID($data['prefix'] ?? '', $content),
            'startLineNumber' => $line,
            'content' => $splitContent,
            'language' => $highlighter,
            'filename' => $file,
            'title' => $title,
            'lines' => \count($splitContent),
        ]);

        return WCF::getTPL()->fetch('shared_codeMetaCode');
    }

    /**
     * Returns a likely highlighter for the given content.
     *
     * @param string $content
     * @return string
     */
    public function guessHighlighter($content)
    {
        // PHP at the beginning is almost surely PHP.
        if (\str_starts_with($content, '<?php')) {
            return 'php';
        }

        if (
            \str_starts_with($content, 'SELECT')
            || \str_starts_with($content, 'UPDATE')
            || \str_starts_with($content, 'INSERT')
            || \str_starts_with($content, 'DELETE')
        ) {
            return 'sql';
        }

        if (\str_contains($content, 'import java.')) {
            return 'java';
        }

        if (\str_contains($content, 'using System;')) {
            return 'csharp';
        }

        if (
            \str_contains($content, "---")
            && \str_contains($content, "\n+++")
        ) {
            return 'diff';
        }

        if (\str_contains($content, "\n#include ")) {
            return 'c';
        }

        if (\str_starts_with($content, '#!/usr/bin/perl')) {
            return 'perl';
        }

        if (
            \str_starts_with($content, '#!/usr/bin/python')
            || \str_contains($content, 'def __init__(self')
            || Regex::compile("from (\\S+) import (\\S+)")->match($content)
        ) {
            return 'python';
        }

        if (Regex::compile('^#!/bin/(ba|z)?sh')->match($content)) {
            return 'bash';
        }

        if (
            \str_starts_with($content, 'FROM')
            && \str_contains($content, "RUN")
        ) {
            return 'docker';
        }

        if (
            \stripos($content, "RewriteRule") !== false
            || \stripos($content, "RewriteEngine On") !== false
            || \stripos($content, "AuthUserFile") !== false
        ) {
            return 'apacheconf';
        }

        if (\str_contains($content, '\\documentclass')) {
            return 'latex';
        }

        // PHP somewhere later might not necessarily be PHP, it could also be
        // a .patch or a Dockerfile.
        if (\str_contains($content, '<?php')) {
            return 'php';
        }

        if (
            \str_contains($content, '{/if}')
            && (
                \str_contains($content, '<div')
                || \str_contains($content, '<span')
            )
        ) {
            return 'smarty';
        }

        if (\str_contains($content, '<html')) {
            return 'html';
        }

        if (\str_starts_with($content, '<?xml')) {
            return 'xml';
        }

        if (\str_contains($content, '@mixin')) {
            return 'scss';
        }

        if (\str_contains($content, '!important;')) {
            return 'css';
        }

        if (\preg_match('/(^|\n)HTTP\\/[0-9]\\.[0-9] [0-9]{3}/', $content)) {
            return 'http';
        }

        return '';
    }

    /**
     * Returns a unique ID for this code block.
     *
     * @param string $prefix
     * @param string $code
     * @return  string
     */
    protected function getCodeID($prefix, $code)
    {
        $i = -1;
        // find an unused codeID
        do {
            $codeID = $prefix . \mb_substr(\sha1($code), 0, 6) . (++$i ? '_' . $i : '');
        } while (isset(self::$codeIDs[$codeID]));

        // mark codeID as used
        self::$codeIDs[$codeID] = true;

        return $codeID;
    }
}
