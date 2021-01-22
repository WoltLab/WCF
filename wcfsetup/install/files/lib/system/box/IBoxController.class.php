<?php

namespace wcf\system\box;

use wcf\data\box\Box;
use wcf\data\media\ViewableMedia;

/**
 * Default interface for box controllers.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Box
 * @since   3.0
 */
interface IBoxController
{
    /**
     * Returns the content of this box.
     *
     * @return  string
     */
    public function getContent();

    /**
     * Returns false if this box has no content.
     *
     * @return  bool
     */
    public function hasContent();

    /**
     * Returns the image of this box or `null` if the box has no image.
     *
     * Note: The box itself checks if the active user can access the returned media file.
     *
     * @return  ViewableMedia|null
     */
    public function getImage();

    /**
     * Returns the title link of this box.
     *
     * @return  string
     */
    public function getLink();

    /**
     * Returns true if this box has a title link.
     *
     * @return  bool
     */
    public function hasLink();

    /**
     * Returns the database object of this box.
     *
     * @return  Box
     */
    public function getBox();

    /**
     * Sets the database object of this box.
     *
     * @param Box $box
     */
    public function setBox(Box $box);

    /**
     * Saves additional box data for box set via `setBox()`.
     */
    public function saveAdditionalData();

    /**
     * Returns the title of this box, the special value `null` can be returned to
     * apply the default behavior as seen in previous versions. Only supported for
     * system-type boxes.
     *
     * @return      string|null
     * @since       5.2
     */
    public function getTitle();

    /**
     * Returns a list of supported box positions.
     *
     * @return  string[]
     */
    public static function getSupportedPositions();
}
