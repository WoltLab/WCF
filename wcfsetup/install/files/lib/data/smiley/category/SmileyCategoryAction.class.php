<?php

namespace wcf\data\smiley\category;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\category\Category;
use wcf\data\category\CategoryEditor;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Executes smiley category-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<Category, CategoryEditor>
 */
class SmileyCategoryAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = CategoryEditor::class;

    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['getSmilies'];

    /**
     * active smiley category
     * @var ?SmileyCategory
     */
    public $smileyCategory;

    /**
     * Validates smiley category id.
     *
     * @return void
     * @deprecated 6.2 No longer in use.
     */
    public function validateGetSmilies()
    {
        $this->smileyCategory = new SmileyCategory($this->getSingleObject()->getDecoratedObject());

        if ($this->smileyCategory->isDisabled !== 0) {
            throw new IllegalLinkException();
        }
    }

    /**
     * Returns parsed template for smiley category's smilies.
     *
     * @return array{smileyCategoryID: int, template: string}
     * @deprecated 6.2 No longer in use.
     */
    public function getSmilies()
    {
        $this->smileyCategory->loadSmilies();

        return [
            'smileyCategoryID' => $this->smileyCategory->categoryID,
            'template' => WCF::getTPL()->render('wcf', 'shared_messageFormSmilies', [
                'smilies' => $this->smileyCategory,
            ]),
        ];
    }
}
