<?php

namespace Laminas\ProgressBar\Adapter;

use Laminas\ProgressBar\Adapter\Exception\InvalidArgumentException;
use Laminas\Stdlib\ErrorHandler;
use Laminas\Stdlib\StringUtils;
use Traversable;
use ValueError;

use function array_diff;
use function ceil;
use function count;
use function defined;
use function fclose;
use function floor;
use function fopen;
use function fwrite;
use function implode;
use function in_array;
use function is_int;
use function min;
use function preg_match;
use function round;
use function shell_exec;
use function sprintf;
use function str_pad;
use function str_repeat;
use function strlen;
use function substr;

use const E_DEPRECATED;
use const PHP_EOL;
use const PHP_OS;
use const STDOUT;
use const STR_PAD_LEFT;
use const STR_PAD_RIGHT;

/**
 * Laminas\ProgressBar\Adapter\Console offers a text-based progressbar for console
 * applications
 */
class Console extends AbstractAdapter
{
    /**
     * Percentage value of the progress
     */
    public const ELEMENT_PERCENT = 'ELEMENT_PERCENT';

    /**
     * Visual value of the progress
     */
    public const ELEMENT_BAR = 'ELEMENT_BAR';

    /**
     * ETA of the progress
     */
    public const ELEMENT_ETA = 'ELEMENT_ETA';

    /**
     * Text part of the progress
     */
    public const ELEMENT_TEXT = 'ELEMENT_TEXT';

    /**
     * Finish action: End of Line
     */
    public const FINISH_ACTION_EOL = 'FINISH_ACTION_EOL';

    /**
     * Finish action: Clear Line
     */
    public const FINISH_ACTION_CLEAR_LINE = 'FINISH_ACTION_CLEAR_LINE';

    /**
     * Finish action: None
     */
    public const FINISH_ACTION_NONE = 'FINISH_ACTION_NONE';

    /**
     * Width of the progressbar
     *
     * @var int
     */
    protected $width;

    /**
     * Elements to display
     *
     * @var array
     */
    protected $elements = [
        self::ELEMENT_PERCENT,
        self::ELEMENT_BAR,
        self::ELEMENT_ETA,
    ];

    /**
     * Which action to do at finish call
     *
     * @var string
     */
    protected $finishAction = self::FINISH_ACTION_EOL;

    /**
     * Width of the bar element
     *
     * @var int
     */
    protected $barWidth;

    /**
     * Left character(s) within the bar
     *
     * @var string
     */
    protected $barLeftChar = '#';

    /**
     * Indicator character(s) within the bar
     *
     * @var string
     */
    protected $barIndicatorChar = '';

    /**
     * Right character(s) within the bar
     *
     * @var string
     */
    protected $barRightChar = '-';

    /**
     * Output-stream, when STDOUT is not defined (e.g. in CGI) or set manually
     *
     * @var resource
     */
    protected $outputStream;

    /**
     * Width of the text element
     *
     * @var string
     */
    protected $textWidth = 20;

    /**
     * Whether the output started yet or not
     *
     * @var bool
     */
    protected $outputStarted = false;

    /**
     * Charset of text element
     *
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * Defined by Laminas\ProgressBar adapter
     *
     * @param array|Traversable $options
     */
    public function __construct($options = null)
    {
        // Call parent constructor with options
        parent::__construct($options);

        // Check if a width was set, else use auto width
        if ($this->width === null) {
            $this->setWidth();
        }
    }

    /**
     * Close local stdout, when open
     */
    public function __destruct()
    {
        if ($this->outputStream !== null) {
            fclose($this->outputStream);
        }
    }

    /**
     * Set a different output-stream
     *
     * @param  string $resource
     * @throws RuntimeException
     */
    public function setOutputStream($resource)
    {
        $fileOpenError = null;
        ErrorHandler::start(E_DEPRECATED);
        try {
            $stream = fopen($resource, 'w');
        } catch (ValueError $fileOpenError) {
            $stream = false;
        } finally {
            $error = ErrorHandler::stop();
        }

        if ($stream === false) {
            $previous = $fileOpenError ?: $error;
            throw new Exception\RuntimeException('Unable to open stream', 0, $previous);
        }

        if ($this->outputStream !== null) {
            fclose($this->outputStream);
        }

        $this->outputStream = $stream;
    }

    /**
     * Get the current output stream
     *
     * @return resource
     */
    public function getOutputStream()
    {
        if ($this->outputStream === null) {
            if (! defined('STDOUT')) {
                $this->outputStream = fopen('php://stdout', 'w');
            } else {
                return STDOUT;
            }
        }

        return $this->outputStream;
    }

    /**
     * Set the width of the progressbar
     *
     * @param  int $width
     * @return Console
     */
    public function setWidth($width = null)
    {
        if ($width === null || ! is_int($width)) {
            if (substr(PHP_OS, 0, 3) === 'WIN') {
                // We have to default to 79 on windows, because the windows
                // terminal always has a fixed width of 80 characters and the
                // cursor is counted to the line, else windows would line break
                // after every update.
                $this->width = 79;
            } else {
                // Set the default width of 80
                $this->width = 80;

                // Try to determine the width through stty
                ErrorHandler::start();
                if (preg_match('#\d+ (\d+)#', shell_exec('stty size'), $match) === 1) {
                    $this->width = (int) $match[1];
                } elseif (preg_match('#columns = (\d+);#', shell_exec('stty'), $match) === 1) {
                    $this->width = (int) $match[1];
                }
                ErrorHandler::stop();
            }
        } else {
            $this->width = (int) $width;
        }

        $this->_calculateBarWidth();

        return $this;
    }

    /**
     * Set the elements to display with the progressbar
     *
     * @param  array $elements
     * @throws InvalidArgumentException When an invalid element is found in the array.
     * @return Console
     */
    public function setElements(array $elements)
    {
        $allowedElements = [
            self::ELEMENT_PERCENT,
            self::ELEMENT_BAR,
            self::ELEMENT_ETA,
            self::ELEMENT_TEXT,
        ];

        if (count(array_diff($elements, $allowedElements)) > 0) {
            throw new InvalidArgumentException('Invalid element found in $elements array');
        }

        $this->elements = $elements;

        $this->_calculateBarWidth();

        return $this;
    }

    /**
     * Set the left-hand character for the bar
     *
     * @param  string $char
     * @throws InvalidArgumentException When character is empty.
     * @return Console
     */
    public function setBarLeftChar($char)
    {
        if (empty($char)) {
            throw new InvalidArgumentException('Character may not be empty');
        }

        $this->barLeftChar = (string) $char;

        return $this;
    }

    /**
     * Set the right-hand character for the bar
     *
     * @param  string $char
     * @throws InvalidArgumentException When character is empty.
     * @return Console
     */
    public function setBarRightChar($char)
    {
        if (empty($char)) {
            throw new InvalidArgumentException('Character may not be empty');
        }

        $this->barRightChar = (string) $char;

        return $this;
    }

    /**
     * Set the indicator character for the bar
     *
     * @param  string $char
     * @return Console
     */
    public function setBarIndicatorChar($char)
    {
        $this->barIndicatorChar = (string) $char;

        return $this;
    }

    /**
     * Set the width of the text element
     *
     * @param  int $width
     * @return Console
     */
    public function setTextWidth($width)
    {
        $this->textWidth = (int) $width;

        $this->_calculateBarWidth();

        return $this;
    }

    /**
     * Set the charset of the text element
     *
     * @param string $charset
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * Set the finish action
     *
     * @param  string $action
     * @throws InvalidArgumentException When an invalid action is specified.
     * @return Console
     */
    public function setFinishAction($action)
    {
        $allowedActions = [
            self::FINISH_ACTION_CLEAR_LINE,
            self::FINISH_ACTION_EOL,
            self::FINISH_ACTION_NONE,
        ];

        if (! in_array($action, $allowedActions)) {
            throw new InvalidArgumentException('Invalid finish action specified');
        }

        $this->finishAction = $action;

        return $this;
    }

    /**
     * Defined by Laminas\ProgressBar\Adapter\AbstractAdapter
     *
     * @param  float   $current       Current progress value
     * @param  float   $max           Max progress value
     * @param  float   $percent       Current percent value
     * @param  int $timeTaken     Taken time in seconds
     * @param  int|null $timeRemaining Remaining time in seconds
     * @param  string  $text          Status text
     * @return void
     */
    public function notify($current, $max, $percent, $timeTaken, $timeRemaining, $text)
    {
        // See if we must clear the line
        if ($this->outputStarted) {
            $data = str_repeat("\x08", $this->width);
        } else {
            $data                = '';
            $this->outputStarted = true;
        }

        // Build all elements
        $renderedElements = [];

        foreach ($this->elements as $element) {
            switch ($element) {
                case self::ELEMENT_BAR:
                    $visualWidth = $this->barWidth - 2;
                    $bar         = '[';

                    $indicatorWidth = strlen($this->barIndicatorChar);

                    $doneWidth = min($visualWidth - $indicatorWidth, round($visualWidth * $percent));
                    if ($doneWidth > 0) {
                        $bar .= substr(
                            str_repeat($this->barLeftChar, ceil($doneWidth / strlen($this->barLeftChar))),
                            0,
                            $doneWidth
                        );
                    }

                    $bar .= $this->barIndicatorChar;

                    $leftWidth = $visualWidth - $doneWidth - $indicatorWidth;
                    if ($leftWidth > 0) {
                        $bar .= substr(
                            str_repeat($this->barRightChar, ceil($leftWidth / strlen($this->barRightChar))),
                            0,
                            $leftWidth
                        );
                    }

                    $bar .= ']';

                    $renderedElements[] = $bar;
                    break;

                case self::ELEMENT_PERCENT:
                    $renderedElements[] = str_pad(round($percent * 100), 3, ' ', STR_PAD_LEFT) . '%';
                    break;

                case self::ELEMENT_ETA:
                    // In the first 5 seconds we don't get accurate results,
                    // this skipping technique is found in many progressbar
                    // implementations.
                    if ($timeTaken < 5) {
                        $renderedElements[] = str_repeat(' ', 12);
                        break;
                    }

                    if ($timeRemaining === null || $timeRemaining > 86400) {
                        $etaFormatted = '??:??:??';
                    } else {
                        $hours   = floor($timeRemaining / 3600);
                        $minutes = floor(($timeRemaining % 3600) / 60);
                        $seconds = $timeRemaining % 3600 % 60;

                        $etaFormatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                    }

                    $renderedElements[] = 'ETA ' . $etaFormatted;
                    break;

                case self::ELEMENT_TEXT:
                    $wrapper            = StringUtils::getWrapper($this->charset);
                    $renderedElements[] = $wrapper->strPad(
                        $wrapper->substr($text, 0, $this->textWidth),
                        $this->textWidth,
                        ' ',
                        STR_PAD_RIGHT
                    );
                    break;
            }
        }

        $data .= implode(' ', $renderedElements);

        // Output line data
        $this->_outputData($data);
    }

    /**
     * Defined by Laminas\ProgressBar\Adapter\AbstractAdapter
     *
     * @return void
     */
    public function finish()
    {
        switch ($this->finishAction) {
            case self::FINISH_ACTION_EOL:
                $this->_outputData(PHP_EOL);
                break;

            case self::FINISH_ACTION_CLEAR_LINE:
                if ($this->outputStarted) {
                    $data = str_repeat("\x08", $this->width)
                          . str_repeat(' ', $this->width)
                          . str_repeat("\x08", $this->width);

                    $this->_outputData($data);
                }
                break;

            case self::FINISH_ACTION_NONE:
                break;
        }
    }

    /**
     * Calculate the bar width when other elements changed
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _calculateBarWidth()
    {
        // @codingStandardsIgnoreEnd
        if (in_array(self::ELEMENT_BAR, $this->elements)) {
            $barWidth = $this->width;

            if (in_array(self::ELEMENT_PERCENT, $this->elements)) {
                $barWidth -= 4;
            }

            if (in_array(self::ELEMENT_ETA, $this->elements)) {
                $barWidth -= 12;
            }

            if (in_array(self::ELEMENT_TEXT, $this->elements)) {
                $barWidth -= $this->textWidth;
            }

            $this->barWidth = $barWidth - (count($this->elements) - 1);
        }
    }

    /**
     * Outputs given data to STDOUT.
     *
     * This split-off is required for unit-testing.
     *
     * @param  string $data
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _outputData($data)
    {
        // @codingStandardsIgnoreEnd
        fwrite($this->getOutputStream(), $data);
    }
}
