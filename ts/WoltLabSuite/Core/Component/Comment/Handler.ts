import { dboAction } from "../../Ajax";
import DomUtil from "../../Dom/Util";
import { getPhrase } from "../../Language";
import { CommentAdd } from "./Add";

type ResponseLoadComments = {
  lastCommentTime: number;
  template: string;
};

class CommentHandler {
  readonly #container: HTMLElement;

  constructor(container: HTMLElement) {
    this.#container = container;

    this.#initComments();
    this.#initLoadNextComments();
    this.#initCommentAdd();
  }

  #initCommentAdd(): void {
    if (this.#container.dataset.canAdd) {
      new CommentAdd(this.#container.querySelector(".commentAdd")!);
    }
  }

  #initComments(): void {}

  #initLoadNextComments(): void {
    if (this.#displayedComments < this.#totalComments) {
      if (!this.#container.querySelector(".commentLoadNext")) {
        const li = document.createElement("li");
        li.classList.add("commentLoadNext", "showMore");
        this.#container.append(li);

        const button = document.createElement("button");
        button.type = "button";
        button.classList.add("button", "small", "commentLoadNext__button");
        button.textContent = getPhrase("wcf.comment.more");
        li.append(button);

        button.addEventListener("click", () => {
          void this.#loadNextComments();
        });
      }
    }
  }

  async #loadNextComments(): Promise<void> {
    const button = this.#container.querySelector<HTMLButtonElement>(".commentLoadNext__button")!;
    button.disabled = true;

    const response = (await dboAction("loadComments", "wcf\\data\\comment\\CommentAction")
      .payload({
        data: {
          objectID: this.#container.dataset.objectId,
          objectTypeID: this.#container.dataset.objectTypeId,
          lastCommentTime: this.#container.dataset.lastCommentTime,
        },
      })
      .dispatch()) as ResponseLoadComments;

    const fragment = DomUtil.createFragmentFromHtml(response.template);
    this.#container.insertBefore(fragment, this.#container.querySelector(".commentLoadNext"));

    this.#container.dataset.lastCommentTime = response.lastCommentTime.toString();

    if (this.#displayedComments < this.#totalComments) {
      button.disabled = false;
    } else {
      this.#container.querySelector<HTMLElement>(".commentLoadNext")!.hidden = true;
    }

    this.#initComments();
  }

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

    const replyButton = comment.querySelector(".jsCommentReplyButton");
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

  get #displayedComments(): number {
    return this.#container.querySelectorAll(".comment").length;
  }

  get #totalComments(): number {
    return parseInt(this.#container.dataset.comments!);
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
