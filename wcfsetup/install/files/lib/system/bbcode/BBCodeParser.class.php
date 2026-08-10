<?php

namespace wcf\system\bbcode;

use wcf\data\bbcode\attribute\BBCodeAttribute;
use wcf\data\bbcode\BBCode;
use wcf\data\bbcode\BBCodeCache;
use wcf\system\SingletonFactory;

/**
 * Parses bbcode tags in text.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @phpstan-type BBCodeTag array{
 *  name: string,
 *  closing: bool,
 *  source: string,
 *  attributes?: list<string>,
 * }
 */
class BBCodeParser extends SingletonFactory
{
    /**
     * list of bbcodes
     * @var array<string, BBCode>
     */
    protected $bbcodes = [];

    /**
     * output type
     * @var string
     */
    protected $outputType = 'text/html';

    /**
     * source text
     * @var string
     */
    protected $text = '';

    /**
     * parsed text
     * @var string
     */
    protected $parsedText = '';

    /**
     * @var list<BBCodeTag>
     */
    protected $tagArray = [];

    /**
     * @var array<int, string>
     */
    protected $textArray = [];

    /**
     * regular expression for source code tags
     * @var string
     */
    protected $sourceCodeRegEx = '';

    #[\Override]
    protected function init()
    {
        // get bbcodes
        $this->bbcodes = BBCodeCache::getInstance()->getBBCodes();

        // handle source codes
        $sourceCodeTags = [];
        foreach ($this->bbcodes as $bbcode) {
            if ($bbcode->isSourceCode !== 0) {
                $sourceCodeTags[] = $bbcode->bbcodeTag;
            }
        }
        if ($sourceCodeTags !== []) {
            $this->sourceCodeRegEx = \implode('|', $sourceCodeTags);
        }
    }

    /**
     * Sets the output type of the parser.
     *
     * @return void
     */
    public function setOutputType(string $outputType)
    {
        $this->outputType = $outputType;
    }

    /**
     * Returns the current output type.
     *
     * @return string
     */
    public function getOutputType()
    {
        return $this->outputType;
    }

    /**
     * Sets the text to be parsed.
     *
     * @return void
     */
    public function setText(string $text)
    {
        $this->text = $text;
    }

    /**
     * Parses the given text.
     *
     * @return string parsed text
     */
    public function parse(string $text)
    {
        $this->setText($text);
        $this->buildTagArray();
        $this->buildXMLStructure();
        $this->buildParsedString();

        return $this->parsedText;
    }

    /**
     * Builds a valid xml structure of bbcode tags.
     * Inserts unclosed tags automatically.
     *
     * @return void
     */
    public function buildXMLStructure()
    {
        // stack for open tags
        $openTagStack = $openTagDataStack = [];
        $newTagArray = [];
        $newTextArray = [];

        $i = -1;
        foreach ($this->tagArray as $i => $tag) {
            if ($tag['closing']) {
                // closing tag
                if (\in_array($tag['name'], $openTagStack)) {
                    // close unclosed tags
                    $tmpOpenTags = [];
                    while (($previousTag = \end($openTagStack)) !== $tag['name']) {
                        $nextIndex = \count($newTagArray);
                        $newTagArray[$nextIndex] = $this->buildTag('[/' . $previousTag . ']');
                        if (!isset($newTextArray[$nextIndex])) {
                            $newTextArray[$nextIndex] = '';
                        }
                        $newTextArray[$nextIndex] .= $this->textArray[$i];
                        $this->textArray[$i] = '';
                        $tmpOpenTags[] = \end($openTagDataStack);
                        \array_pop($openTagStack);
                        \array_pop($openTagDataStack);
                    }

                    $nextIndex = \count($newTagArray);
                    $newTagArray[$nextIndex] = $tag;
                    \array_pop($openTagStack);
                    \array_pop($openTagDataStack);
                    if (!isset($newTextArray[$nextIndex])) {
                        $newTextArray[$nextIndex] = '';
                    }
                    $newTextArray[$nextIndex] .= $this->textArray[$i];

                    // open closed unclosed tags
                    while ($tmpTag = \end($tmpOpenTags)) {
                        $nextIndex = \count($newTagArray);
                        $newTagArray[$nextIndex] = $tmpTag;
                        if (!isset($newTextArray[$nextIndex])) {
                            $newTextArray[$nextIndex] = '';
                        }
                        $openTagStack[] = $tmpTag['name'];
                        $openTagDataStack[] = $tmpTag;
                        \array_pop($tmpOpenTags);
                    }
                } else {
                    // no such tag open
                    // handle as plain text
                    $this->textArray[$i] .= $tag['source'];
                    $last = \count($newTagArray);
                    if (!isset($newTextArray[$last])) {
                        $newTextArray[$last] = '';
                    }
                    $newTextArray[$last] .= $this->textArray[$i];
                }
            } else {
                // opening tag
                if ($this->isValidTag($tag)) {
                    $openTagStack[] = $tag['name'];
                    $openTagDataStack[] = $tag;
                    $nextIndex = \count($newTagArray);
                    $newTagArray[$nextIndex] = $tag;
                    if (!isset($newTextArray[$nextIndex])) {
                        $newTextArray[$nextIndex] = '';
                    }
                    $newTextArray[$nextIndex] .= $this->textArray[$i];
                } else {
                    $this->textArray[$i] .= $tag['source'];
                    $last = \count($newTagArray);
                    if (!isset($newTextArray[$last])) {
                        $newTextArray[$last] = '';
                    }
                    $newTextArray[$last] .= $this->textArray[$i];
                }
            }
        }

        $last = \count($newTagArray);
        if (!isset($newTextArray[$last])) {
            $newTextArray[$last] = '';
        }
        $newTextArray[$last] .= $this->textArray[$i + 1];

        // close unclosed open tags
        while (\end($openTagStack)) {
            $nextIndex = \count($newTagArray);
            $newTagArray[$nextIndex] = $this->buildTag('[/' . \end($openTagStack) . ']');
            if (!isset($newTextArray[$nextIndex])) {
                $newTextArray[$nextIndex] = '';
            }
            \array_pop($openTagStack);
            \array_pop($openTagDataStack);
        }

        $this->tagArray = $newTagArray;
        $this->textArray = $newTextArray;
    }

    /**
     * Validates the attributes of a tag.
     *
     * @param BBCodeTag $tag
     * @return bool
     */
    public function isValidTag(array $tag)
    {
        if (isset($tag['attributes']) && \count($tag['attributes']) > \count($this->bbcodes[$tag['name']]->getAttributes())) {
            return false;
        }

        $tagAttributes = ($tag['attributes'] ?? []);

        // right trim any attributes that are truly empty (= zero-length string) and are defined to be optional
        $bbcodeAttributes = $this->bbcodes[$tag['name']]->getAttributes();
        // reverse sort the bbcode attributes to start with the last attribute
        \usort($bbcodeAttributes, static function (BBCodeAttribute $a, BBCodeAttribute $b) {
            if ($a->attributeNo === $b->attributeNo) {
                return 0;
            }

            return ($a->attributeNo < $b->attributeNo) ? 1 : -1;
        });
        foreach ($bbcodeAttributes as $attribute) {
            if ($attribute->required !== 0) {
                break;
            }

            $i = $attribute->attributeNo;
            if (isset($tagAttributes[$i]) && $tagAttributes[$i] === '' && !isset($tagAttributes[$i + 1])) {
                unset($tagAttributes[$i]);
            } else {
                break;
            }
        }

        foreach ($this->bbcodes[$tag['name']]->getAttributes() as $attribute) {
            if (!$this->isValidTagAttribute($tagAttributes, $attribute)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validates an attributes of a tag.
     *
     * @param list<string> $tagAttributes
     * @param BBCodeAttribute $definedTagAttribute
     * @return bool
     */
    protected function isValidTagAttribute(array $tagAttributes, BBCodeAttribute $definedTagAttribute)
    {
        if ($definedTagAttribute->validationPattern !== '' && isset($tagAttributes[$definedTagAttribute->attributeNo])) {
            // validate attribute
            if (
                !\preg_match(
                    '~' . \str_replace('~', '\~', $definedTagAttribute->validationPattern) . '~i',
                    $tagAttributes[$definedTagAttribute->attributeNo]
                )
            ) {
                return false;
            }
        }

        if ($definedTagAttribute->required !== 0 && $definedTagAttribute->useText === 0 && !isset($tagAttributes[$definedTagAttribute->attributeNo])) {
            return false;
        }

        return true;
    }

    /**
     * Returns true if the text inside the given text needs to be buffered.
     *
     * @param BBCodeTag $tag
     * @return bool
     */
    protected function needBuffering(array $tag)
    {
        // check for special bbcode class
        if (!empty($this->bbcodes[$tag['name']]->className)) {
            return true;
        }

        // search 'useText' attributes
        foreach ($this->bbcodes[$tag['name']]->getAttributes() as $attribute) {
            if ($attribute->useText !== 0 && !isset($tag['attributes'][$attribute->attributeNo])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Builds the opening tag.
     *
     * @param BBCodeTag $tag
     * @return string
     */
    protected function buildOpeningTag(array $tag)
    {
        // build attributes
        $attributesString = '';
        foreach ($this->bbcodes[$tag['name']]->getAttributes() as $attribute) {
            if (isset($tag['attributes'][$attribute->attributeNo])) {
                $attributeString = '';
                if (!empty($attribute->attributeHtml)) {
                    $attributeString = ' ' . $attribute->attributeHtml;
                }

                if (!empty($attributeString)) {
                    $attributesString .= \sprintf($attributeString, $tag['attributes'][$attribute->attributeNo]);
                }
            }
        }

        // build tag
        if (!empty($this->bbcodes[$tag['name']]->htmlOpen)) {
            return '<' . $this->bbcodes[$tag['name']]->htmlOpen . $attributesString . (empty($this->bbcodes[$tag['name']]->htmlClose) ? ' /' : '') . '>';
        }

        return '';
    }

    /**
     * Builds the closing tag.
     *
     * @param BBCodeTag $tag
     * @return string
     */
    protected function buildClosingTag(array $tag)
    {
        if (!empty($this->bbcodes[$tag['name']]->htmlClose)) {
            return '</' . $this->bbcodes[$tag['name']]->htmlClose . '>';
        }

        return '';
    }

    /**
     * Returns true if the given tag is allowed in the given list of open tags.
     *
     * @param mixed[] $openTags
     * @return bool
     * @deprecated 5.5 This method is useless since 3.0, do not use it.
     */
    protected function isAllowed(array $openTags, string $tag, bool $closing = false)
    {
        // This is a left-over from the pre WoltLab Suite 3.0 era.
        return true;
    }

    /**
     * Builds the parsed string.
     *
     * @return void
     */
    public function buildParsedString()
    {
        // reset parsed text
        $this->parsedText = '';

        // create text buffer
        $buffer = &$this->parsedText;

        // stack of buffered tags
        $bufferedTagStack = [];

        // loop through the tags
        $i = -1;
        foreach ($this->tagArray as $i => $tag) {
            // append text to buffer
            $buffer .= $this->textArray[$i];

            if ($tag['closing']) {
                // get buffered opening tag
                $openingTag = \end($bufferedTagStack);

                // closing tag
                if ($openingTag && $openingTag['name'] === $tag['name']) {
                    $hideBuffer = false;
                    // insert buffered content as attribute value
                    foreach ($this->bbcodes[$tag['name']]->getAttributes() as $attribute) {
                        if ($attribute->useText !== 0 && !isset($openingTag['attributes'][$attribute->attributeNo])) {
                            $openingTag['attributes'][$attribute->attributeNo] = $buffer;
                            $hideBuffer = true;
                            break;
                        }
                    }

                    // validate tag attributes again
                    if ($this->isValidTag($openingTag)) {
                        if ($this->bbcodes[$tag['name']]->getProcessor()) {
                            // build tag
                            $parsedTag = $this->bbcodes[$tag['name']]->getProcessor()->getParsedTag(
                                $openingTag,
                                $buffer,
                                $tag,
                                $this
                            );
                        } else {
                            // build tag
                            $parsedTag = $this->buildOpeningTag($openingTag);
                            $closingTag = $this->buildClosingTag($tag);
                            if (!empty($closingTag) && $hideBuffer) {
                                $parsedTag .= $buffer . $closingTag;
                            }
                        }
                    } else {
                        $parsedTag = $openingTag['source'] . $buffer . $tag['source'];
                    }

                    // close current buffer
                    \array_pop($bufferedTagStack);

                    // open previous buffer
                    if (\count($bufferedTagStack) > 0) {
                        $bufferedTag = &$bufferedTagStack[\count($bufferedTagStack) - 1];
                        $buffer = &$bufferedTag['buffer'];
                    } else {
                        $buffer = &$this->parsedText;
                    }

                    // append parsed tag
                    $buffer .= $parsedTag;
                } else {
                    $buffer .= $this->buildClosingTag($tag);
                }
            } else {
                // opening tag
                if ($this->needBuffering($tag)) {
                    // start buffering
                    $tag['buffer'] = '';
                    $bufferedTagStack[] = $tag;
                    $buffer = &$bufferedTagStack[\count($bufferedTagStack) - 1]['buffer'];
                } else {
                    $buffer .= $this->buildOpeningTag($tag);
                }
            }
        }

        if (isset($this->textArray[$i + 1])) {
            $this->parsedText .= $this->textArray[$i + 1];
        }
    }

    /**
     * Builds the tag array from the given text.
     *
     * @return void
     */
    public function buildTagArray(bool $ignoreSourceCodes = true)
    {
        // build tag pattern
        $validTags = '';
        if (!$ignoreSourceCodes) {
            $validTags = \implode('|', \array_keys($this->bbcodes));
        } else {
            foreach ($this->bbcodes as $tag => $bbcode) {
                if ($bbcode->isSourceCode === 0) {
                    // remove source codes
                    if (!empty($validTags)) {
                        $validTags .= '|';
                    }
                    $validTags .= $tag;
                }
            }
        }
        $pattern = '~\[(?:/(?:' . $validTags . ')|(?:' . $validTags . ')
            (?:=
                (?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*)
                (?:,(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*))*
            )?)\]~ix';

        // get bbcode tags
        \preg_match_all($pattern, $this->text, $matches);

        // build tags
        $this->tagArray = \array_map(
            fn(string $match) => $this->buildTag($match),
            $matches[0],
        );

        // get text
        $this->textArray = \preg_split($pattern, $this->text);
    }

    /**
     * Builds a bbcode tag.
     *
     * @return BBCodeTag
     */
    protected function buildTag(string $string)
    {
        $tag = ['name' => '', 'closing' => false, 'source' => $string];

        if (\mb_substr($string, 1, 1) === '/') {
            // closing tag
            $tag['name'] = \mb_strtolower(\mb_substr($string, 2, \mb_strlen($string) - 3));
            $tag['closing'] = true;
        } else {
            // opening tag
            // split tag and attributes
            \preg_match("!^\\[([a-z0-9]+)=?(.*)]$!si", $string, $match);
            $tag['name'] = \mb_strtolower($match[1]);

            // build attributes
            if (!empty($match[2])) {
                $tag['attributes'] = $this->buildTagAttributes($match[2]);
            }
        }

        return $tag;
    }

    /**
     * Builds the attributes of a bbcode tag.
     *
     * @param string $string
     * @return list<string> bbcode attributes
     */
    public function buildTagAttributes(string $string)
    {
        \preg_match_all("~(?:^|,)('[^'\\\\]*(?:\\\\.[^'\\\\]*)*'|[^,]*)~", $string, $matches);

        // remove quotes
        for ($i = 0, $j = \count($matches[1]); $i < $j; $i++) {
            if (\mb_substr($matches[1][$i], 0, 1) === "'" && \mb_substr($matches[1][$i], -1) === "'") {
                $matches[1][$i] = \str_replace("\\'", "'", $matches[1][$i]);
                $matches[1][$i] = \str_replace("\\\\", "\\", $matches[1][$i]);

                $matches[1][$i] = \mb_substr($matches[1][$i], 1, -1);
            }
        }

        return $matches[1];
    }

    /**
     * Removes code bbcode occurrences in given message.
     *
     * @return string
     */
    public function removeCodeTags(string $message)
    {
        if (!empty($this->sourceCodeRegEx)) {
            return \preg_replace("~(\\[(" . $this->sourceCodeRegEx . ")
				(?:=
					(?:\\'[^\\'\\\\]*(?:\\\\.[^\\'\\\\]*)*\\'|[^,\\]]*)
					(?:,(?:\\'[^\\'\\\\]*(?:\\\\.[^\\'\\\\]*)*\\'|[^,\\]]*))*
				)?\\])
				(.*?)
				(?:\\[/\\2\\])~six", '', $message);
        }

        return $message;
    }

    /**
     * Returns the list of bbcodes that represent block elements.
     *
     * @return string[] list of bbcode block elements
     */
    public function getBlockBBCodes(): array
    {
        static $bbcodes;

        if ($bbcodes === null) {
            $bbcodes = [];

            foreach ($this->bbcodes as $name => $bbcode) {
                if ($bbcode->isBlockElement !== 0) {
                    $bbcodes[] = $name;
                }
            }
        }

        return $bbcodes;
    }

    /**
     * Returns the list of bbcodes that represent source code elements.
     *
     * @return list<string> list of bbcode source code elements
     */
    public function getSourceBBCodes(): array
    {
        static $bbcodes;

        if ($bbcodes === null) {
            $bbcodes = [];

            foreach ($this->bbcodes as $name => $bbcode) {
                if ($bbcode->isSourceCode !== 0) {
                    $bbcodes[] = $name;
                }
            }
        }

        return $bbcodes;
    }
}
