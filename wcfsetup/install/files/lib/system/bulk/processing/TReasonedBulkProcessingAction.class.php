<?php

namespace wcf\system\bulk\processing;

use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Trait for bulk processing actions allowing to enter a reason for executing the action.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
trait TReasonedBulkProcessingAction
{
    /**
     * reason
     * @var string
     */
    protected $reason = '';

    /**
     * @inheritDoc
     */
    public function getHTML()
    {
        return WCF::getTPL()->fetch('reasonedBulkProcessingAction', 'wcf', [
            'reason' => $this->reason,
            'reasonFieldName' => $this->getReasonFieldName(),
        ]);
    }

    /**
     * Returns the name of the reason field.
     *
     * @return  string
     */
    abstract protected function getReasonFieldName();

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        if (isset($_POST[$this->getReasonFieldName()])) {
            $this->reason = StringUtil::trim($_POST[$this->getReasonFieldName()]);
        }
    }

    /**
     * @inheritDoc
     */
    public function reset()
    {
        $this->reason = '';
    }

    #[\Override]
    public function getAdditionalParameters(): array
    {
        return [
            'reason' => $this->reason,
        ];
    }

    #[\Override]
    public function loadAdditionalParameters(array $data): void
    {
        $this->reason = $data['reason'] ?? '';
    }
}
