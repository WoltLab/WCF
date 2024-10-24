<?php

namespace wcf\system\importer;

use wcf\data\article\Article;
use wcf\data\article\ArticleEditor;
use wcf\data\article\content\ArticleContentEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\language\LanguageFactory;
use wcf\system\tagging\TagEngine;
use wcf\system\WCF;

/**
 * Imports cms articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ArticleImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = Article::class;

    /**
     * category for orphaned articles
     * @var int
     */
    private $importCategoryID = 0;

    /**
     * @inheritDoc
     */
    public function import($oldID, array $data, array $additionalData = [])
    {
        $data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);

        $contents = [];
        foreach ($additionalData['contents'] as $languageCode => $contentData) {
            $languageID = 0;
            if ($languageCode) {
                if (($language = LanguageFactory::getInstance()->getLanguageByCode($languageCode)) !== null) {
                    $languageID = $language->languageID;
                } else {
                    continue;
                }
            }

            $imageID = null;
            if (!empty($contentData['imageID'])) {
                $imageID = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.media', $contentData['imageID']);
            }

            $teaserImageID = null;
            if (!empty($contentData['teaserImageID'])) {
                $teaserImageID = ImportHandler::getInstance()
                    ->getNewID('com.woltlab.wcf.media', $contentData['teaserImageID']);
            }

            $contents[$languageID] = [
                'title' => (!empty($contentData['title']) ? $contentData['title'] : ''),
                'teaser' => (!empty($contentData['teaser']) ? $contentData['teaser'] : ''),
                'content' => (!empty($contentData['content']) ? $contentData['content'] : ''),
                'imageID' => $imageID,
                'teaserImageID' => $teaserImageID,
                'tags' => (!empty($contentData['tags']) ? $contentData['tags'] : []),
                'metaTitle' => (!empty($contentData['metaTitle']) ? $contentData['metaTitle'] : ''),
                'metaDescription' => (!empty($contentData['metaDescription']) ? $contentData['metaDescription'] : ''),
            ];
        }
        if (empty($contents)) {
            return 0;
        }
        if (\count($contents) > 1) {
            $data['isMultilingual'] = 1;
        }

        // check old id
        if (\ctype_digit((string)$oldID)) {
            $article = new Article($oldID);
            if (!$article->articleID) {
                $data['articleID'] = $oldID;
            }
        }

        // category
        $categoryID = 0;
        if (!empty($data['categoryID'])) {
            $categoryID = ImportHandler::getInstance()
                ->getNewID('com.woltlab.wcf.article.category', $data['categoryID']);
        }
        if (!$categoryID) {
            $categoryID = $this->getImportCategoryID();
        }
        $data['categoryID'] = $categoryID;

        // comments
        if (!isset($data['enableComments'])) {
            $data['enableComments'] = ARTICLE_ENABLE_COMMENTS_DEFAULT_VALUE ? 1 : 0;
        }

        // save article
        $article = ArticleEditor::create($data);

        // save article content
        foreach ($contents as $languageID => $contentData) {
            $articleContent = ArticleContentEditor::create([
                'articleID' => $article->articleID,
                'languageID' => $languageID ?: null,
                'title' => $contentData['title'],
                'teaser' => $contentData['teaser'],
                'content' => $contentData['content'],
                'imageID' => $contentData['imageID'],
                'teaserImageID' => $contentData['teaserImageID'],
                'metaTitle' => $contentData['metaTitle'],
                'metaDescription' => $contentData['metaDescription'],
            ]);

            // save tags
            if (!empty($contentData['tags'])) {
                TagEngine::getInstance()->addObjectTags(
                    'com.woltlab.wcf.article',
                    $articleContent->articleContentID,
                    $contentData['tags'],
                    $languageID ?: LanguageFactory::getInstance()->getDefaultLanguageID()
                );
            }
        }

        ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.article', $oldID, $article->articleID);

        return $article->articleID;
    }

    /**
     * Returns the id of the category used for articles without previous categories.
     *
     * @return  int
     */
    private function getImportCategoryID()
    {
        if (!$this->importCategoryID) {
            $objectTypeID = ObjectTypeCache::getInstance()
                ->getObjectTypeIDByName('com.woltlab.wcf.category', 'com.woltlab.wcf.article.category');

            $sql = "SELECT      categoryID
                    FROM        wcf1_category
                    WHERE       objectTypeID = ?
                            AND parentCategoryID = ?
                            AND title = ?
                    ORDER BY    categoryID";
            $statement = WCF::getDB()->prepare($sql, 1);
            $statement->execute([$objectTypeID, 0, 'Import']);
            $categoryID = $statement->fetchSingleColumn();
            if ($categoryID) {
                $this->importCategoryID = $categoryID;
            } else {
                $sql = "INSERT INTO wcf1_category
                                    (objectTypeID, parentCategoryID, title, showOrder, time)
                        VALUES      (?, ?, ?, ?, ?)";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([$objectTypeID, 0, 'Import', 0, TIME_NOW]);
                $this->importCategoryID = WCF::getDB()->getInsertID("wcf1_category", 'categoryID');
            }
        }

        return $this->importCategoryID;
    }
}
