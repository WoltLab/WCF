<?php

namespace wcf\system\worker;

/**
 * Abstract implementation of a worker.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Worker
 */
abstract class AbstractWorker implements IWorker
{
    /**
     * count of total actions (limited by $limit per loop)
     * @var int
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
     * @var array
     */
    protected $parameters = [];

    /**
     * @inheritDoc
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @inheritDoc
     */
    public function setLoopCount($loopCount)
    {
        $this->loopCount = $loopCount;
    }

    /**
     * Counts objects applicable for worker action.
     */
    abstract protected function countObjects();

    /**
     * @inheritDoc
     */
    public function getProgress()
    {
        $this->countObjects();

        if (!$this->count) {
            return 100;
        }

        $progress = (($this->limit * ($this->loopCount + 1)) / $this->count) * 100;
        if ($progress > 100) {
            $progress = 100;
        }

        return \floor($progress);
    }

    /**
     * @inheritDoc
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @inheritDoc
     */
    public function finalize()
    {
        // does nothing
    }
}
