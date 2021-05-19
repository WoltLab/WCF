/**
 * Shows the ignore dialogs when editing users on the page listing ignored users.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/Ignore/List
 * @woltlabExcludeBundle all
 */

import FormBuilderDialog from "../../../Form/Builder/Dialog";
import * as Language from "../../../Language";
import * as UiNotification from "../../Notification";

interface AjaxResponse {
  isIgnoredUser: 0 | 1;
}

export class UiUserIgnoreList {
  protected dialogs = new Map<number, FormBuilderDialog>();

  constructor() {
    document
      .querySelectorAll(".jsEditIgnoreButton")
      .forEach((el) => el.addEventListener("click", (ev) => this.openDialog(ev)));
  }

  protected openDialog(event: Event): void {
    const button = event.currentTarget as HTMLAnchorElement;
    const userId = ~~(button.closest(".jsIgnoredUser") as HTMLLIElement).dataset.objectId!;

    if (!this.dialogs.has(userId)) {
      this.dialogs.set(
        userId,
        new FormBuilderDialog("ignoreDialog", "wcf\\data\\user\\ignore\\UserIgnoreAction", "getDialog", {
          dialog: {
            title: Language.get("wcf.user.button.ignore"),
          },
          actionParameters: {
            userID: userId,
          },
          submitActionName: "submitDialog",
          successCallback(data: AjaxResponse) {
            UiNotification.show(undefined, () => {
              if (!data.isIgnoredUser) {
                window.location.reload();
              }
            });
          },
        }),
      );
    }

    this.dialogs.get(userId)!.open();
  }
}

export default UiUserIgnoreList;
