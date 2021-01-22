<?php

namespace wcf\system\message\embedded\object;

use wcf\data\DatabaseObject;
use wcf\system\html\input\HtmlInputProcessor;

/**
 * Default interface of embedded object handler.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Message\Embedded\Object
 *
 * @property-read   int $objectTypeID   id of the embedded object type
 */
interface IMessageEmbeddedObjectHandler
{
    /**
     * Processes embedded data and optionally accesses the current
     * document to extract additional data. Returns the IDs of found
     * embedded objects.
     *
     * @param HtmlInputProcessor $htmlInputProcessor html input processor holding the current document
     * @param mixed[] $embeddedData list of found embedded data with attributes
     * @return      int[]               ids of found embedded objects
     */
    public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData);

    /**
     * Loads and returns embedded objects.
     *
     * @param array $objectIDs
     * @return  DatabaseObject[]
     */
    public function loadObjects(array $objectIDs);
}
