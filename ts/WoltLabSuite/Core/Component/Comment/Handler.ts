import { dboAction } from "../../Ajax";
import DomChangeListener from "../../Dom/Change/Listener";
import DomUtil from "../../Dom/Util";
import { wheneverFirstSeen } from "../../Helper/Selector";
import { getPhrase } from "../../Language";
import { CommentAdd } from "./Add";
import { CommentResponseAdd } from "./Response/Add";
import * as UiScroll from "../../Ui/Scroll";
import WoltlabCoreCommentElement from "./woltlab-core-comment";

type ResponseLoadComments = {
  lastCommentTime: number;
  template: string;
};

type ResponseLoadResponses = {
  lastResponseTime: number;
  lastResponseID: number;
  template: string;
};

class CommentHandler {
  readonly #container: HTMLElement;
  #commentResponseAdd: CommentResponseAdd;

  constructor(container: HTMLElement) {
    this.#container = container;

    this.#initComments();
    this.#initLoadNextComments();
    this.#initCommentAdd();
  }

  #initCommentAdd(): void {
    if (this.#container.dataset.canAdd) {
      new CommentAdd(
        this.#container.querySelector(".commentAdd")!,
        parseInt(this.#container.dataset.objectTypeId!),
        parseInt(this.#container.dataset.objectId!),
        (template: string) => {
          this.#insertComment(template);
        },
      );
      this.#commentResponseAdd = new CommentResponseAdd(
        this.#container.querySelector(".commentResponseAdd")!,
        (commentId, template) => {
          this.#insertResponse(commentId, template);
        },
      );
    }
  }

  #initComments(): void {
    wheneverFirstSeen("woltlab-core-comment", (element: WoltlabCoreCommentElement) => {
      element.addEventListener("reply", () => {
        this.#showAddResponse(element.parentElement!, element.commentId);
      });

      element.addEventListener("delete", () => {
        element.parentElement?.remove();
      });

      this.#initLoadNextResponses(element.parentElement!);
    });
  }

  #initLoadNextResponses(comment: HTMLElement): void {
    const displayedResponses = comment.querySelectorAll(".commentResponse").length;
    const responses = parseInt(comment.dataset.responses!);

    if (displayedResponses < responses) {
      const phrase = getPhrase("wcf.comment.response.more", { count: responses - displayedResponses });

      if (!comment.querySelector(".commentLoadNextResponses")) {
        const li = document.createElement("li");
        li.classList.add("commentLoadNextResponses");
        comment.querySelector(".commentResponseList")!.append(li);

        const button = document.createElement("button");
        button.type = "button";
        button.classList.add("button", "small", "commentLoadNextResponses__button");
        button.textContent = phrase;
        li.append(button);

        button.addEventListener("click", () => {
          void this.#loadNextResponses(comment);
        });
      } else {
        comment.querySelector(".commentLoadNextResponses__button")!.textContent = phrase;
      }
    } else {
      comment.querySelector(".commentLoadNextResponses")?.remove();
    }
  }

  async #loadNextResponses(comment: HTMLElement, loadAllResponses = false): Promise<void> {
    const button = comment.querySelector<HTMLButtonElement>(".commentLoadNextResponses__button")!;
    button.disabled = true;

    const response = (await dboAction("loadResponses", "wcf\\data\\comment\\response\\CommentResponseAction")
      .payload({
        data: {
          commentID: comment.dataset.commentId,
          lastResponseTime: comment.dataset.lastResponseTime,
          lastResponseID: comment.dataset.lastResponseId,
          loadAllResponses: loadAllResponses ? 1 : 0,
        },
      })
      .dispatch()) as ResponseLoadResponses;

    const fragment = DomUtil.createFragmentFromHtml(response.template);

    fragment.querySelectorAll<HTMLElement>(".commentResponse").forEach((element) => {
      comment.querySelector(`.commentResponse[data-response-id="${element.dataset.responseId!}"]`)?.remove();
    });

    comment
      .querySelector(".commentResponseList")!
      .insertBefore(fragment, this.#container.querySelector(".commentLoadNextResponses"));

    comment.dataset.lastResponseTime = response.lastResponseTime.toString();
    comment.dataset.lastResponseId = response.lastResponseID.toString();

    this.#initLoadNextResponses(comment);
  }

  #initLoadNextComments(): void {
    if (this.#displayedComments < this.#totalComments) {
      if (!this.#container.querySelector(".commentLoadNext")) {
        const li = document.createElement("li");
        li.classList.add("commentLoadNext", "showMore");
        this.#container.querySelector(".commentList")!.append(li);

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
    this.#container
      .querySelector(".commentList")!
      .insertBefore(fragment, this.#container.querySelector(".commentLoadNext"));

    this.#container.dataset.lastCommentTime = response.lastCommentTime.toString();

    if (this.#displayedComments < this.#totalComments) {
      button.disabled = false;
    } else {
      this.#container.querySelector<HTMLElement>(".commentLoadNext")!.hidden = true;
    }
  }

  #showAddResponse(container: HTMLElement, commentId: number): void {
    container.append(this.#container.querySelector<HTMLElement>(".commentResponseAdd")!);
    this.#commentResponseAdd.show(commentId);
  }

  #insertComment(template: string): void {
    DomUtil.insertHtml(template, this.#container.querySelector(".commentAdd")!, "after");
    DomChangeListener.trigger();

    const scrollTarget = this.#container.querySelector(".commentAdd")!.nextElementSibling as HTMLElement;

    window.setTimeout(() => {
      UiScroll.element(scrollTarget);
    }, 100);
  }

  #insertResponse(commentId: number, template: string): void {
    const li = this.#container.querySelector(`.comment[data-comment-id="${commentId}"]`)!;
    let commentResponseList = li.querySelector<HTMLElement>(".commentResponseList");
    if (!commentResponseList) {
      const div = document.createElement("div");
      div.classList.add("comment__responses");
      li.append(div);

      commentResponseList = document.createElement("ul");
      commentResponseList.classList.add("containerList", "commentResponseList");
      commentResponseList.dataset.responses = "1";
      div.append(commentResponseList);
    }

    DomUtil.insertHtml(template, commentResponseList, "append");
    DomChangeListener.trigger();

    const scrollTarget = commentResponseList.firstElementChild as HTMLElement;

    window.setTimeout(() => {
      UiScroll.element(scrollTarget);
    }, 100);
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
