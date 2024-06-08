/**
 * Provides data of the active user.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

class User {
  constructor(
    readonly userId: number,
    readonly username: string,
    readonly link: string,
    readonly guestTokenDialogEndpoint: string,
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
  init(userId: number, username: string, link: string, guestTokenDialogEndpoint: string = ""): void {
    if (user) {
      throw new Error("User has already been initialized.");
    }

    user = new User(userId, username, link, guestTokenDialogEndpoint);
  },

  get userId(): number {
    return user.userId;
  },

  get username(): string {
    return user.username;
  },

  get guestTokenDialogEndpoint(): string {
    return user.guestTokenDialogEndpoint;
  },
};
