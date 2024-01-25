<?php

namespace wcf\system\form\builder\container;

/**
 * Represents a container whose children are tabs of a tab menu.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class TabMenuFormContainer extends FormContainer implements ITabMenuFormContainer
{
    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_tabMenuFormContainer';

    /**
     * is `true` if the links in the tab menu have anchors
     * @var bool
     */
    protected $useAnchors = true;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->addClass('section')
            ->addClass('tabMenuContainer');
    }

    /**
     * Sets if the links in the tab menu have anchors and returns this form container.
     *
     * @param bool $useAnchors
     * @return  TabMenuFormContainer        this form container
     */
    public function useAnchors($useAnchors = true)
    {
        $this->useAnchors = $useAnchors;

        return $this;
    }

    /**
     * Returns `true` if the links in the tab menu have anchors and `false` otherwise.
     *
     * By default, the links in the tab menu have anchors.
     *
     * @return  bool
     */
    public function usesAnchors()
    {
        return $this->useAnchors;
    }
}
