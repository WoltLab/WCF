<?php

namespace wcf\data\unfurl\url;

use wcf\command\unfurl\url\FindOrCreateUnfurlUrl;
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
 * @extends AbstractDatabaseObjectAction<UnfurlUrl, UnfurlUrlEditor>
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

    /**
     * @param array<string, int|float|string> $imageData
     */
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

        return (int)WCF::getDB()->getInsertID("wcf1_unfurl_url_image", "imageID");
    }

    /**
     * Returns the unfurl url object to a given url.
     * @deprecated 6.2 Use `FindOrCreateUnfurlUrl` instead.
     */
    public function findOrCreate(): UnfurlUrl
    {
        return (new FindOrCreateUnfurlUrl($this->parameters['data']['url']))();
    }
}
