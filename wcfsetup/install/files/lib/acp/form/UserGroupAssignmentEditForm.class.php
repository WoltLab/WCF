<?php

namespace wcf\acp\form;

use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\WCF;
use wcf\util\HtmlString;

/**
 * Shows the form to edit an existing automatic user group assignment.
 *
 * @author  Olaf Braun, Matthias Schmidt
 * @copyright   2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserGroupAssignmentEditForm extends UserGroupAssignmentAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.group.assignment';

    /**
     * @inheritDoc
     */
    public $formAction = 'edit';

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!isset($_REQUEST['id'])) {
            throw new IllegalLinkException();
        }

        $this->formObject = new UserGroupAssignment(\intval($_REQUEST['id']));
        if (!$this->formObject->assignmentID) {
            throw new IllegalLinkException();
        }

        if ($this->formObject->isLegacy) {
            throw new NamedUserException(
                HtmlString::fromSafeHtml(WCF::getLanguage()->getDynamicVariable('wcf.acp.group.assignment.legacyNotice'))
            );
        }
    }
}
