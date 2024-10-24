<?php

namespace wcf\data\unfurl\url;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of unfurled urls.
 *
 * @author      Joshua Ruesweg
 * @copyright   2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.4
 *
 * @method      UnfurlUrl           current()
 * @method      UnfurlUrl[]         getObjects()
 * @method      UnfurlUrl|null      getSingleObject()
 * @method      UnfurlUrl|null      search($objectID)
 * @property    UnfurlUrl[]         $objects
 */
class UnfurlUrlList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        if (!empty($this->sqlSelects)) {
            $this->sqlSelects .= ',';
        }
        $this->sqlSelects .= "unfurl_url_image.*";
        $this->sqlJoins .= "
            LEFT JOIN   wcf1_unfurl_url_image unfurl_url_image
            ON          unfurl_url_image.imageID = unfurl_url.imageID";
    }
}
