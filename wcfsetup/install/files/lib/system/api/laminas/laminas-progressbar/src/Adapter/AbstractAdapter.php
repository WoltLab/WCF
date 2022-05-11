<?php

namespace Laminas\ProgressBar\Adapter;

use Laminas\Stdlib\ArrayUtils;
use Traversable;

/**
 * Abstract class for Laminas\ProgressBar Adapters
 */
abstract class AbstractAdapter
{
    /**
     * Option keys to skip when calling setOptions()
     *
     * @var array
     */
    protected $skipOptions = [
        'options',
        'config',
    ];

    /**
     * Create a new adapter
     *
     * $options may be either be an array or a Laminas\Config object which
     * specifies adapter related options.
     *
     * @param  array|Traversable $options
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Set options via an array
     *
     * @param  array $options
     * @return AbstractAdapter
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if (in_array(strtolower($key), $this->skipOptions)) {
                continue;
            }

            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * Notify the adapter about an update
     *
     * @param  float   $current       Current progress value
     * @param  float   $max           Max progress value
     * @param  float   $percent       Current percent value
     * @param  int $timeTaken     Taken time in seconds
     * @param  int $timeRemaining Remaining time in seconds
     * @param  string  $text          Status text
     * @return void
     */
    abstract public function notify($current, $max, $percent, $timeTaken, $timeRemaining, $text);

    /**
     * Called when the progress is explicitly finished
     *
     * @return void
     */
    abstract public function finish();
}
