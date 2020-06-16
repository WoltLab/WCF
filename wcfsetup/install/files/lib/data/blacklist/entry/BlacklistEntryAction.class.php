<?php
namespace wcf\data\blacklist\entry;
use wcf\data\blacklist\status\BlacklistStatus;
use wcf\data\blacklist\status\BlacklistStatusEditor;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\HTTPNotFoundException;
use wcf\system\exception\HTTPServerErrorException;
use wcf\system\exception\HTTPUnauthorizedException;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\exception\HTTPException;
use wcf\util\HTTPRequest;
use wcf\util\JSON;

/**
 * Executes blacklist entry-related actions.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Data\Blacklist\Entry
 * 
 * @method BlacklistEntryEditor[] getObjects()
 * @method BlacklistEntryEditor getSingleObject()
 * @since 5.2
 */
class BlacklistEntryAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = BlacklistEntryEditor::class;
	
	public function import() {
		// Check if we need to import any data at all.
		$status = BlacklistStatus::getAll();
		$nextDelta = BlacklistStatus::getNextDelta($status);
		if ($nextDelta === null) {
			return;
		}
		
		$request = new HTTPRequest("https://assets.woltlab.com/blacklist/{$nextDelta}");
		try {
			$request->execute();
		}
		catch (SystemException $e) {
			if (
				$e instanceof HTTPNotFoundException
				|| $e instanceof HTTPUnauthorizedException
				|| $e instanceof HTTPServerErrorException
				|| $e instanceof HTTPException
			) {
				\wcf\functions\exception\logThrowable($e);
				
				return;
			}
			
			throw $e;
		}
		
		$response = $request->getReply();
		if ($response['statusCode'] == 200) {
			$data = JSON::decode($response['body']);
			if (is_array($data)) {
				$sql = "INSERT INTO             wcf".WCF_N."_blacklist_entry
								(type, hash, lastSeen, occurrences)
					VALUES                  (?, ?, ?, ?)
					ON DUPLICATE KEY UPDATE lastSeen = VALUES(lastSeen),
								occurrences = VALUES(occurrences)";
				$statement = WCF::getDB()->prepareStatement($sql);
				
				$lastSeen = preg_replace('~^(.+)T(.+)Z~', '$1 $2', $data['meta']['end']);
				
				WCF::getDB()->beginTransaction();
				foreach (['email', 'ipv4', 'ipv6', 'username'] as $type) {
					foreach ($data[$type] as $hash => $occurrences) {
						$statement->execute([
							$type,
							hex2bin($hash),
							$lastSeen,
							min($occurrences, 32767),
						]);
					}
				}
				WCF::getDB()->commitTransaction();
				unset($entries);
				
				$blacklistStatus = new BlacklistStatus($data['meta']['date']);
				if (!$blacklistStatus->date) {
					$blacklistStatus = BlacklistStatusEditor::create(['date' => $data['meta']['date']]);
				}
				
				(new BlacklistStatusEditor($blacklistStatus))->update([$data['meta']['type'] => 1]);
			}
		}
	}
}
