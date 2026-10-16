<?php

namespace wcf\system\form\builder\field\media;

use wcf\data\category\CategoryNodeTree;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TDefaultIdFormField;
use wcf\system\WCF;

/**
 * Implementation of a form field to select the category of media files, the
 * value `0` represents no category.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MediaCategoryFormField extends SingleSelectionFormField
{
    use TDefaultIdFormField;

    public function __construct()
    {
        $this->label('wcf.global.category');
        $this->options($this->getCategoryOptions(), true);
        $this->value(0);
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
        $options = [
            [
                'depth' => 0,
                'label' => WCF::getLanguage()->get('wcf.global.noSelection'),
                'value' => 0,
            ],
        ];

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
