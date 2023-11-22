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
    protected NoticeFormNodeType $type = NoticeFormNodeType::Info;

    /**
     * @inheritDoc
     */
    public function getHtml()
    {
        return '<woltlab-core-notice type="' . $this->getType()->toString() . '">' . parent::getHtml() . '</woltlab-core-notice>';
    }

    /**
     * Sets the type of this notice.
     */
    public function type(NoticeFormNodeType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): NoticeFormNodeType
    {
        return $this->type;
    }
}
