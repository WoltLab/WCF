<?php

namespace wcf\system\importer;

use wcf\data\language\item\LanguageItemEditor;
use wcf\data\like\Like;
use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeEditor;
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

    /**
     * @inheritDoc
     */
    public function import($oldID, array $data, array $additionalData = [])
    {
        if ($data['objectUserID']) {
            $data['objectUserID'] = ImportHandler::getInstance()
                ->getNewID('com.woltlab.wcf.user', $data['objectUserID']);
        }
        $data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
        if (!$data['userID']) {
            return 0;
        }
        if (empty($data['time'])) {
            $data['time'] = 1;
        }

        if (!isset($data['reactionTypeID'])) {
            if ($data['likeValue'] == 1) {
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
            $sql = "SELECT reactionTypeID FROM wcf1_reaction_type WHERE iconFile = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute(['thumbsDown.svg']);
            $reaction = $statement->fetchObject(ReactionType::class);
            if ($reaction === null) {
                $sql = "SELECT MAX(showOrder) FROM wcf1_reaction_type";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute();
                $showOrder = $statement->fetchColumn();

                $reaction = ReactionTypeEditor::create(['iconFile' => 'thumbsDown.svg', 'showOrder' => $showOrder + 1]);

                $sql = "SELECT  languageCategoryID
                        FROM    wcf1_language_category
                        WHERE   languageCategory = ?";
                $statement = WCF::getDB()->prepare($sql, 1);
                $statement->execute(['wcf.reactionType']);
                $languageCategoryID = $statement->fetchSingleColumn();

                foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                    LanguageItemEditor::create([
                        'languageID' => $language->languageID,
                        'languageItem' => 'wcf.reactionType.title' . $reaction->reactionTypeID,
                        'languageItemValue' => ($language->getFixedLanguageCode() === 'de' ? 'Gefällt mir nicht' : 'Dislike'),
                        'languageCategoryID' => $languageCategoryID,
                        'packageID' => 1,
                    ]);
                }

                $editor = new ReactionTypeEditor($reaction);
                $editor->update(['title' => 'wcf.reactionType.title' . $reaction->reactionTypeID]);
            }

            self::$dislikeReactionTypeID = $reaction->reactionTypeID;
        }

        return self::$dislikeReactionTypeID;
    }
}
