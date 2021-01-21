<?php

namespace wcf\data\box\content;

use wcf\data\DatabaseObject;
use wcf\data\media\ViewableMedia;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\html\simple\HtmlSimpleParser;
use wcf\system\WCF;

/**
 * Represents a box content.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Box\Content
 * @since   3.0
 *
 * @property-read   int     $boxContentID       unique id of the box content
 * @property-read   int     $boxID          id of the box the box content belongs to
 * @property-read   int     $languageID     id of the box content's language
 * @property-read   string      $title          title of the page in the associated language
 * @property-read   string      $content        actual content of the box in the associated language
 * @property-read   int|null    $imageID        id of the (image) media object used as box image for the associated language or `null` if no image is used
 * @property-read   int     $hasEmbeddedObjects is `1` if the box content contains embedded objects, otherwise `0`
 */
class BoxContent extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'box_content';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'boxContentID';

    /**
     * image media object
     * @var ViewableMedia
     */
    protected $image;

    /**
     * Returns a certain box content or `null` if it does not exist.
     *
     * @param       int         $boxID
     * @param       int         $languageID
     * @return      BoxContent|null
     */
    public static function getBoxContent($boxID, $languageID)
    {
        if ($languageID !== null) {
            $sql = "SELECT  *
				FROM    wcf" . WCF_N . "_box_content
				WHERE   boxID = ?
					AND languageID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$boxID, $languageID]);
        } else {
            $sql = "SELECT  *
				FROM    wcf" . WCF_N . "_box_content
				WHERE   boxID = ?
					AND languageID IS NULL";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$boxID]);
        }

        if (($row = $statement->fetchSingleRow()) !== false) {
            return new self(null, $row);
        }
    }

    /**
     * Returns the image of this box content.
     *
     * @return  ViewableMedia|null
     */
    public function getImage()
    {
        if ($this->image === null) {
            if ($this->imageID) {
                $this->image = ViewableMedia::getMedia($this->imageID);
            }
        }

        return $this->image;
    }

    /**
     * Sets the image of this box content.
     *
     * @param       ViewableMedia   $image
     */
    public function setImage(ViewableMedia $image)
    {
        $this->image = $image;
    }

    /**
     * Returns the box's formatted content.
     *
     * @return      string
     */
    public function getFormattedContent()
    {
        $processor = new HtmlOutputProcessor();
        $processor->enableUgc = false;
        $processor->process($this->content, 'com.woltlab.wcf.box.content', $this->boxContentID);

        return $processor->getHtml();
    }

    /**
     * Parses simple placeholders embedded in raw html.
     *
     * @return      string          parsed content
     */
    public function getParsedContent()
    {
        return HtmlSimpleParser::getInstance()->replaceTags('com.woltlab.wcf.box.content', $this->boxContentID, $this->content);
    }

    /**
     * Parses simple placeholders embedded in HTML with template scripting.
     *
     * @param       string          $templateName           content template name
     * @return      string          parsed template
     */
    public function getParsedTemplate($templateName)
    {
        HtmlSimpleParser::getInstance()->setContext('com.woltlab.wcf.box.content', $this->boxContentID);

        WCF::getTPL()->registerPrefilter(['simpleEmbeddedObject']);

        $returnValue = WCF::getTPL()->fetch($templateName);

        WCF::getTPL()->removePrefilter('simpleEmbeddedObject');

        return $returnValue;
    }
}
