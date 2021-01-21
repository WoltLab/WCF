<?php

namespace wcf\acp\form;

use wcf\data\reaction\type\ReactionType;
use wcf\system\exception\IllegalLinkException;

/**
 * Represents the reaction type add form.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Form
 * @since   5.2
 */
class ReactionTypeEditForm extends ReactionTypeAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.reactionType.list';

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

        if (isset($_REQUEST['id'])) {
            $this->formObject = new ReactionType($_REQUEST['id']);
            if (!$this->formObject->reactionTypeID) {
                throw new IllegalLinkException();
            }
        } else {
            throw new IllegalLinkException();
        }
    }
}
