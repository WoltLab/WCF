<?php

namespace wcf\system\worker;

/**
 * Every worker has to implement this interface.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IWorker
{
    /**
     * Creates a new worker object with additional parameters
     *
     * @param mixed[] $parameters
     */
    public function __construct(array $parameters);

    /**
     * Sets current loop count.
     *
     * @return void
     */
    public function setLoopCount(int $loopCount);

    /**
     * Returns current process of the worker, an int between 0 and 100.
     * If the progress hits 100, the worker will terminate.
     *
     * @return  int
     */
    public function getProgress();

    /**
     * Executes worker action.
     *
     * @return void
     */
    public function execute();

    /**
     * Returns parameters previously given within __construct().
     *
     * @return  mixed[]
     */
    public function getParameters();

    /**
     * Validates parameters.
     *
     * @return void
     */
    public function validate();

    /**
     * Returns URL for redirect after worker finished.
     *
     * @return  string
     */
    public function getProceedURL();

    /**
     * Executes actions after worker has been executed.
     *
     * @return void
     */
    public function finalize();
}
