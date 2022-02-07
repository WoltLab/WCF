/**
 * Abstract implementation for poll views.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Manager/View/Abstract
 * @since   5.5
 */

import * as Core from "../../../../Core";
import AjaxRequest from "../../../../Ajax/Request";
import { RequestOptions, ResponseData } from "../../../../Ajax/Data";
import Manager, { PollViews } from "../Manager";

export abstract class Abstract {
  protected readonly pollManager: Manager;
  protected button: HTMLButtonElement;

  public constructor(manager: Manager) {
    this.pollManager = manager;

    this.initButton();
  }

  private apiCall(actionName: string, data?: RequestOptions): void {
    const request = new AjaxRequest({
      url: `index.php?poll/&t=${Core.getXsrfToken()}`,
      data: Core.extend(
        {
          actionName,
          pollID: this.pollManager.pollID,
        },
        data ? data : {},
      ),
      success: (data: ResponseData) => {
        this.button.disabled = false;

        this.success(data);
      },
    });
    request.sendRequest();
  }

  protected initButton(): void {
    const button =
      (this.pollManager.getPollContainer().querySelector(this.getButtonSelector()) as HTMLButtonElement) || null;

    if (!button) {
      throw new Error(
        `Could not find button with selector "${this.getButtonSelector()}" for poll "${this.pollManager.pollID}"`,
      );
    }

    this.button = button;

    this.button.addEventListener("click", (event) => {
      if (event) {
        event.preventDefault();
      }

      this.apiCall(this.getActionName(), this.getData());

      this.button.disabled = true;
    });
  }

  protected getData(): RequestOptions | undefined {
    return undefined;
  }

  protected changeView(view: PollViews, html: string): void {
    this.pollManager.changeView(view, html);
  }

  public hideButton(): void {
    this.button.hidden = true;
  }

  public showButton(): void {
    this.button.hidden = false;
  }

  protected abstract success(data: ResponseData): void;
  protected abstract getButtonSelector(): string;
  protected abstract getActionName(): string;
  public abstract checkVisibility(view: PollViews): void;
}

export default Abstract;
