<?php

namespace wcf\system\importer;

use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyEditor;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Imports smilies.
 *
 * @author  Tim Duesterhus, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SmileyImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = Smiley::class;

    /**
     * known smiley codes
     * @var string[]
     */
    public $knownCodes = [];

    /**
     * Reads out known smiley codes.
     */
    public function __construct()
    {
        $sql = "SELECT  smileyID, smileyCode, aliases
                FROM    wcf1_smiley";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();

        while ($row = $statement->fetchArray()) {
            $known = [];
            if (!empty($row['aliases'])) {
                $known = \explode("\n", $row['aliases']);
            }
            $known[] = $row['smileyCode'];

            foreach ($known as $smileyCode) {
                $this->knownCodes[\mb_strtolower($smileyCode)] = $row['smileyID'];
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function import($oldID, array $data, array $additionalData = [])
    {
        // copy smiley
        $data['smileyPath'] = 'images/smilies/' . \basename($additionalData['fileLocation']);
        if (!@\copy($additionalData['fileLocation'], WCF_DIR . $data['smileyPath'])) {
            return 0;
        }

        // check smileycode
        if (isset($this->knownCodes[\mb_strtolower($data['smileyCode'])])) {
            return $this->knownCodes[\mb_strtolower($data['smileyCode'])];
        }

        $data['packageID'] = 1;
        if (!isset($data['aliases'])) {
            $data['aliases'] = '';
        }

        // check aliases
        $aliases = [];
        if (!empty($data['aliases'])) {
            $aliases = \explode("\n", StringUtil::unifyNewlines($data['aliases']));
            foreach ($aliases as $key => $alias) {
                if (isset($this->knownCodes[\mb_strtolower($alias)])) {
                    unset($aliases[$key]);
                }
            }
            $data['aliases'] = \implode("\n", $aliases);
        }

        // get category id
        if (!empty($data['categoryID'])) {
            $data['categoryID'] = ImportHandler::getInstance()
                ->getNewID('com.woltlab.wcf.smiley.category', $data['categoryID']);
        }

        // save smiley
        $smiley = SmileyEditor::create($data);

        // add smileyCode + aliases to knownCodes
        $this->knownCodes[\mb_strtolower($data['smileyCode'])] = $smiley->smileyID;
        foreach ($aliases as $alias) {
            $this->knownCodes[\mb_strtolower($alias)] = $smiley->smileyID;
        }

        return $smiley->smileyID;
    }
}
