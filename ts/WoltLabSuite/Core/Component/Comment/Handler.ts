import { dboAction } from "../../Ajax";
import { CommentAdd } from "./Add";

class CommentHandler {
  readonly #container: HTMLElement;

  constructor(container: HTMLElement) {
    this.#container = container;

    this.#initComments();
    this.#initCommentAdd();
  }

  #initCommentAdd(): void {
    if (this.#container.dataset.canAdd) {
      new CommentAdd(this.#container.querySelector(".commentAdd")!);
    }
  }

  #initComments(): void {}

  #initComment(commentId: number, comment: HTMLElement) {
    /*if (this._container.data('canAdd')) {
			this._initAddResponse(commentID, comment);
		}*/

    const enableButton = comment.querySelector(".jsCommentEnableButton");
    if (enableButton) {
      enableButton.addEventListener("click", (event) => {
        event.preventDefault();
        void this.#enableComment(comment);
      });
    }

    const deleteButton = comment.querySelector(".jsCommentDeleteButton");
    if (deleteButton) {
      deleteButton.addEventListener("click", (event) => {
        event.preventDefault();
      });
    }

    const replyButton = comment.querySelector('.jsCommentReplyButton');
    if (replyButton) {
      replyButton.addEventListener("click", (event) => {
        //this._showAddResponse();
      });
    }
  }

  async #enableComment(comment: HTMLElement): Promise<void> {
    await dboAction("enable", "wcf\\data\\comment\\CommentAction")
      .objectIds([parseInt(comment.dataset.objectId!)])
      .dispatch();

    comment.dataset.isDisabled = "";
    comment.querySelector<HTMLElement>(".jsIconDisabled")!.hidden = true;
    comment.querySelector<HTMLElement>(".jsCommentEnableButton")!.hidden = true;
  }
}

export function setup(elementId: string): void {
  const element = document.getElementById(elementId);
  if (!element) {
    console.debug(`[Comment.Handler] Unable to find container identified by '${elementId}'`);
    return;
  }

  new CommentHandler(element);
}
