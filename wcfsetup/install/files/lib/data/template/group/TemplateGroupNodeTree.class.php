<?php

namespace wcf\data\template\group;

/**
 * Represents a tree of templates group nodes.
 *
 * @author  Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class TemplateGroupNodeTree implements \IteratorAggregate
{
    /**
     * list of ids of template group which will not be included in the node tree
     * @var int[]
     */
    protected array $excludedTemplateGroupIDs = [];

    /**
     * maximum depth considered when building the node tree
     * @var int
     */
    protected int $maxDepth = -1;

    /**
     * id of the template group
     * @var int
     */
    protected int $parentTemplateGroupID = 0;

    /**
     * parent template group node
     * @var TemplateGroupNode
     */
    protected TemplateGroupNode $parentNode;

    /**
     * Creates a new instance of TemplateGroupNodeTree.
     *
     * @param int $parentTemplateGroupID
     * @param int[] $excludedTemplateGroupIDs
     */
    public function __construct(
        int $parentTemplateGroupID = 0,
        array $excludedTemplateGroupIDs = []
    ) {
        $this->parentTemplateGroupID = $parentTemplateGroupID;
        $this->excludedTemplateGroupIDs = $excludedTemplateGroupIDs;
    }

    /**
     * Sets the maximum depth considered when building the node tree, defaults
     * to -1 which equals infinite.
     */
    public function setMaxDepth(int $maxDepth)
    {
        $this->maxDepth = $maxDepth;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): \Traversable
    {
        if (!isset($this->parentNode)) {
            $this->buildTree();
        }

        return new \RecursiveIteratorIterator($this->parentNode, \RecursiveIteratorIterator::SELF_FIRST);
    }

    /**
     * Builds the template group node tree.
     */
    protected function buildTree()
    {
        $this->parentNode = $this->getNode($this->parentTemplateGroupID);
        $this->buildTreeLevel($this->parentNode, $this->maxDepth);
    }

    /**
     * Returns the template group node for the template group with the given id.
     *
     * @param int $templateGroupID
     * @return  TemplateGroupNode
     */
    protected function getNode(int $templateGroupID): TemplateGroupNode
    {
        if (!$templateGroupID) {
            $templateGroup = new TemplateGroup(null, [
                'templateGroupID' => 0,
            ]);
        } else {
            $templateGroup = new TemplateGroup($templateGroupID);
        }

        return new TemplateGroupNode($templateGroup);
    }

    /**
     * Builds a certain level of the tree.
     */
    protected function buildTreeLevel(TemplateGroupNode $parentNode, int $depth = 0)
    {
        if ($this->maxDepth != -1 && $depth < 0) {
            return;
        }

        foreach (TemplateGroup::getParentTemplatesGroups($parentNode->templateGroupID) as $childTemplateGroup) {
            $childNode = $this->getNode($childTemplateGroup->templateGroupID);

            if ($this->isIncluded($childNode)) {
                $parentNode->addChild($childNode);

                // build next level
                $this->buildTreeLevel($childNode, $depth - 1);
            }
        }
    }

    /**
     * Returns true if the given template group node fulfils all relevant conditions
     * to be included in this tree.
     */
    protected function isIncluded(TemplateGroupNode $templateGroupNode): bool
    {
        return !\in_array(
            $templateGroupNode->templateGroupID,
            $this->excludedTemplateGroupIDs
        );
    }
}
