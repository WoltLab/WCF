<?php

namespace wcf\acp\action;

use wcf\action\AbstractSecureAction;
use wcf\action\AJAXInvokeAction;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\system\worker\IWorker;

/**
 * Handles worker actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class WorkerProxyAction extends AJAXInvokeAction
{
    /**
     * @inheritDoc
     */
    public const DO_NOT_LOG = true;

    /**
     * loop counter
     * @var int
     */
    protected $loopCount = -1;

    /**
     * parameters for worker action
     * @var array<string, mixed>
     */
    protected $parameters = [];

    /**
     * worker object
     * @var IWorker
     */
    protected $worker;

    /**
     * @var string[]
     */
    public static $allowInvoke = [];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        AbstractSecureAction::readParameters();

        if (isset($_POST['className'])) {
            $this->className = $_POST['className'];
        }
        if (isset($_POST['loopCount'])) {
            $this->loopCount = \intval($_POST['loopCount']);
        }
        if (isset($_POST['parameters']) && \is_array($_POST['parameters'])) {
            $this->parameters = $_POST['parameters'];
        }

        $this->validate();
    }

    /**
     * Validates class name.
     *
     * @return void
     */
    protected function validate()
    {
        if (empty($this->className)) {
            throw new SystemException("class name cannot be empty.");
        }

        if (!\is_subclass_of($this->className, IWorker::class)) {
            throw new ImplementationException($this->className, IWorker::class);
        }
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        AbstractSecureAction::execute();

        if ($this->loopCount == -1) {
            $this->sendResponse();
        }

        // init worker
        $this->worker = new $this->className($this->parameters);
        $this->worker->setLoopCount($this->loopCount);

        // validate worker parameters
        $this->worker->validate();

        // calculate progress, triggers countObjects()
        $progress = $this->worker->getProgress();

        // execute worker
        $this->worker->execute();

        $this->worker->finalize();

        // send current state
        $this->sendResponse($progress, $this->worker->getParameters(), $this->worker->getProceedURL());
    }

    /**
     * Sends a JSON-encoded response.
     *
     * @param int $progress
     * @param ?array<string, mixed> $parameters
     * @param string $proceedURL
     * @return void
     */
    protected function sendResponse($progress = 0, ?array $parameters = null, $proceedURL = '')
    {
        if ($parameters === null) {
            $parameters = $this->parameters;
        }

        // build return values
        $returnValues = [
            'className' => $this->className,
            'loopCount' => $this->loopCount + 1,
            'parameters' => $parameters,
            'proceedURL' => $proceedURL,
            'progress' => $progress,
        ];

        // include template on startup
        if ($this->loopCount == -1) {
            $returnValues['template'] = WCF::getTPL()->render('wcf', 'shared_worker', []);
        }

        // send JSON-encoded response
        \header('Content-type: application/json; charset=UTF-8');
        echo \json_encode($returnValues, \JSON_THROW_ON_ERROR);

        exit;
    }
}
