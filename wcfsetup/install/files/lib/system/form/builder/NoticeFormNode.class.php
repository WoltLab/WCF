<?php

namespace wcf\system\form\builder;

/**
 * Form node that shows a notice.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
class NoticeFormNode extends LanguageItemFormNode
{
    const AVAILABLE_TYPES = ['info', 'success', 'warning', 'error'];

    protected string $type = 'info';

    /**
     * @inheritDoc
     */
    public function getHtml()
    {
        return '<woltlab-core-notice type="' . $this->getType() . '">' . parent::getHtml() . '</woltlab-core-notice>';
    }

    /**
     * Sets the type of this notice.
     */
    public function type(string $type): static
    {
        if (!\in_array($type, self::AVAILABLE_TYPES)) {
            throw new \BadMethodCallException("Invalid value '{$type}' given.");
        }

        $this->type = $type;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
