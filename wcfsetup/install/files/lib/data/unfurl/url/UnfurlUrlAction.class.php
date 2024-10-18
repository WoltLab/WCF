<?php

namespace wcf\data\unfurl\url;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\background\job\UnfurlUrlBackgroundJob;
use wcf\system\WCF;

/**
 * Contains all dbo actions for unfurl url objects.
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.4
 *
 * @method  UnfurlUrlEditor[]   getObjects()
 * @method  UnfurlUrlEditor     getSingleObject()
 */
class UnfurlUrlAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    public function create()
    {
        if (isset($this->parameters['imageData']) && !empty($this->parameters['imageData'])) {
            $this->parameters['data']['imageID'] = $this->saveImageData($this->parameters['imageData']);
        }

        /** @var UnfurlUrl $object */
        $object = parent::create();

        BackgroundQueueHandler::getInstance()->enqueueIn([
            new UnfurlUrlBackgroundJob($object),
        ]);

        BackgroundQueueHandler::getInstance()->forceCheck();

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        if (isset($this->parameters['imageData']) && !empty($this->parameters['imageData'])) {
            $this->parameters['data']['imageID'] = $this->saveImageData($this->parameters['imageData']);
        }

        parent::update();
    }

    private function saveImageData(array $imageData): int
    {
        $keys = $values = '';
        $statementParameters = [];
        foreach ($imageData as $key => $value) {
            if (!empty($keys)) {
                $keys .= ',';
                $values .= ',';
            }

            $keys .= $key;
            $values .= '?';
            $statementParameters[] = $value;
        }

        // save object
        $sql = "INSERT INTO wcf1_unfurl_url_image
                            (" . $keys . ")
                VALUES      (" . $values . ")";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($statementParameters);

        return WCF::getDB()->getInsertID("wcf1_unfurl_url_image", "imageID");
    }

    /**
     * Returns the unfurl url object to a given url.
     */
    public function findOrCreate(): UnfurlUrl
    {
        $object = UnfurlUrl::getByUrl($this->parameters['data']['url']);

        if (!$object) {
            $returnValues = (new self([], 'create', [
                'data' => [
                    'url' => $this->parameters['data']['url'],
                    'urlHash' => \sha1($this->parameters['data']['url']),
                ],
            ]))->executeAction();

            return $returnValues['returnValues'];
        }

        return $object;
    }
}
