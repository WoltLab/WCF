<?php

namespace wcf\system\code;

use wcf\system\bbcode\BBCodeHandler;
use wcf\system\Regex;
use wcf\system\WCF;

/**
 * Renders source code listings including syntax highlighting.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class SourceCodeRenderer
{
    /**
     * aliases for highlighters that are not known by the client-side highlighter
     * @var array<string, string>
     */
    private const HIGHLIGHTER_ALIASES = [
        'js' => 'javascript',
        'c++' => 'cpp',
        'tex' => 'latex',
        'shell' => 'bash',
    ];

    /**
     * already used ids for line numbers to prevent duplicate ids in the output
     * @var array<string, bool>
     */
    private static array $codeIDs = [];

    /**
     * Renders the given source code. If no highlighter is provided, the used
     * language is guessed based on the content.
     */
    public function render(
        string $content,
        string $highlighter = '',
        string $filename = '',
        int $startLineNumber = 1,
        string $codeIDPrefix = ''
    ): string {
        $content = $this->trimContent($content);

        if ($startLineNumber < 1) {
            $startLineNumber = 1;
        }

        $highlighter = $this->getHighlighter($content, $highlighter);

        $meta = BBCodeHandler::getInstance()->getHighlighterMeta();
        $title = WCF::getLanguage()->get('wcf.bbcode.code');
        if (isset($meta[$highlighter])) {
            $title = $meta[$highlighter]['title'];
        } else {
            $highlighter = '';
        }

        $lines = $this->splitLines($content);

        return WCF::getTPL()->render('wcf', 'shared_codeMetaCode', [
            'codeID' => $this->getCodeID($codeIDPrefix, $content),
            'startLineNumber' => $startLineNumber,
            'content' => $lines,
            'language' => $highlighter,
            'filename' => $filename,
            'title' => $title,
            'lines' => \count($lines),
        ]);
    }

    /**
     * Removes a leading and a trailing empty line from the given content.
     */
    public function trimContent(string $content): string
    {
        $content = \preg_replace('/^\s*\n/', '', $content);

        return \preg_replace('/\n\s*$/', '', $content);
    }

    /**
     * Returns the highlighter that should be used for the given content. The
     * highlighter is guessed if no highlighter is provided.
     */
    public function getHighlighter(string $content, string $highlighter = ''): string
    {
        $highlighter = $this->normalizeHighlighter($highlighter);
        if ($highlighter === '') {
            $highlighter = $this->guessHighlighter($content);
        }

        return $highlighter;
    }

    /**
     * Resolves known aliases of highlighter names.
     */
    public function normalizeHighlighter(string $highlighter): string
    {
        return self::HIGHLIGHTER_ALIASES[$highlighter] ?? $highlighter;
    }

    /**
     * Splits the content into single lines while preserving the line breaks.
     *
     * @return string[]
     */
    public function splitLines(string $content): array
    {
        $lines = \explode("\n", $content);
        $last = \array_pop($lines);
        $lines = \array_map(static fn (string $line) => $line . "\n", $lines);
        $lines[] = $last;

        return $lines;
    }

    /**
     * Returns a likely highlighter for the given content.
     */
    public function guessHighlighter(string $content): string
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
            || Regex::compile("from (\\S+) import (\\S+)")->match($content) !== 0
        ) {
            return 'python';
        }

        if (Regex::compile('^#!(/usr)?/bin/(ba|z)?sh')->match($content) !== 0) {
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
     * Returns a unique ID for the given code block.
     */
    public function getCodeID(string $prefix, string $code): string
    {
        $i = -1;
        // find an unused codeID
        do {
            $codeID = $prefix . \mb_substr(\sha1($code), 0, 6) . (++$i !== 0 ? '_' . $i : '');
        } while (isset(self::$codeIDs[$codeID]));

        // mark codeID as used
        self::$codeIDs[$codeID] = true;

        return $codeID;
    }
}
