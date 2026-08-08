<?php

namespace wcf\system\worker;

/**
 * Abstract implementation of a worker.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractWorker implements IWorker
{
    /**
     * count of total actions (limited by $limit per loop)
     * @var ?int
     */
    protected $count;

    /**
     * limit of actions per loop
     * @var int
     */
    protected $limit = 0;

    /**
     * current loop count
     * @var int
     */
    protected $loopCount = 0;

    /**
     * list of additional parameters
     * @var mixed[]
     */
    protected $parameters = [];

    #[\Override]
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    #[\Override]
    public function setLoopCount(int $loopCount)
    {
        $this->loopCount = $loopCount;
    }

    /**
     * Counts objects applicable for worker action.
     *
     * @return void
     */
    abstract protected function countObjects();

    #[\Override]
    public function getProgress()
    {
        $this->countObjects();

        if ($this->count === null || $this->count === 0) {
            return 100;
        }

        $progress = (($this->limit * ($this->loopCount + 1)) / $this->count) * 100;
        if ($progress > 100) {
            $progress = 100;
        }

        return (int)\floor($progress);
    }

    #[\Override]
    public function getParameters()
    {
        return $this->parameters;
    }

    #[\Override]
    public function finalize()
    {
        // does nothing
    }
}
