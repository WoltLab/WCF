<?php

namespace wcf\data;

/**
 * Default interface for user generated content.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data
 */
interface IUserContent extends ITitledLinkObject
{
    /**
     * Returns message creation timestamp.
     *
     * @return  int
     */
    public function getTime();

    /**
     * Returns author's user id.
     *
     * @return  int
     */
    public function getUserID();

    /**
     * Returns author's username.
     *
     * @return  string
     */
    public function getUsername();
}
