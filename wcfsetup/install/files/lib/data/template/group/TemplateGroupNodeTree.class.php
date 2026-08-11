<?php

namespace wcf\data\template\group;

/**
 * Represents a tree of templates group nodes.
 *
 * @author  Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @implements \IteratorAggregate<int, TemplateGroupNode>
 */
final class TemplateGroupNodeTree implements \IteratorAggregate
{
    /**
     * list of ids of template group which will not be included in the node tree
     * @var int[]
     */
    private array $excludedTemplateGroupIDs = [];

    /**
     * maximum depth considered when building the node tree
     */
    private int $maxDepth = -1;

    /**
     * id of the template group
     */
    private int $parentTemplateGroupID = 0;

    /**
     * parent template group node
     */
    private TemplateGroupNode $parentNode;

    /**
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
    public function setMaxDepth(int $maxDepth): void
    {
        $this->maxDepth = $maxDepth;
    }

    /**
     * @return \RecursiveIteratorIterator<TemplateGroupNode>
     */
    #[\Override]
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
    private function buildTree(): void
    {
        $this->parentNode = $this->getNode($this->parentTemplateGroupID);
        $this->buildTreeLevel($this->parentNode, $this->maxDepth);
    }

    /**
     * Returns the template group node for the template group with the given id.
     */
    private function getNode(int $templateGroupID): TemplateGroupNode
    {
        if ($templateGroupID === 0) {
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
    private function buildTreeLevel(TemplateGroupNode $parentNode, int $depth = 0): void
    {
        if ($this->maxDepth !== -1 && $depth < 0) {
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
    private function isIncluded(TemplateGroupNode $templateGroupNode): bool
    {
        return !\in_array(
            $templateGroupNode->templateGroupID,
            $this->excludedTemplateGroupIDs,
            true
        );
    }
}
