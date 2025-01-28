<?php

namespace wcf\system\form\builder;

use wcf\system\gridView\AbstractGridView;

/**
 * Form node that shows the contents of a grid view.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class GridViewFormNode implements IFormChildNode
{
    use TFormChildNode;
    use TFormNode;

    protected AbstractGridView $gridView;

    /**
     * Returns the grid view object.
     *
     * @throws  \BadMethodCallException     if the grid view object has not been set yet
     */
    public function getGridView(): AbstractGridView
    {
        if (!isset($this->gridView)) {
            throw new \BadMethodCallException(
                "Grid view object has not been set yet for node '{$this->getId()}'."
            );
        }

        return $this->gridView;
    }

    /**
     * Sets the grid view object that contains the contents of the form node and returns this form node.
     */
    public function gridView(AbstractGridView $gridView): static
    {
        $this->gridView = $gridView;

        return $this;
    }

    #[\Override]
    public function getHtml()
    {
        return $this->getGridView()->render();
    }

    #[\Override]
    public function validate()
    {
        // does nothing
    }
}
