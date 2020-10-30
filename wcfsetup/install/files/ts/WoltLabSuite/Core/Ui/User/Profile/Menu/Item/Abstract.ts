/**
 * Default implementation for user interaction menu items used in the user profile.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/Profile/Menu/Item/Abstract
 */

import * as Ajax from "../../../../../Ajax";
import { AjaxCallbackObject, RequestOptions } from "../../../../../Ajax/Data";

abstract class UiUserProfileMenuItemAbstract implements AjaxCallbackObject {
  readonly _button = document.createElement("a");
  readonly _isActive: boolean;
  readonly _listItem = document.createElement("li");
  readonly _userId: number;

  /**
   * Creates a new user profile menu item.
   */
  protected constructor(userId: number, isActive: boolean) {
    this._userId = userId;
    this._isActive = isActive;

    this._initButton();
    this._updateButton();
  }

  /**
   * Initializes the menu item.
   */
  protected _initButton(): void {
    this._button.href = "#";
    this._button.addEventListener("click", (ev) => this._toggle(ev));
    this._listItem.appendChild(this._button);

    const menu = document.querySelector(`.userProfileButtonMenu[data-menu="interaction"]`) as HTMLElement;
    menu.insertAdjacentElement("afterbegin", this._listItem);
  }

  /**
   * Handles clicks on the menu item button.
   */
  protected _toggle(event: MouseEvent): void {
    event.preventDefault();

    Ajax.api(this, {
      actionName: this._getAjaxActionName(),
      parameters: {
        data: {
          userID: this._userId,
        },
      },
    });
  }

  /**
   * Updates the button state and label.
   *
   * @protected
   */
  protected _updateButton(): void {
    this._button.textContent = this._getLabel();
    if (this._isActive) {
      this._listItem.classList.add("active");
    } else {
      this._listItem.classList.remove("active");
    }
  }

  /**
   * Returns the button label.
   */
  protected _getLabel(): string {
    // This should be an abstract method, but cannot be marked as such for backwards compatibility.

    throw new Error("Implement me!");
  }

  /**
   * Returns the Ajax action name.
   */
  protected _getAjaxActionName(): string {
    // This should be an abstract method, but cannot be marked as such for backwards compatibility.

    throw new Error("Implement me!");
  }

  /**
   * Handles successful Ajax requests.
   */
  _ajaxSuccess(): void {
    // This should be an abstract method, but cannot be marked as such for backwards compatibility.

    throw new Error("Implement me!");
  }

  /**
   * Returns the default Ajax request data
   */
  _ajaxSetup(): RequestOptions {
    // This should be an abstract method, but cannot be marked as such for backwards compatibility.

    throw new Error("Implement me!");
  }
}

export = UiUserProfileMenuItemAbstract;
