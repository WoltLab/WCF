<?php

namespace wcf\system\form\builder\field\media;

use wcf\data\category\CategoryNodeTree;
use wcf\system\form\builder\field\AbstractSingleSelectFormField;
use wcf\system\form\builder\field\TDefaultIdFormField;

/**
 * Implementation of a form field to select the category of media files, the
 * value `null` represents no category.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MediaCategoryFormField extends AbstractSingleSelectFormField
{
    use TDefaultIdFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_selectFormField';

    public function __construct()
    {
        $this->label('wcf.global.category');
        $this->options($this->getCategoryOptions(), true);
    }

    /**
     * Returns the selected category id or `null` if no category is selected.
     */
    public function getCategoryID(): ?int
    {
        return (int)$this->getSaveValue() ?: null;
    }

    /**
     * @return list<array{depth: int, label: string, value: int}>
     */
    private function getCategoryOptions(): array
    {
        $options = [];

        foreach (new CategoryNodeTree('com.woltlab.wcf.media.category') as $category) {
            $options[] = [
                'depth' => $category->getDepth() - 1,
                'label' => $category->getTitle(),
                'value' => $category->categoryID,
            ];
        }

        return $options;
    }

    #[\Override]
    protected static function getDefaultId(): string
    {
        return 'categoryID';
    }
}
