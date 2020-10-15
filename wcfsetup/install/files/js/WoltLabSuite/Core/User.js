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
    let _link;
    return {
        /**
         * Returns the link to the active user's profile or an empty string
         * if the active user is a guest.
         */
        getLink: () => _link || '',
        /**
         * Initializes the user object.
         */
        init: (userId, username, link) => {
            if (_link !== undefined) {
                throw new Error('User has already been initialized.');
            }
            // define non-writeable properties for userId and username
            Object.defineProperty(this, 'userId', {
                value: userId,
                writable: false,
            });
            Object.defineProperty(this, 'username', {
                value: username,
                writable: false,
            });
            _link = link || '';
        },
    };
});
