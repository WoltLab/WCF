<?php
namespace wcf\system\importer;
use wcf\data\page\content\PageContentEditor;
use wcf\data\page\Page;
use wcf\data\page\PageEditor;
use wcf\system\language\LanguageFactory;

/**
 * Imports cms pages.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 * @since       5.2
 */
class PageImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = Page::class;
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		$contents = [];
		foreach ($additionalData['contents'] as $languageCode => $contentData) {
			$languageID = 0;
			if ($languageCode) {
				if (($language = LanguageFactory::getInstance()->getLanguageByCode($languageCode)) !== null) {
					$languageID = $language->languageID;
				}
				else {
					continue;
				}
			}
			
			$contents[$languageID] = [
				'title' => (!empty($contentData['title']) ? $contentData['title'] : ''),
				'content' => (!empty($contentData['content']) ? $contentData['content'] : ''),
				'metaDescription' => (!empty($contentData['metaDescription']) ? $contentData['metaDescription'] : ''),
				'metaKeywords' => (!empty($contentData['content']) ? $contentData['content'] : ''),
				'customURL' => (!empty($contentData['customURL']) ? $contentData['customURL'] : ''),
				'hasEmbeddedObjects' => (!empty($contentData['hasEmbeddedObjects']) ? $contentData['hasEmbeddedObjects'] : 0)
			];
		}
		if (empty($contents)) return 0;
		if (count($contents) > 1) {
			$data['isMultilingual'] = 1;
		}
		if (empty($data['packageID'])) {
			$data['packageID'] = 1;
		}
		if (empty($data['applicationPackageID'])) {
			$data['applicationPackageID'] = 1;
		}
		
		// check old id
		if (is_numeric($oldID)) {
			$page = new Page($oldID);
			if (!$page->pageID) $data['pageID'] = $oldID;
		}
		
		// save page
		$page = PageEditor::create($data);
		if (!$page->identifier) {
			// set generic page identifier
			$pageEditor = new PageEditor($page);
			$pageEditor->update([
				'identifier' => 'com.woltlab.wcf.generic'.$page->pageID
			]);
		}
		
		// save page content
		foreach ($contents as $languageID => $contentData) {
			PageContentEditor::create([
				'pageID' => $page->pageID,
				'languageID' => $languageID ?: null,
				'title' => $contentData['title'],
				'content' => $contentData['content'],
				'metaDescription' => $contentData['metaDescription'],
				'metaKeywords' => $contentData['metaKeywords'],
				'customURL' => $contentData['customURL'],
				'hasEmbeddedObjects' => $contentData['hasEmbeddedObjects']
			]);
		}
		
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.page', $oldID, $page->pageID);
		return $page->pageID;
	}
}
