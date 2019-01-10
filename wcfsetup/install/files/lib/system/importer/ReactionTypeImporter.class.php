<?php
namespace wcf\system\importer;
use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeEditor;

/**
 * Imports reaction types.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 * @since       5.2
 */
class ReactionTypeImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = ReactionType::class;
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		// copy reaction type image
		$data['iconFile'] = basename($additionalData['fileLocation']);
		if (!@copy($additionalData['fileLocation'], WCF_DIR.'images/reaction/'.$data['iconFile'])) return 0;
		
		/** @var ReactionType $reactionType */
		$reactionType = ReactionTypeEditor::create($data);
		
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.reactionType', $oldID, $reactionType->reactionTypeID);
		
		return $reactionType->reactionTypeID;
	}
}
