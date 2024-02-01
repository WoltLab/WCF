<?php

namespace wcf\system\form\builder\container;

/**
 * Represents a container that is a tab of a tab menu.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class TabFormContainer extends FormContainer implements ITabFormContainer
{
    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_tabFormContainer';

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->addClass('tabMenuContent');
    }
}
