<?php

namespace wcf\system\form\builder\container;

/**
 * Represents a container that is a tab of a tab menu and a tab menu itself.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class TabTabMenuFormContainer extends TabMenuFormContainer implements ITabFormContainer
{
    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_tabTabMenuFormContainer';

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->addClasses(['tabMenuContainer', 'tabMenuContent']);
    }
}
