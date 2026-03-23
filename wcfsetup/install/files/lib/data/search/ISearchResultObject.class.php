<?php

namespace wcf\data\search;

use wcf\data\user\UserProfile;

/**
 * All search result objects should implement this interface.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface ISearchResultObject
{
    /**
     * Returns author's user profile.
     *
     * @return ?UserProfile
     */
    public function getUserProfile();

    /**
     * Returns the subject of this object.
     *
     * @return  string
     */
    public function getSubject();

    /**
     * Returns the creation time.
     *
     * @return  int
     */
    public function getTime();

    /**
     * Returns the link to this object.
     *
     * @return  string
     */
    public function getLink(string $query = '');

    /**
     * Returns the object type name.
     *
     * @return  string
     */
    public function getObjectTypeName();

    /**
     * Returns the message text.
     *
     * @return  string
     */
    public function getFormattedMessage();

    /**
     * Returns the title of object's container. Returns empty string if there
     * is no container.
     *
     * @return  string
     */
    public function getContainerTitle();

    /**
     * Returns the link to object's container. Returns empty string if there
     * is no container.
     *
     * @return  string
     */
    public function getContainerLink();
}
