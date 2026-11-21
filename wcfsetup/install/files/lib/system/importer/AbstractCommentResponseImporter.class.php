<?php

namespace wcf\system\importer;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentBuilder;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseEditor;

/**
 * Imports comment responses.
 *
 * @author  Tim Duesterhus, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class AbstractCommentResponseImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = CommentResponse::class;

    /**
     * object type name
     * @var string
     */
    protected $objectTypeName = '';

    #[\Override]
    public function import(mixed $oldID, array $data, array $additionalData = [])
    {
        $data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);

        $data['commentID'] = ImportHandler::getInstance()->getNewID($this->objectTypeName, $data['commentID']);
        if ($data['commentID'] === null) {
            return 0;
        }

        $response = CommentResponseEditor::create($data);

        CommentBuilder::forUpdate(new Comment($response->commentID))
            ->recalculateResponseIDs()
            ->recalculateResponses()
            ->recalculateUnfilteredResponseIDs()
            ->recalculateUnfilteredResponses()
            ->update();

        return $response->responseID;
    }
}
