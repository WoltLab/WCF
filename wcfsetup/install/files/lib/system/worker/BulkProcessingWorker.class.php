<?php

namespace wcf\system\worker;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\bulk\processing\IBulkProcessingAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * @author  Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class BulkProcessingWorker extends AbstractWorker
{
    /**
     * @inheritDoc
     */
    protected $limit = 100;
    protected array $bulkProcessingData;
    protected IBulkProcessingAction $action;

    #[\Override]
    public function validate()
    {
        if (!isset($this->parameters['bulkProcessingID'])) {
            throw new SystemException("bulkProcessingID missing");
        }

        $bulkProcessingData = WCF::getSession()->getVar('bulkProcessingData');
        if (!isset($bulkProcessingData[$this->parameters['bulkProcessingID']])) {
            throw new SystemException("bulkProcessingID '" . $this->parameters['bulkProcessingID'] . "' is invalid");
        }

        $this->bulkProcessingData = $bulkProcessingData[$this->parameters['bulkProcessingID']];
        $objectType = ObjectTypeCache::getInstance()->getObjectType($this->bulkProcessingData['objectTypeID']);
        if (!$objectType->validateOptions() || !$objectType->validatePermissions()) {
            throw new PermissionDeniedException();
        }

        $this->action = $objectType->getProcessor();
        if (!$this->action->canRunInWorker()) {
            throw new SystemException("action '" . $this->bulkProcessingData['action'] . "' cannot run in worker");
        }

        $this->action->loadAdditionalParameters($this->bulkProcessingData['additionalParameters']);
    }

    #[\Override]
    public function countObjects()
    {
        $this->count = \count($this->bulkProcessingData['objectIDs']);
    }

    #[\Override]
    public function getProgress()
    {
        $progress = parent::getProgress();

        if ($progress == 100) {
            // clear session
            $bulkProcessingData = WCF::getSession()->getVar('bulkProcessingData');
            unset($bulkProcessingData[$this->parameters['bulkProcessingID']]);
            WCF::getSession()->register('bulkProcessingData', $bulkProcessingData);
        }

        return $progress;
    }

    #[\Override]
    public function execute()
    {
        $objectList = $this->action->getObjectList();
        $objectList->setObjectIDs(
            \array_slice($this->bulkProcessingData['objectIDs'], $this->limit * $this->loopCount, $this->limit)
        );
        $objectList->readObjects();

        $this->action->executeAction($objectList);
    }

    #[\Override]
    public function getProceedURL()
    {
        return $this->bulkProcessingData['form'];
    }
}
