<?php

namespace wcf\data\bbcode;

use wcf\data\bbcode\attribute\BBCodeAttribute;
use wcf\data\ProcessibleDatabaseObject;
use wcf\system\bbcode\IBBCode;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents a bbcode.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $bbcodeID       unique id of the bbcode
 * @property-read   string $bbcodeTag      tag of the bbcode
 * @property-read   int $packageID      id of the package which delivers the bbcode or `1` if it has been created in the acp
 * @property-read   string $htmlOpen       html code of the opening tag (without the less-than sign and greater-than sign) or empty if no such html code exists
 * @property-read   string $htmlClose      html code of the closing tag (without the less-than sign and greater-than sign) or empty if no such html code exists
 * @property-read   string $className      name of the PHP class implementing `wcf\system\bbcode\IBBCode` or empty if no such class exists
 * @property-read   int $isBlockElement     is `1` if the bbcode represents a block element and thus can contain multiple lines, otherwise `0`
 * @property-read   string $wysiwygIcon        css class name used as icon for the bbcode in the editor toolbar
 * @property-read   string $buttonLabel        name of the language item used as button label for the bbcode in the editor toolbar
 * @property-read   int $isSourceCode       is `1` if the bbcode's content is treated as source code, otherwise `0`
 * @property-read   int $showButton     is `1` if a button for the bbcode will be shown in the editor toolbar, otherwise `0`
 * @property-read   int $originIsSystem     is `1` if the bbcode has been delivered by a package, otherwise `0` (if the bbcode has been created by an admin in the acp)
 */
class BBCode extends ProcessibleDatabaseObject implements IRouteController
{
    /**
     * list of attributes
     * @var BBCodeAttribute[]
     */
    protected $attributes;

    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'bbcode';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'bbcodeID';

    /**
     * @inheritDoc
     */
    protected static $processorInterface = IBBCode::class;

    /**
     * Returns the attributes of this bbcode.
     *
     * @return  BBCodeAttribute[]
     */
    public function getAttributes()
    {
        if ($this->attributes === null) {
            $this->attributes = BBCodeCache::getInstance()->getBBCodeAttributes($this->bbcodeTag);
        }

        return $this->attributes;
    }

    /**
     * Sets the attributes of this bbcode.
     *
     * @param BBCodeAttribute[] $attributes list of attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->bbcodeTag;
    }

    /**
     * Returns the button label in the active user's language.
     *
     * @return  string
     * @since   5.2
     */
    public function getButtonLabel()
    {
        return WCF::getLanguage()->get($this->buttonLabel);
    }

    /**
     * Returns BBCode object with the given tag.
     *
     * @param string $tag
     * @return  BBCode
     */
    public static function getBBCodeByTag($tag)
    {
        $sql = "SELECT  *
                FROM    wcf1_bbcode
                WHERE   bbcodeTag = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$tag]);
        $row = $statement->fetchArray();
        if (!$row) {
            $row = [];
        }

        return new self(null, $row);
    }

    /**
     * Returns true if the given BBCode tag is allowed by the given list of
     * BBCode tags. If the relevant BBCode should be globally disabled or non-
     * existent, false is returned.
     *
     * @param string $bbcodeTag
     * @param string[] $allowedBBCodeTags
     * @return  bool
     */
    public static function isAllowedBBCode($bbcodeTag, array $allowedBBCodeTags)
    {
        // check if bbcode is unknown or disabled
        if (BBCodeCache::getInstance()->getBBCodeByTag($bbcodeTag) === null) {
            return false;
        }

        // all BBCodes are allowed
        if (\in_array('all', $allowedBBCodeTags)) {
            return true;
        }

        // no BBCode are allowed
        if (\in_array('none', $allowedBBCodeTags)) {
            return false;
        }

        return \in_array($bbcodeTag, $allowedBBCodeTags);
    }

    /**
     * Returns true if this BBCode can be deleted.
     *
     * @return  bool
     */
    public function canDelete()
    {
        if ($this->originIsSystem) {
            return false;
        }

        return true;
    }

    /**
     * Returns the FontAwesome icon string or the URL to the image.
     *
     * @since 6.0
     */
    public function getIcon(): string
    {
        if ($this->wysiwygIcon === '') {
            return '';
        }

        if (\str_contains($this->wysiwygIcon, ';')) {
            return $this->wysiwygIcon;
        }

        return \sprintf(
            '%sicon/%s',
            WCF::getPath(),
            $this->wysiwygIcon
        );
    }
}
