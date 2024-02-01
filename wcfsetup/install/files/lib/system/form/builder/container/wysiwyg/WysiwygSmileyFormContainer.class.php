<?php

namespace wcf\system\form\builder\container\wysiwyg;

use wcf\data\smiley\SmileyCache;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\TabFormContainer;
use wcf\system\form\builder\container\TabTabMenuFormContainer;
use wcf\system\form\builder\TWysiwygFormNode;
use wcf\system\form\builder\wysiwyg\WysiwygSmileyFormNode;
use wcf\util\StringUtil;

/**
 * Represents the tab for the smiley-related fields below a WYSIWYG editor.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class WysiwygSmileyFormContainer extends TabTabMenuFormContainer
{
    use TWysiwygFormNode;

    /**
     * name of container template
     * @var string
     */
    protected $templateName = 'shared_wysiwygSmileyFormContainer';

    /**
     * Creates a new instance of `WysiwygSmileyFormContainer`.
     */
    public function __construct()
    {
        $this->attribute('data-preselect', 'true')
            ->attribute('data-collapsible', 'false');
    }

    /**
     * @inheritDoc
     */
    public function populate()
    {
        parent::populate();

        $smileyCategories = \array_values(SmileyCache::getInstance()->getVisibleCategories());
        foreach ($smileyCategories as $index => $smileyCategory) {
            // All smilies not in the first category are loaded via JavaScript.
            $smilies = [];
            if (!$index) {
                $smilies = SmileyCache::getInstance()->getCategorySmilies($smileyCategory->categoryID ?: null);
            }

            $this->appendChild(
                TabFormContainer::create($this->getId() . '_smileyCategoryTab' . $smileyCategory->categoryID)
                    ->label(StringUtil::encodeHTML($smileyCategory->getTitle()))
                    ->removeClass('tabMenuContent')
                    ->addClass('messageTabMenuContent')
                    ->appendChild(
                        FormContainer::create(
                            $this->getId() . '_smileyCategoryContainer' . $smileyCategory->categoryID
                        )
                            ->removeClass('section')
                            ->appendChild(
                                WysiwygSmileyFormNode::create(
                                    $this->getId() . '_smileyCategory' . $smileyCategory->categoryID
                                )
                                    ->smilies($smilies)
                            )
                    )
            );
        }

        if (\count($this->children()) > 1) {
            $this->addClass('messageTabMenu');
        }
    }
}
