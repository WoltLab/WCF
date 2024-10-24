<?php

namespace wcf\system\cache\builder;

use wcf\data\bbcode\attribute\BBCodeAttribute;
use wcf\data\bbcode\BBCodeList;
use wcf\system\WCF;

/**
 * Caches the bbcodes.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class BBCodeCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected function rebuild(array $parameters)
    {
        $attributes = [];
        $data = ['bbcodes' => []];

        // get attributes
        $sql = "SELECT      attribute.*, bbcode.bbcodeTag
                FROM        wcf1_bbcode_attribute attribute
                LEFT JOIN   wcf1_bbcode bbcode
                ON          bbcode.bbcodeID = attribute.bbcodeID
                ORDER BY    attribute.attributeNo";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            if (!isset($attributes[$row['bbcodeTag']])) {
                $attributes[$row['bbcodeTag']] = [];
            }

            $attributes[$row['bbcodeTag']][$row['attributeNo']] = new BBCodeAttribute(null, $row);
        }

        // get bbcodes
        $bbcodeList = new BBCodeList();
        $bbcodeList->readObjects();
        foreach ($bbcodeList as $bbcode) {
            if (isset($attributes[$bbcode->bbcodeTag])) {
                $bbcode->setAttributes($attributes[$bbcode->bbcodeTag]);
            } else {
                // set an empty array, because the internal default value of a bbcode's
                // attributes is null, this avoid an infinite loop
                $bbcode->setAttributes([]);
            }

            $data['bbcodes'][$bbcode->bbcodeTag] = $bbcode;
        }

        return $data;
    }
}
