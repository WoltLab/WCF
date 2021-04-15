/**
 * Provides data of the active user.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  User (alias)
 * @module  WoltLabSuite/Core/User
 */

class User {
  constructor(
    readonly userId: number,
    readonly username: string,
    readonly link: string,
    readonly accessToken: string,
  ) {}
}

let user: User;

export = {
  /**
   * Returns the link to the active user's profile or an empty string
   * if the active user is a guest.
   */
  getLink(): string {
    return user.link;
  },

  /**
   * Initializes the user object.
   */
  init(userId: number, username: string, link: string, accessToken: string): void {
    if (user) {
      throw new Error("User has already been initialized.");
    }

    user = new User(userId, username, link, accessToken);
  },

  get accessToken(): string {
    return user.accessToken;
  },

  get userId(): number {
    return user.userId;
  },

  get username(): string {
    return user.username;
  },
};
