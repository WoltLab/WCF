/**
 * Handles a user ban.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action/Handler
 * @since       5.5
 */

import * as Ajax from "../../../../../Ajax";
import BanDialog from "./Ban/Dialog";

type Callback = () => void;

export class BanHandler {
  private userIDs: number[];

  public constructor(userIDs: number[]) {
    this.userIDs = userIDs;
  }

  public ban(callback: Callback): void {
    BanDialog.open(this.userIDs, callback);
  }

  public unban(callback: Callback): void {
    Ajax.api({
      _ajaxSetup: () => {
        return {
          data: {
            actionName: "unban",
            className: "wcf\\data\\user\\UserAction",
            objectIDs: this.userIDs,
          },
        };
      },
      _ajaxSuccess: () => callback(),
    });
  }
}

export default BanHandler;
