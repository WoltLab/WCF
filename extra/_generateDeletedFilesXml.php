<?php

namespace wcf\extra;

if (\PHP_SAPI !== 'cli') {
    exit(1);
}

if ($argc !== 2) {
    echo "Usage: {$argv[0]} <repository>\n";
    echo "\tPayload expected via stdin\n";
    exit(1);
}

$repository = $argv[1];
$isCore = \is_dir("{$repository}/com.woltlab.wcf/");

$acpTemplates = [];
$files = [];
$templates = [];

$data = \file_get_contents('php://stdin');
$lines = \explode("\n", $data);
foreach ($lines as $line) {
    if ($line === '') {
        continue;
    }

    if ($isCore) {
        if (\str_starts_with($line, 'com.woltlab.wcf/')) {
            $templates[''][] = \preg_replace('~^com\.woltlab\.wcf/templates/([^.]++).tpl$~', '$1', $line);
        } else if (\str_starts_with($line, 'wcfsetup/install/files/acp/templates/')) {
            $acpTemplates[''][] = \preg_replace('~^wcfsetup/install/files/acp/templates/([^.]++).tpl$~', '$1', $line);
        } else {
            $files[''][] = \preg_replace('~^wcfsetup/install/files/~', '', $line);
        }

        continue;
    }

    if (\preg_match('~^(?<type>acptemplates|files|templates)(?:_(?<application>[^/]++))?/(?<pathname>.++)$~', $line, $matches)) {
        $type = $matches['type'];
        $application = $matches['application'] ?? '';
        $pathname = $matches['pathname'];

        switch ($type) {
            case 'acptemplates':
                $acpTemplates[$application][] = \preg_replace('~\.tpl$~', '', $pathname);
                break;

            case 'files':
                $files[$application][] = $pathname;
                break;

            case 'templates':
                $templates[$application][] = \preg_replace('~\.tpl$~', '', $pathname);
                break;
        }
    } else {
        echo "Unexpected line: {$line}\n";
        exit(1);
    }
}

\ksort($acpTemplates, \SORT_NATURAL);
\ksort($files, \SORT_NATURAL);
\ksort($templates, \SORT_NATURAL);

function generateXml(string $repository, string $type, array $data): void
{
    \ksort($data, \SORT_NATURAL);

    $pathname = "{$repository}/{$type}.xml";
    if ($data === []) {
        if (\file_exists($pathname)) {
            \unlink($pathname);
        }

        return;
    }

    $tagName = match ($type) {
        'acpTemplateDelete', 'templateDelete' => 'template',
        'fileDelete' => 'file',
    };

    $content = '';
    foreach ($data as $application => $entries) {
        \sort($entries, \SORT_NATURAL);
        foreach ($entries as $entry) {
            $content .= \sprintf(
                "\t\t<%s%s>%s</%s>\n",
                $tagName,
                $application ? ' application="' . $application . '"' : '',
                $entry,
                $tagName,
            );
        }
    }

    $content = <<<EOF
        <?xml version="1.0" encoding="UTF-8"?>
        <data xmlns="http://www.woltlab.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.woltlab.com http://www.woltlab.com/XSD/6.0/{$type}.xsd">
        \t<delete>
        {$content}\t</delete>
        </data>\n
        EOF;

    \file_put_contents($pathname, $content);
}

generateXml($repository, 'acpTemplateDelete', $acpTemplates);
generateXml($repository, 'fileDelete', $files);
generateXml($repository, 'templateDelete', $templates);
