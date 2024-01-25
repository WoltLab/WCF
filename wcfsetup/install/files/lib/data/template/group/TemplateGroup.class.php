<?php

namespace wcf\data\template\group;

use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a template group.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $templateGroupID        unique id of the template group
 * @property-read   int|null $parentTemplateGroupID      id of the template group's parent template group or `null` if the template group has no parent template group
 * @property-read   string $templateGroupName      name of the template group
 * @property-read   string $templateGroupFolderName    name of the folder containing the modified templates (relative to the normal template folder)
 */
class TemplateGroup extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'templateGroupID';

    /**
     * @var TemplateGroup[][]
     */
    protected static $templateGroupStructure;

    protected static $selectList;

    /**
     * Returns whether the template group is immutable (i.e. whether it's the email
     * or shared template group).
     *
     * @return  bool
     */
    public function isImmutable()
    {
        return $this->templateGroupFolderName === '_wcf_email/' || $this->templateGroupFolderName === '_wcf_shared/';
    }

    /**
     * Returns the localized name of the template group.
     *
     * @return  string
     */
    public function getName()
    {
        return WCF::getLanguage()->get($this->templateGroupName);
    }

    /**
     * Creates a select list of all template groups.
     *
     * @param int[] $ignore Array of template group ids that should be excluded with all of their children.
     *                      -1 denotes that all immutable groups should be ignored.
     * @param int $initialDepth Specifies the initial indentation depth of the list
     * @return  array
     */
    public static function getSelectList($ignore = [], $initialDepth = 0)
    {
        if (self::$templateGroupStructure === null) {
            self::$templateGroupStructure = [];

            $sql = "SELECT      *
                    FROM        wcf" . WCF_N . "_template_group
                    ORDER BY    templateGroupName ASC";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute();
            while ($row = $statement->fetchArray()) {
                self::$templateGroupStructure[$row['parentTemplateGroupID'] ?: 0][] = new self(null, $row);
            }
        }

        self::$selectList = [];
        self::makeSelectList(0, $initialDepth, $ignore);

        return self::$selectList;
    }

    /**
     * Generates the select list.
     *
     * @param int $parentID id of the parent template group
     * @param int $depth current list depth
     * @param array $ignore list of template group ids to ignore in result
     */
    protected static function makeSelectList($parentID = 0, $depth = 0, $ignore = [])
    {
        if (!isset(self::$templateGroupStructure[$parentID ?: 0])) {
            return;
        }

        foreach (self::$templateGroupStructure[$parentID ?: 0] as $templateGroup) {
            if (!empty($ignore) && \in_array($templateGroup->templateGroupID, $ignore)) {
                continue;
            }
            if (\in_array(-1, $ignore) && $templateGroup->isImmutable()) {
                continue;
            }

            // we must encode html here because the htmloptions plugin doesn't do it
            $title = StringUtil::encodeHTML($templateGroup->getName());
            if ($depth > 0) {
                $title = \str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth) . ' ' . $title;
            }

            self::$selectList[$templateGroup->templateGroupID] = $title;
            self::makeSelectList($templateGroup->templateGroupID, $depth + 1, $ignore);
        }
    }
}
