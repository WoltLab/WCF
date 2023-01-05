import { dboAction } from "../../../Ajax";
import UiDropdownSimple from "../../../Ui/Dropdown/Simple";
import * as UiNotification from "../../../Ui/Notification";
import { confirmationFactory } from "../../Confirmation";

export class WoltlabCoreCommentResponseElement extends HTMLElement {
  connectedCallback() {
    if (this.menu) {
      const enableButton = this.menu.querySelector(".commentResponse__option--enable");
      enableButton?.addEventListener("click", (event) => {
        event.preventDefault();
        void this.#enable();
      });

      const deleteButton = this.menu.querySelector(".commentResponse__option--delete");
      deleteButton?.addEventListener("click", (event) => {
        event.preventDefault();
        void this.#delete();
      });
    }
  }

  async #enable(): Promise<void> {
    await dboAction("enable", "wcf\\data\\comment\\response\\CommentResponseAction")
      .objectIds([this.responseId])
      .dispatch();

    this.querySelector<HTMLElement>(".commentResponse__status--disabled")!.hidden = true;
    if (this.menu) {
      this.menu.querySelector<HTMLElement>(".commentResponse__option--enable")!.hidden = true;
    }
  }

  async #delete(): Promise<void> {
    const result = await confirmationFactory().delete("todo");
    if (result) {
      await dboAction("delete", "wcf\\data\\comment\\response\\CommentResponseAction")
        .objectIds([this.responseId])
        .dispatch();

      UiNotification.show();

      this.dispatchEvent(new CustomEvent("delete"));
    }
  }

  get responseId(): number {
    return parseInt(this.getAttribute("response-id")!);
  }

  get menu(): HTMLElement | undefined {
    return UiDropdownSimple.getDropdownMenu(`commentResponseOptions${this.responseId}`);
  }
}

window.customElements.define("woltlab-core-comment-response", WoltlabCoreCommentResponseElement);

export default WoltlabCoreCommentResponseElement;
