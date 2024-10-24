<?php

namespace wcf\system\importer;

use wcf\system\WCF;

/**
 * Imports ACLs.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class AbstractACLImporter extends AbstractImporter
{
    /**
     * object type id for options
     * @var int
     */
    protected $objectTypeID = 0;

    /**
     * object type name
     * @var int
     */
    protected $objectTypeName = '';

    /**
     * available options
     * @var array
     */
    protected $options = [];

    /**
     * Creates an AbstractACLImporter object.
     */
    public function __construct()
    {
        // get options
        $sql = "SELECT  optionName, optionID
                FROM    wcf1_acl_option
                WHERE   objectTypeID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->objectTypeID]);
        $this->options = $statement->fetchMap('optionName', 'optionID');
    }

    /**
     * @inheritDoc
     */
    public function import($oldID, array $data, array $additionalData = [])
    {
        if (!isset($this->options[$additionalData['optionName']])) {
            return 0;
        }
        $data['optionID'] = $this->options[$additionalData['optionName']];

        $data['objectID'] = ImportHandler::getInstance()->getNewID($this->objectTypeName, $data['objectID']);
        if (!$data['objectID']) {
            return 0;
        }

        if (!empty($data['groupID'])) {
            $data['groupID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user.group', $data['groupID']);
            if (!$data['groupID']) {
                return 0;
            }

            $sql = "INSERT IGNORE INTO  wcf1_acl_option_to_group
                                        (optionID, objectID, groupID, optionValue)
                    VALUES              (?, ?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$data['optionID'], $data['objectID'], $data['groupID'], $data['optionValue']]);

            return 1;
        } elseif (!empty($data['userID'])) {
            $data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
            if (!$data['userID']) {
                return 0;
            }

            $sql = "INSERT IGNORE INTO  wcf1_acl_option_to_user
                                        (optionID, objectID, userID, optionValue)
                    VALUES              (?, ?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$data['optionID'], $data['objectID'], $data['userID'], $data['optionValue']]);

            return 1;
        }
    }
}
