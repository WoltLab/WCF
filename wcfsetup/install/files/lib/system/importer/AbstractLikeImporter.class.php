<?php

namespace wcf\system\importer;

use wcf\command\file\CreateFileFromExistingFile;
use wcf\data\like\Like;
use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\reaction\ReactionHandler;
use wcf\system\WCF;

/**
 * Imports likes.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class AbstractLikeImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = Like::class;

    /**
     * object type id for likes
     * @var int
     */
    protected $objectTypeID = 0;

    /**
     * @var int|null
     */
    protected static $dislikeReactionTypeID;

    #[\Override]
    public function import(mixed $oldID, array $data, array $additionalData = [])
    {
        if (!empty($data['objectUserID'])) {
            $data['objectUserID'] = ImportHandler::getInstance()
                ->getNewID('com.woltlab.wcf.user', $data['objectUserID']);
        }
        $data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
        if ($data['userID'] === null) {
            return 0;
        }
        if (empty($data['time'])) {
            $data['time'] = 1;
        }

        if (!isset($data['reactionTypeID'])) {
            if ($data['likeValue'] === 1) {
                $data['reactionTypeID'] = ReactionHandler::getInstance()->getFirstReactionTypeID();
            } else {
                $data['reactionTypeID'] = self::getDislikeReactionTypeID();
            }
        } else {
            $data['reactionTypeID'] = ImportHandler::getInstance()
                ->getNewID('com.woltlab.wcf.reactionType', $data['reactionTypeID']);
        }

        if (empty($data['reactionTypeID'])) {
            return 0;
        }

        $sql = "INSERT IGNORE INTO  wcf1_like
                                    (objectID, objectTypeID, objectUserID, userID, time, likeValue, reactionTypeID)
                VALUES              (?, ?, ?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $data['objectID'],
            $this->objectTypeID,
            $data['objectUserID'],
            $data['userID'],
            $data['time'],
            $data['likeValue'],
            $data['reactionTypeID'],
        ]);

        return 0;
    }

    /**
     * @return int
     */
    protected static function getDislikeReactionTypeID()
    {
        if (self::$dislikeReactionTypeID === null) {
            $sql = "SELECT      reaction_type.*
                    FROM        wcf1_reaction_type reaction_type
                    INNER JOIN  wcf1_file file
                    ON          file.fileID = reaction_type.iconFileID
                    WHERE       file.filename = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute(['thumbsDown.svg']);
            $reaction = $statement->fetchObject(ReactionType::class);
            if ($reaction === null) {
                $sql = "SELECT MAX(showOrder) FROM wcf1_reaction_type";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute();
                $showOrder = $statement->fetchColumn();

                // The bundled image must remain available for later imports.
                $file = new CreateFileFromExistingFile(
                    \WCF_DIR . 'images/reaction/thumbsDown.svg',
                    'thumbsDown.svg',
                    'com.woltlab.wcf.reactionType.icon',
                    copy: true,
                )();

                $title = [];
                foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                    $title[$language->languageID] = $language->getFixedLanguageCode() === 'de' ? 'Gefällt mir nicht' : 'Dislike';
                }

                $reaction = ReactionTypeBuilder::forCreate()
                    ->setTitle($title)
                    ->setShowOrder($showOrder + 1)
                    ->setIconFileID($file?->fileID)
                    ->create();
            }

            self::$dislikeReactionTypeID = $reaction->reactionTypeID;
        }

        return self::$dislikeReactionTypeID;
    }
}
