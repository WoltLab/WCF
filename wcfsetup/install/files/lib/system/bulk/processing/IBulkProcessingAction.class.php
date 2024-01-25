<?php

namespace wcf\system\bulk\processing;

use wcf\data\DatabaseObjectList;

/**
 * Every bulk processing action has to implement this interface.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
interface IBulkProcessingAction
{
    /**
     * Executes the bulk processing action on all objects in the given object
     * list.
     *
     * @param DatabaseObjectList $objectList
     * @throws  \InvalidArgumentException   if given object list cannot be handled by the action
     */
    public function executeAction(DatabaseObjectList $objectList);

    /**
     * Returns the output for setting additional action parameters.
     *
     * @return  string
     */
    public function getHTML();

    /**
     * Returns an object list which will be populated with conditions to read
     * the processed objects.
     *
     * @return  DatabaseObjectList
     */
    public function getObjectList();

    /**
     * Returns true if the action is available for the active user.
     *
     * @return  bool
     */
    public function isAvailable();

    /**
     * Reads additional parameters to execute the action.
     */
    public function readFormParameters();

    /**
     * Resets the internally stored additional action parameters.
     */
    public function reset();

    /**
     * Validates the additional action parameters.
     */
    public function validate();

    /**
     * Returns true if the action can be executed in a worker.
     *
     * @return  bool
     * @since 6.1
     */
    public function canRunInWorker(): bool;

    /**
     * Returns the additional action parameters that should be serialized.
     *
     * @return  array
     * @since 6.1
     */
    public function getAdditionalParameters(): array;

    /**
     * Loads the additional action parameters from the given data.
     *
     * @param array $data
     * @since 6.1
     */
    public function loadAdditionalParameters(array $data): void;
}
