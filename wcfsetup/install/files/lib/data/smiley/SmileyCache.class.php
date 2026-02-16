<?php

namespace wcf\data\smiley;

use wcf\data\category\Category;
use wcf\data\smiley\category\SmileyCategory;
use wcf\system\cache\builder\SmileyCacheBuilder;
use wcf\system\category\CategoryHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the smiley cache.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class SmileyCache extends SingletonFactory
{
    /**
     * @var array<int, array<int, Smiley>>
     */
    private $cachedSmilies = [];

    /**
     * @var array<string, Smiley>
     */
    private $cachedSmileyByCode = [];

    /**
     * @var array<int, SmileyCategory>
     */
    private $cachedCategories = [];

    /**
     * enabled smiley categories with at least one smiley
     * @var SmileyCategory[]
     */
    private $visibleCategories;

    /**
     * @var array<string, Smiley>
     */
    private array $emojis;

    #[\Override]
    protected function init(): void
    {
        // get smiley cache
        $this->cachedSmilies = SmileyCacheBuilder::getInstance()->getData([], 'smilies');
        $this->cachedSmileyByCode = SmileyCacheBuilder::getInstance()->getData([], 'codes');
        $smileyCategories = CategoryHandler::getInstance()->getCategories('com.woltlab.wcf.bbcode.smiley');

        $this->cachedCategories[0] = new SmileyCategory(new Category(null, [
            'categoryID' => null,
            'parentCategoryID' => 0,
            'title' => 'wcf.acp.smiley.categoryID.default',
            'description' => '',
            'showOrder' => -1,
            'isDisabled' => 0,
        ]));

        foreach ($smileyCategories as $key => $smileyCategory) {
            $this->cachedCategories[$key] = new SmileyCategory($smileyCategory);
        }
    }

    /**
     * @return array<int, array<int, Smiley>>
     */
    public function getSmilies(): array
    {
        return $this->cachedSmilies;
    }

    public function getSmileyByCode(string $code): ?Smiley
    {
        return $this->cachedSmileyByCode[$code] ?? null;
    }

    /**
     * Returns all smiley categories.
     *
     * @return array<int, SmileyCategory>
     */
    public function getCategories(): array
    {
        return $this->cachedCategories;
    }

    /**
     * Returns all enabled smiley categories with at least one smiley.
     *
     * @return array<int, SmileyCategory>
     */
    public function getVisibleCategories(): array
    {
        if ($this->visibleCategories === null) {
            $this->visibleCategories = [];

            foreach ($this->cachedCategories as $key => $category) {
                if (!$category->isDisabled) {
                    $category->loadSmilies();

                    if (\count($category)) {
                        $this->visibleCategories[$key] = $category;
                    }
                }
            }
        }

        return $this->visibleCategories;
    }

    /**
     * Returns all the smilies of a category.
     *
     * @return array<int, Smiley>
     */
    public function getCategorySmilies(?int $categoryID = null): array
    {
        return $this->cachedSmilies[$categoryID ?? 0] ?? [];
    }

    /**
     * Assigns the smilies and their categories to the template.
     */
    public function assignVariables(): void
    {
        if (!MODULE_SMILEY) {
            return;
        }

        $smileyCategories = $this->getVisibleCategories();
        $firstCategory = \reset($smileyCategories);
        $defaultSmilies = [];
        if ($firstCategory) {
            $defaultSmilies = $this->getCategorySmilies($firstCategory->categoryID ?: null);
        }

        WCF::getTPL()->assign([
            'defaultSmilies' => $defaultSmilies,
            'smileyCategories' => $smileyCategories,
        ]);
    }

    /**
     * Return all smileys that match `:[a-z][a-z0-9]*+(?:_[a-z0-9]+)*:`.
     *
     * @return array<string, Smiley>
     * @since 6.1
     */
    public function getEmojis(): array
    {
        if (!MODULE_SMILEY) {
            return [];
        }

        if (!isset($this->emojis)) {
            $this->emojis = [];
            foreach ($this->getVisibleCategories() as $category) {
                foreach ($category as $smiley) {
                    foreach ($smiley->smileyCodes as $smileyCode) {
                        if (\preg_match('~^:[a-z][a-z0-9]*+(?:_[a-z0-9]+)*:$~', $smileyCode)) {
                            $this->emojis[$smileyCode] = $smiley;
                        }
                    }
                }
            }
        }

        return $this->emojis;
    }
}
