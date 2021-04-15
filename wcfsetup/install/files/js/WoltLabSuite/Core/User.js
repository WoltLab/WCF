/**
 * Provides data of the active user.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  User (alias)
 * @module  WoltLabSuite/Core/User
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    class User {
        constructor(userId, username, link, accessToken) {
            this.userId = userId;
            this.username = username;
            this.link = link;
            this.accessToken = accessToken;
        }
    }
    let user;
    return {
        /**
         * Returns the link to the active user's profile or an empty string
         * if the active user is a guest.
         */
        getLink() {
            return user.link;
        },
        /**
         * Initializes the user object.
         */
        init(userId, username, link, accessToken) {
            if (user) {
                throw new Error("User has already been initialized.");
            }
            user = new User(userId, username, link, accessToken);
        },
        get accessToken() {
            return user.accessToken;
        },
        get userId() {
            return user.userId;
        },
        get username() {
            return user.username;
        },
    };
});
