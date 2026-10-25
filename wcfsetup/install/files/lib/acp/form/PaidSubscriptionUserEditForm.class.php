<?php

namespace wcf\acp\form;

use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;

/**
 * Shows the user subscription edit form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PaidSubscriptionUserEditForm extends PaidSubscriptionUserAddForm
{
    /**
     * @inheritDoc
     */
    public string $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->formObject = Helper::fetchObjectFromQueryParameter(PaidSubscriptionUser::class);
        if ($this->formObject->endDate === 0 || $this->formObject->isActive === 0) {
            throw new IllegalLinkException();
        }

        $this->subscription = $this->formObject->getSubscription();
    }
}
