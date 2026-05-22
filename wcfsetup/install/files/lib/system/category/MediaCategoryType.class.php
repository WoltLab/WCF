<?php

namespace wcf\system\category;

use wcf\acp\form\MediaCategoryAddForm;
use wcf\acp\form\MediaCategoryEditForm;
use wcf\system\WCF;

/**
 * Category implementation for media files.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MediaCategoryType extends AbstractCategoryType
{
    /**
     * @inheritDoc
     */
    protected $langVarPrefix = 'wcf.media.category';

    /**
     * @inheritDoc
     */
    protected $hasDescription = false;

    /**
     * @inheritDoc
     */
    protected $maximumNestingLevel = 2;

    #[\Override]
    public function canAddCategory()
    {
        return WCF::getSession()->hasPermission('admin.content.cms.canManageMedia');
    }

    #[\Override]
    public function canDeleteCategory()
    {
        return WCF::getSession()->hasPermission('admin.content.cms.canManageMedia');
    }

    #[\Override]
    public function canEditCategory()
    {
        return WCF::getSession()->hasPermission('admin.content.cms.canManageMedia');
    }

    #[\Override]
    public function getEditControllerClass(): string
    {
        return MediaCategoryEditForm::class;
    }

    #[\Override]
    public function getAddControllerClass(): string
    {
        return MediaCategoryAddForm::class;
    }
}
