/**
 * Handles the dialog to select the user when assigning a user to multiple moderation queue entries
 * via clipboard.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../../Event/Handler", "../../Notification", "../../../User", "../../../StringUtil", "../../../Language", "../../User/Search/Input", "../../../Dom/Traverse", "../../../Ajax", "../../../Dom/Util", "../../Dialog"], function (require, exports, tslib_1, EventHandler, UiNotification, User_1, StringUtil, Language, Input_1, DomTraverse, Ajax, Util_1, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    EventHandler = tslib_1.__importStar(EventHandler);
    UiNotification = tslib_1.__importStar(UiNotification);
    User_1 = tslib_1.__importDefault(User_1);
    StringUtil = tslib_1.__importStar(StringUtil);
    Language = tslib_1.__importStar(Language);
    Input_1 = tslib_1.__importDefault(Input_1);
    DomTraverse = tslib_1.__importStar(DomTraverse);
    Ajax = tslib_1.__importStar(Ajax);
    Util_1 = tslib_1.__importDefault(Util_1);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    class UiModerationClipboardAssignUser {
        /**
         * ids of the moderation queue entries currently being handled
         */
        queueIds = [];
        _ajaxFailure(data) {
            if (data.returnValues?.fieldName === "assignedUsername") {
                let errorMessage = "";
                const dialog = Dialog_1.default.getDialog(this).content;
                const assignedUsername = dialog.querySelector("input[name=assignedUsername]");
                const errorType = data.returnValues.errorType;
                switch (errorType) {
                    case "empty":
                        errorMessage = Language.get("wcf.global.form.error.empty");
                        break;
                    case "notAffected":
                        errorMessage = Language.get("wcf.moderation.assignedUser.error.notAffected");
                        break;
                    default:
                        errorMessage = Language.get(`wcf.user.username.error.${errorType}`, {
                            username: assignedUsername.value,
                        });
                        break;
                }
                Util_1.default.innerError(assignedUsername, errorMessage);
                return false;
            }
            return true;
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "assignUserByClipboard",
                    className: "wcf\\data\\moderation\\queue\\ModerationQueueAction",
                },
            };
        }
        _ajaxSuccess() {
            Dialog_1.default.close(this);
            UiNotification.show(undefined, () => window.location.reload());
        }
        _dialogSetup() {
            const submitCallback = () => this.submitDialog();
            return {
                id: "moderationQueueClipboardAssignUser",
                options: {
                    onSetup(content) {
                        const username = content.querySelector("input[name=assignedUsername]");
                        new Input_1.default(username, {});
                        username.addEventListener("click", (event) => {
                            const assignedUserId = DomTraverse.prevBySel(event.currentTarget, "input[name=assignedUserID]");
                            assignedUserId.click();
                        });
                        content.querySelector("button[data-type=submit]").addEventListener("click", submitCallback);
                    },
                    onShow(content) {
                        // Reset dialog to initial state.
                        const assignedUsername = content.querySelector("input[name=assignedUsername]");
                        content
                            .querySelectorAll("input[name=assignedUserID]")
                            .forEach((el) => (el.checked = el.defaultChecked));
                        assignedUsername.value = "";
                        Util_1.default.innerError(assignedUsername, "");
                    },
                    title: Language.get("wcf.moderation.assignedUser.change"),
                },
                source: `
<div class="section">
  <dl>
    <dt>${Language.get("wcf.moderation.assignedUser")}</dt>
    <dd>
      <ul>
        <li>
          <label>
            <input type="radio" name="assignedUserID" value="${User_1.default.userId}" checked>
            ${StringUtil.escapeHTML(User_1.default.username)}
          </label>
        </li>
        <li>
          <label>
            <input type="radio" name="assignedUserID" value="0">
            ${Language.get("wcf.moderation.assignedUser.nobody")}
          </label>
        </li>
        <li>
          <input type="radio" name="assignedUserID" value="-1">
          <input type="text" name="assignedUsername" value="">
        </li>
      </ul>
    </dd>
  </dl>
</div>
<div class="formSubmit">
  <button type="button" class="button buttonPrimary" data-type="submit">${Language.get("wcf.global.button.save")}</button>
</div>`,
            };
        }
        showDialog(queueIds) {
            this.queueIds = queueIds;
            Dialog_1.default.open(this);
        }
        submitDialog() {
            const dialog = Dialog_1.default.getDialog(this).content;
            const assignedUserId = dialog.querySelector("input[name=assignedUserID]:checked");
            const assignedUsername = dialog.querySelector("input[name=assignedUsername]");
            Ajax.api(this, {
                objectIDs: this.queueIds,
                parameters: {
                    assignedUserID: assignedUserId.value,
                    assignedUsername: assignedUsername.value,
                },
            });
        }
    }
    let isSetUp = false;
    function setup() {
        if (isSetUp) {
            return;
        }
        const handler = new UiModerationClipboardAssignUser();
        EventHandler.add("com.woltlab.wcf.clipboard", "com.woltlab.wcf.moderation.queue", (data) => {
            if (data.data.actionName === "com.woltlab.wcf.moderation.queue.assignUserByClipboard" &&
                data.responseData === null) {
                handler.showDialog(data.data.parameters.objectIDs);
            }
        });
        isSetUp = true;
    }
});
