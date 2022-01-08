<?php

namespace wcf\data\smiley;

use wcf\data\DatabaseObject;
use wcf\data\ITitledObject;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a smiley.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Smiley
 *
 * @property-read   int $smileyID   unique id of the smiley
 * @property-read   int $packageID  id of the package which delivers the smiley
 * @property-read   int|null $categoryID id of the category the smiley belongs to or `null` if it belongs to the default category
 * @property-read   string $smileyPath path to the smiley file relative to wcf's default path
 * @property-read   string $smileyPath2x   path to the smiley file relative to wcf's default path (2x version)
 * @property-read   string $smileyTitle    title of the smiley or name of language item that contains the title
 * @property-read   string $smileyCode code used for displaying the smiley
 * @property-read   string $aliases    alternative codes used for displaying the smiley
 * @property-read   int $showOrder  position of the smiley in relation to the other smileys in the same category
 */
class Smiley extends DatabaseObject implements ITitledObject
{
    protected $height;

    protected $width;

    public $smileyCodes;

    /**
     * @inheritDoc
     * @since   5.2
     */
    public function getTitle()
    {
        return WCF::getLanguage()->get($this->smileyTitle);
    }

    /**
     * Returns the url to this smiley.
     *
     * @return  string
     */
    public function getURL()
    {
        return WCF::getPath() . $this->smileyPath;
    }

    /**
     * Returns the url to the 2x version of the smiley.
     *
     * @return  string
     */
    public function getURL2x()
    {
        return ($this->smileyPath2x) ? WCF::getPath() . $this->smileyPath2x : '';
    }

    /**
     * Returns all aliases for this smiley.
     *
     * @return  string[]
     */
    public function getAliases()
    {
        if (!$this->aliases) {
            return [];
        }

        return \explode("\n", StringUtil::unifyNewlines($this->aliases));
    }

    /**
     * Returns the dimensions of the smiley.
     *
     * @since   5.4
     * @return  int[]
     */
    public function getDimensions()
    {
        if ($this->height === null) {
            $this->height = $this->width = 0;

            $file = WCF_DIR . $this->smileyPath;
            if (\file_exists($file) && \preg_match('~\.(gif|jpe?g|png|webp)$~', $file)) {
                $data = \getimagesize($file);
                if ($data !== false) {
                    // The first two indices of `getimagesize()` represent the image dimensions.
                    [$this->width, $this->height] = $data;
                }
            }
        }

        return [
            'width' => $this->width,
            'height' => $this->height,
        ];
    }

    /**
     * Returns the height of the smiley.
     *
     * @return  int
     */
    public function getHeight()
    {
        return $this->getDimensions()['height'];
    }

    /**
     * Returns the width of the smiley.
     *
     * @since   5.4
     * @return  int
     */
    public function getWidth()
    {
        return $this->getDimensions()['width'];
    }

    /**
     * Returns the html code to render the smiley.
     *
     * @param string $class (additional) class(es) of the smiley element
     * @return  string
     */
    public function getHtml($class = '')
    {
        $srcset = ($this->smileyPath2x) ? ' srcset="' . StringUtil::encodeHTML($this->getURL2x()) . ' 2x"' : '';
        $height = ($this->getHeight()) ? ' height="' . $this->getHeight() . '"' : '';
        $width = ($this->getWidth()) ? ' width="' . $this->getWidth() . '"' : '';
        if ($class !== '') {
            $class = ' ' . $class;
        }

        $title = StringUtil::encodeHTML(WCF::getLanguage()->get($this->smileyTitle));

        return '<img src="' . StringUtil::encodeHTML($this->getURL()) . '" alt="' . StringUtil::encodeHTML($this->smileyCode) . '" title="' . $title . '" class="smiley' . $class . '"' . $srcset . $height . $width . ' loading="eager">';
    }
}
