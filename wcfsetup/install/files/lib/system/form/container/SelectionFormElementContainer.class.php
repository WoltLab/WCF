<?php

namespace wcf\system\form\container;

use wcf\util\StringUtil;

/**
 * Basic implementation for form selection element containers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class SelectionFormElementContainer extends AbstractFormElementContainer
{
    /**
     * container name
     * @var string
     */
    protected $name = '';

    /**
     * Sets container name.
     *
     * @return void
     */
    public function setName(string $name)
    {
        $this->name = StringUtil::trim($name);
    }

    /**
     * Returns container name
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }
}
