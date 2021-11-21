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
 * @package WoltLabSuite\Core\System\Label\Object
 */
interface ILabelObjectHandler
{
    /**
     * Returns a list of label group ids.
     *
     * @param array $parameters
     * @return  int[]
     */
    public function getLabelGroupIDs(array $parameters = []);

    /**
     * Returns a list of label groups.
     *
     * @param array $parameters
     * @return  ViewableLabelGroup[]
     */
    public function getLabelGroups(array $parameters = []);

    /**
     * Returns true, if all given label ids are valid and accessible.
     *
     * @param int[] $labelIDs
     * @param string $optionName
     * @param bool $legacyReturnValue
     * @return  mixed
     */
    public function validateLabelIDs(array $labelIDs, $optionName = '', $legacyReturnValue = true);

    /**
     * Assigns labels to an object.
     *
     * @param int[] $labelIDs
     * @param int $objectID
     * @param bool $validatePermissions
     * @see     \wcf\system\label\LabelHandler::setLabels()
     */
    public function setLabels(array $labelIDs, $objectID, $validatePermissions = true);

    /**
     * Removes all assigned labels.
     *
     * @param int $objectID
     * @param bool $validatePermissions
     * @see     \wcf\system\label\LabelHandler::removeLabels()
     */
    public function removeLabels($objectID, $validatePermissions = true);

    /**
     * Returns a list of assigned labels.
     *
     * @param int[] $objectIDs
     * @param bool $validatePermissions
     * @return  Label[][]
     */
    public function getAssignedLabels(array $objectIDs, $validatePermissions = true);
}
