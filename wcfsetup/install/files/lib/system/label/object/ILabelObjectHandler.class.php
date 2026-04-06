<?php

namespace wcf\system\label\object;

use wcf\data\label\group\ViewableLabelGroup;
use wcf\data\label\Label;

/**
 * Every label object handler has to implement this interface.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface ILabelObjectHandler
{
    /**
     * Returns a list of label group ids.
     *
     * @param mixed[] $parameters
     * @return int[]
     */
    public function getLabelGroupIDs(array $parameters = []);

    /**
     * Returns a list of label groups.
     *
     * @param mixed[] $parameters
     * @return ViewableLabelGroup[]
     */
    public function getLabelGroups(array $parameters = []);

    /**
     * Returns true, if all given label ids are valid and accessible.
     *
     * @param int[] $labelIDs
     * @return mixed
     */
    public function validateLabelIDs(array $labelIDs, string $optionName = '', bool $legacyReturnValue = true);

    /**
     * Assigns labels to an object.
     *
     * @param int[] $labelIDs
     * @return void
     * @see     \wcf\system\label\LabelHandler::setLabels()
     */
    public function setLabels(array $labelIDs, int $objectID, bool $validatePermissions = true);

    /**
     * Removes all assigned labels.
     *
     * @param int|list<int> $objectID
     * @return void
     * @see \wcf\system\label\LabelHandler::removeLabels()
     */
    public function removeLabels(int|array $objectID, bool $validatePermissions = true);

    /**
     * Returns a list of assigned labels.
     *
     * @param int[] $objectIDs
     * @return Label[][]
     */
    public function getAssignedLabels(array $objectIDs, bool $validatePermissions = true);
}
