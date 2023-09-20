<?php

namespace wcf\data\label;

use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a label.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $labelID        unique id of the label
 * @property-read   int $groupID        id of the label group the label belongs to
 * @property-read   string $label          label text or name of language item which contains the label text
 * @property-read   string $cssClassName       css class name used when displaying the label
 * @property-read   int $showOrder      position of the label in relation to the other labels in the label group
 */
class Label extends DatabaseObject implements IRouteController
{
    /**
     * Returns the label's textual representation if a label is treated as a
     * string.
     */
    public function __toString(): string
    {
        return $this->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return WCF::getLanguage()->get($this->label);
    }

    /**
     * Returns label CSS class names.
     *
     * @return  string
     */
    public function getClassNames()
    {
        if ($this->cssClassName == 'none') {
            return '';
        }

        return $this->cssClassName;
    }

    /**
     * Returns the HTML representation of the label.
     *
     * @param string $additionalClasses
     * @return      string
     * @since       5.3
     */
    public function render($additionalClasses = ''): string
    {
        $classNames = 'badge label';
        if ($this->getClassNames()) {
            $classNames .= " {$this->getClassNames()}";
        }
        if ($additionalClasses) {
            $classNames .= " {$additionalClasses}";
        }
        return \sprintf(
            '<span class="%s" data-label-id="%d">%s</span>',
            $classNames,
            $this->labelID,
            StringUtil::encodeHTML($this->getTitle()),
        );
    }
}
