<?php

namespace wcf\data\unfurl\url;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\background\job\UnfurlURLJob;

/**
 * Contains all dbo actions for unfurl url objects.
 *
 * @author 		Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license 	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package 	WoltLabSuite\Core\Data\Unfurl\Url
 * @since   	5.4
 *
 * @method	UnfurlUrlEditor[]	getObjects()
 * @method	UnfurlUrlEditor	        getSingleObject()
 */
class UnfurlUrlAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    public function create()
    {
        /** @var UnfurlUrl $object */
        $object = parent::create();
        
        // @TODO enqueue job
        
        return $object;
    }
    
    /**
     * Returns the unfurl url object to a given url.
     *
     * @return UnfurlUrl
     */
    public function findOrCreate()
    {
        $object = UnfurlUrl::getByUrl($this->parameters['data']['url']);
        
        if (!$object->urlID) {
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
