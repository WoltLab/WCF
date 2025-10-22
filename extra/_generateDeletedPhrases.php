<?php

namespace wcf\extra;

if (\PHP_SAPI !== 'cli') {
    exit(1);
}

// This code makes a few assumptions about the appearance of the XML file for
// the sake of simplicity.
//
// It’s also the most awful way to edit XML but enables us to generate the
// <delete> block without messing with any (manual) formatting currently
// present.
//
// Most important, this generates small diffs by only touching the parts that
// actually change.

if ($argc !== 2) {
    echo "Usage: {$argv[0]} <pathname>\n";
    echo "\tPayload expected via stdin\n";
    exit(1);
}

$pathname = $argv[1];

$data = \file_get_contents('php://stdin');

// The data received from stdin is the old branch.
$xml = getXmlFromData($data);
$expectedIdentifiers = getIdentifiers($xml);

$data = \file_get_contents($pathname);
$xml = getXmlFromData($data);
$actualIdentifiers = getIdentifiers($xml);

$missingIdentifiers = \array_diff($expectedIdentifiers, $actualIdentifiers);

$hasDeleteBlock = $xml->xpath('/ns:language/ns:delete');
\assert(\is_array($hasDeleteBlock));
$hasDeleteBlock = $hasDeleteBlock !== [];

if ($missingIdentifiers === [] && $hasDeleteBlock) {
    return;
}

$noIndentation = \substr_count($data, "\n<import>\n");
$indented = \substr_count($data, "\n\t<import>\n");

if (($noIndentation !== 1 && $indented !== 1) || $noIndentation === $indented) {
    echo "Unable to determine the indentation of <import>, found {$noIndentation}/{$indented} (not indented/indented)!\n";
    exit(1);
}

$indentation = $noIndentation ? "" : "\t";

\sort($missingIdentifiers, \SORT_NATURAL);

if ($hasDeleteBlock) {
    // Find the position of {$indentation}<delete> and {$indentation}</delete>
    $startTag = "\n{$indentation}<delete>\n";
    $start = \mb_strpos($data, $startTag);
    $endTag = "\n{$indentation}</delete>\n";
    $end = \mb_strpos($data, $endTag);

    if ($start === false && $end === false) {
        if ($indentation === "") {
            $indentation = "\t";
        }

        $startTag = "\n{$indentation}<delete>\n";
        $start = \mb_strpos($data, $startTag);
        $endTag = "\n{$indentation}</delete>\n";
        $end = \mb_strpos($data, $endTag);
    }

    if ($start === false || $end === false) {
        echo "Could not find the start (" . \get_debug_type($start) . ") and end (" . \get_debug_type($end) . ") positions.\n";
        exit(1);
    }

    $missingIdentifiers = \array_map(
        static fn($identifier) => "{$indentation}\t<item name=\"{$identifier}\"/>",
        $missingIdentifiers
    );

    $before = \mb_substr($data, 0, $start + \mb_strlen($startTag));
    $after = \mb_substr($data, $end);

    \file_put_contents("{$pathname}", $before . \implode("\n", $missingIdentifiers) . $after);
} else {
    // Find the position of the closing {$indentation}</import>
    $endTag = "\n{$indentation}</import>\n";
    $end = \mb_strpos($data, $endTag);

    if ($end === false) {
        echo "Could not find the end position.\n";
        exit(1);
    }

    $missingIdentifiers = \array_map(
        static fn($identifier) => "{$indentation}\t<item name=\"{$identifier}\"/>",
        $missingIdentifiers
    );

    $before = \mb_substr($data, 0, $end + \mb_strlen($endTag));
    $after = \mb_substr($data, $end + \mb_strlen($endTag));

    $deleteStart = "{$indentation}<delete>\n";
    $deleteEnd = "\n{$indentation}</delete>\n";

    \file_put_contents("{$pathname}", $before . $deleteStart . \implode("\n", $missingIdentifiers) . $deleteEnd . $after);
}

/**
 * @return list<string>
 */
function getIdentifiers(\SimpleXMLElement $xml): array
{
    $identifiers = [];
    foreach ($xml->xpath('/ns:language/ns:import/ns:category/ns:item') as $item) {
        foreach ($item->attributes() as $name => $value) {
            if ($name === 'name') {
                $identifiers[] = (string)$value;
                break;
            }
        }
    }

    \assert($identifiers !== []);

    return $identifiers;
}

function getXmlFromData(string $data): \SimpleXMLElement
{
    $xml = \simplexml_load_string($data);
    \assert($xml !== false);

    $xml->registerXPathNamespace('ns', 'http://www.woltlab.com');

    return $xml;
}
