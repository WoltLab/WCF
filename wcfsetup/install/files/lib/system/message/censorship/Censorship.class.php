<?php

namespace wcf\system\message\censorship;

use wcf\system\SingletonFactory;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Finds censored words.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class Censorship extends SingletonFactory
{
    /**
     * censored words
     * @var string[]
     */
    protected $censoredWords = [];

    /**
     * word delimiters
     * @var string
     */
    protected $delimiters = '[\s\x21-\x29\x2B-\x2F\x3A-\x3F\x5B-\x60\x7B-\x7D]';

    /**
     * list of words
     * @var string[]
     */
    protected $words = [];

    /**
     * @inheritDoc
     */
    protected function init()
    {
        // get words which should be censored
        $censoredWords = ArrayUtil::trim(\explode(
            "\n",
            StringUtil::unifyNewlines(\mb_strtolower(CENSORED_WORDS))
        ));

        // format censored words
        foreach ($censoredWords as $censoredWord) {
            $displayedCensoredWord = \str_replace(['~', '*'], '', $censoredWord);

            // check if censored word contains at least one delimiter
            if (\preg_match('!' . $this->delimiters . '+!', $displayedCensoredWord)) {
                // remove delimiters
                $censoredWord = \preg_replace('!' . $this->delimiters . '!', '', $censoredWord);

                // enforce partial matching
                $censoredWord = '~' . $censoredWord;
            }

            $this->censoredWords[$displayedCensoredWord] = $censoredWord;
        }
    }

    /**
     * Returns censored words from a text.
     *
     * @param string $text
     * @return  mixed       $matches / false
     */
    public function test($text)
    {
        if (empty($this->censoredWords)) {
            return false;
        }

        // reset values
        $matches = $this->words = [];

        // string to lower case
        $text = \mb_strtolower($text);

        // ignore bbcode tags
        $text = \preg_replace('~\[/?[a-z]+[^\]]*\]~i', '', $text);

        // split the text in single words
        $this->words = \preg_split("!" . $this->delimiters . "+!", $text, -1, \PREG_SPLIT_NO_EMPTY);

        // check each word if it's censored.
        for ($i = 0, $count = \count($this->words); $i < $count; $i++) {
            $word = $this->words[$i];
            foreach ($this->censoredWords as $displayedCensoredWord => $censoredWord) {
                // check for direct matches ("badword" == "badword")
                if ($censoredWord == $word) {
                    // store censored word
                    if (isset($matches[$word])) {
                        $matches[$word]++;
                    } else {
                        $matches[$word] = 1;
                    }

                    continue 2;
                } // check for asterisk matches ("*badword*" == "FooBadwordBar")
                elseif (\str_contains($censoredWord, '*')) {
                    $censoredWord = \str_replace('\*', '.*', \preg_quote($censoredWord, '!'));
                    if (\preg_match('!^' . $censoredWord . '$!', $word)) {
                        // store censored word
                        if (isset($matches[$word])) {
                            $matches[$word]++;
                        } else {
                            $matches[$word] = 1;
                        }

                        continue 2;
                    }
                } // check for partial matches ("~badword~" == "bad-word")
                elseif (\str_contains($censoredWord, '~')) {
                    $censoredWord = \str_replace('~', '', $censoredWord);
                    if (($position = \mb_strpos($censoredWord, $word)) !== false) {
                        if ($position > 0) {
                            // look behind
                            if (!$this->lookBehind($i - 1, \mb_substr($censoredWord, 0, $position))) {
                                continue;
                            }
                        }

                        if ($position + \mb_strlen($word) < \mb_strlen($censoredWord)) {
                            // look ahead
                            if (
                                $newIndex = $this->lookAhead(
                                    $i + 1,
                                    \mb_substr($censoredWord, $position + \mb_strlen($word))
                                )
                            ) {
                                $i = $newIndex;
                            } else {
                                continue;
                            }
                        }

                        // store censored word
                        if (isset($matches[$displayedCensoredWord])) {
                            $matches[$displayedCensoredWord]++;
                        } else {
                            $matches[$displayedCensoredWord] = 1;
                        }

                        continue 2;
                    }
                }
            }
        }

        // at least one censored word was found
        if (\count($matches) > 0) {
            return $matches;
        } // text is clean
        else {
            return false;
        }
    }

    /**
     * Looks behind in the word list.
     *
     * @param int $index
     * @param string $search
     * @return  bool
     */
    protected function lookBehind($index, $search)
    {
        if (isset($this->words[$index])) {
            if (
                \mb_strpos(
                    $this->words[$index],
                    $search
                ) === (\mb_strlen($this->words[$index]) - \mb_strlen($search))
            ) {
                return true;
            } elseif (
                \mb_strpos(
                    $search,
                    $this->words[$index]
                ) === (\mb_strlen($search) - \mb_strlen($this->words[$index]))
            ) {
                return $this->lookBehind(
                    $index - 1,
                    \mb_substr($search, 0, \mb_strlen($search) - \mb_strlen($this->words[$index]))
                );
            }
        }

        return false;
    }

    /**
     * Looks ahead in the word list.
     *
     * @param int $index
     * @param string $search
     * @return  mixed
     */
    protected function lookAhead($index, $search)
    {
        if (isset($this->words[$index])) {
            if (\str_starts_with($this->words[$index], $search)) {
                return $index;
            } elseif (\str_starts_with($search, $this->words[$index])) {
                return $this->lookAhead($index + 1, \mb_substr($search, \mb_strlen($this->words[$index])));
            }
        }

        return false;
    }
}
