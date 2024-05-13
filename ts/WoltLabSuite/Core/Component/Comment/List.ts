/**
 * Handles the comment list.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import { dboAction, handleValidationErrors } from "../../Ajax";
import DomChangeListener from "../../Dom/Change/Listener";
import DomUtil from "../../Dom/Util";
import { wheneverFirstSeen } from "../../Helper/Selector";
import { getPhrase } from "../../Language";
import { CommentAdd } from "./Add";
import { CommentResponseAdd } from "./Response/Add";
import * as UiScroll from "../../Ui/Scroll";
import UiReactionHandler from "../../Ui/Reaction/Handler";

import type WoltlabCoreCommentElement from "./woltlab-core-comment";
import type WoltlabCoreCommentResponseElement from "./Response/woltlab-core-comment-response";

type ResponseLoadComments = {
  lastCommentTime: number;
  template: string;
};

type ResponseLoadResponses = {
  lastResponseTime: number;
  lastResponseID: number;
  template: string;
};

type ResponseLoadComment = {
  template: string;
  response?: string;
};

type ResponseLoadResponse = {
  template: string;
};

class CommentList {
  readonly #container: HTMLElement;
  #commentResponseAdd: CommentResponseAdd;

  constructor(container: HTMLElement) {
    this.#container = container;

    this.#initComments();
    this.#initResponses();
    this.#initLoadNextComments();
    this.#initCommentAdd();
    this.#initHashHandling();
    this.#initReactions();
  }

  #initReactions(): void {
    if (this.#container.dataset.enableReactions !== "true") {
      return;
    }

    new UiReactionHandler("com.woltlab.wcf.comment", {
      containerSelector: `#${this.#container.id} .commentList__item`,
      buttonSelector: ".comment__button--react",
    });

    new UiReactionHandler("com.woltlab.wcf.comment.response", {
      containerSelector: `#${this.#container.id} .commentResponseList__item`,
      buttonSelector: ".commentResponse__button--react",
    });
  }

  #initHashHandling(): void {
    window.addEventListener("hashchange", () => {
      this.#handleHashChange();
    });

    this.#handleHashChange();
  }

  #handleHashChange(): void {
    const matches = window.location.hash.match(/^#(?:[^/]+\/)?comment(\d+)(?:\/response(\d+))?/);
    if (matches) {
      const comment = this.#container.querySelector<HTMLElement>(`.commentList__item[data-comment-id="${matches[1]}"]`);
      if (comment) {
        if (matches[2]) {
          const response = this.#container.querySelector<HTMLElement>(
            `.commentResponseList__item[data-response-id="${matches[2]}"]`,
          );
          if (response) {
            this.#scrollTo(response, true);
          } else {
            void this.#loadResponseSegment(comment, parseInt(matches[2]));
          }
        } else {
          this.#scrollTo(comment, true);
        }
      } else {
        void this.#loadCommentSegment(parseInt(matches[1]), matches[2] ? parseInt(matches[2]) : 0);
      }
    }
  }

  async #loadCommentSegment(commentId: number, responseId?: number): Promise<void> {
    let permaLinkComment = this.#container.querySelector(".commentPermalink");
    if (permaLinkComment) {
      permaLinkComment.remove();
    }

    permaLinkComment = document.createElement("div");
    permaLinkComment.classList.add("commentList__item", "commentPermalink", "commentPermalink--loading");
    permaLinkComment.innerHTML =
      '<woltlab-core-loading-indicator size="48" hide-text></woltlab-core-loading-indicator>';
    this.#container.querySelector(".commentList")?.prepend(permaLinkComment);

    let ajaxResponse: ResponseLoadComment;
    try {
      ajaxResponse = (await dboAction("loadComment", "wcf\\data\\comment\\CommentAction")
        .objectIds([commentId])
        .payload({
          responseID: responseId,
          objectTypeID: this.#container.dataset.objectTypeId,
        })
        .dispatch()) as ResponseLoadComment;
    } catch (error) {
      await handleValidationErrors(error, () => {
        // The comment id is invalid or there is a mismatch, silently ignore it.
        permaLinkComment!.remove();

        return true;
      });

      return;
    }

    const { template, response } = ajaxResponse;

    DomUtil.insertHtml(template, permaLinkComment, "before");
    permaLinkComment.remove();
    const comment = this.#container.querySelector<HTMLElement>(`.commentList__item[data-comment-id="${commentId}"]`)!;
    comment.classList.add("commentPermalink");

    if (response) {
      const permalinkResponse = document.createElement("div");
      permalinkResponse.classList.add(
        "commentResponseList__item",
        "commentResponsePermalink",
        "commentResponsePermalink--loading",
      );
      permalinkResponse.innerHTML =
        '<woltlab-core-loading-indicator size="32" hide-text></woltlab-core-loading-indicator>';
      comment.querySelector(".commentResponseList")!.prepend(permalinkResponse);

      this.#insertResponseSegment(response);
    } else {
      this.#scrollTo(comment, true);
    }
  }

  #insertResponseSegment(template: string): void {
    const permalinkResponse = this.#container.querySelector(".commentResponsePermalink")!;
    DomUtil.insertHtml(template, permalinkResponse, "before");
    const response = permalinkResponse.previousElementSibling as HTMLElement;
    permalinkResponse.classList.add("commentResponsePermalink");
    permalinkResponse.remove();

    this.#scrollTo(response, true);
  }

  async #loadResponseSegment(comment: HTMLElement, responseId: number): Promise<void> {
    let permalinkResponse = comment.querySelector(".commentResponsePermalink");
    if (permalinkResponse) {
      permalinkResponse.remove();
    }

    permalinkResponse = document.createElement("div");
    permalinkResponse.classList.add(
      "commentResponseList__item",
      "commentResponsePermalink",
      "commentResponsePermalink--loading",
    );
    permalinkResponse.innerHTML =
      '<woltlab-core-loading-indicator size="32" hide-text></woltlab-core-loading-indicator>';
    comment.querySelector(".commentResponseList")!.prepend(permalinkResponse);

    let response: ResponseLoadResponse;
    try {
      response = (await dboAction("loadResponse", "wcf\\data\\comment\\CommentAction")
        .payload({
          responseID: responseId,
          objectTypeID: this.#container.dataset.objectTypeId,
        })
        .dispatch()) as ResponseLoadResponse;
    } catch (error) {
      await handleValidationErrors(error, () => {
        // The response id is invalid or there is a mismatch, silently ignore it.
        permalinkResponse!.remove();

        return true;
      });

      return;
    }

    this.#insertResponseSegment(response.template);
  }

  #scrollTo(element: HTMLElement, highlight = false): void {
    UiScroll.element(element, () => {
      if (highlight) {
        if (element.classList.contains("comment__highlight__target")) {
          element.classList.remove("comment__highlight__target");
        }

        element.classList.add("comment__highlight__target");
      }
    });
  }

  #initCommentAdd(): void {
    if (this.#container.dataset.canAdd === "true") {
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
      if (!this.#container.contains(element)) {
        return;
      }

      element.addEventListener("reply", () => {
        this.#showAddResponse(element, element.commentId);
      });

      element.addEventListener("delete", () => {
        element.parentElement?.remove();
      });

      this.#initLoadNextResponses(element.parentElement!);
    });
  }

  #initResponses(): void {
    wheneverFirstSeen("woltlab-core-comment-response", (element: WoltlabCoreCommentResponseElement) => {
      if (!this.#container.contains(element)) {
        return;
      }

      element.addEventListener("delete", () => {
        element.parentElement?.remove();
      });
    });
  }

  #initLoadNextResponses(comment: HTMLElement): void {
    const displayedResponses = comment.querySelectorAll(".commentResponse").length;
    const responses = parseInt(comment.dataset.responses!);

    if (displayedResponses < responses) {
      const phrase = getPhrase("wcf.comment.response.more", { count: responses - displayedResponses });

      if (!comment.querySelector(".commentLoadNextResponses")) {
        const item = document.createElement("div");
        item.classList.add("commentResponseList__item", "commentLoadNextResponses");
        comment.querySelector(".commentResponseList")!.append(item);

        const button = document.createElement("button");
        button.type = "button";
        button.classList.add("button", "small", "commentLoadNextResponses__button");
        button.textContent = phrase;
        item.append(button);

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

    fragment.querySelectorAll<HTMLElement>(".commentResponseList__item").forEach((element) => {
      comment.querySelector(`.commentResponseList__item[data-response-id="${element.dataset.responseId!}"]`)?.remove();
    });

    comment
      .querySelector(".commentResponseList")!
      .insertBefore(fragment, comment.querySelector(".commentLoadNextResponses"));
    DomChangeListener.trigger();

    comment.dataset.lastResponseTime = response.lastResponseTime.toString();
    comment.dataset.lastResponseId = response.lastResponseID.toString();

    this.#initLoadNextResponses(comment);
  }

  #initLoadNextComments(): void {
    if (this.#displayedComments < this.#totalComments) {
      if (!this.#container.querySelector(".commentLoadNext")) {
        const div = document.createElement("div");
        div.classList.add("commentList__item", "commentLoadNext");
        this.#container.querySelector(".commentList")!.append(div);

        const button = document.createElement("button");
        button.type = "button";
        button.classList.add("button", "small", "commentLoadNext__button");
        button.textContent = getPhrase("wcf.comment.more");
        div.append(button);

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

    fragment.querySelectorAll<HTMLElement>(".commentList__item").forEach((element) => {
      this.#container.querySelector(`.commentList__item[data-comment-id="${element.dataset.commentId!}"]`)?.remove();
    });

    this.#container
      .querySelector(".commentList")!
      .insertBefore(fragment, this.#container.querySelector(".commentLoadNext"));
    DomChangeListener.trigger();

    this.#container.dataset.lastCommentTime = response.lastCommentTime.toString();

    if (this.#displayedComments < this.#totalComments) {
      button.disabled = false;
    } else {
      this.#container.querySelector<HTMLElement>(".commentLoadNext")!.hidden = true;
    }
  }

  #showAddResponse(comment: WoltlabCoreCommentElement, commentId: number): void {
    comment.parentElement!.append(this.#commentResponseAdd.container);

    this.#commentResponseAdd.show(commentId);
  }

  #insertComment(template: string): void {
    const referenceElement = this.#container.querySelector(".commentAdd")!.parentElement!;
    DomUtil.insertHtml(template, referenceElement, "after");
    DomChangeListener.trigger();

    const scrollTarget = referenceElement.nextElementSibling as HTMLElement;

    window.setTimeout(() => {
      UiScroll.element(scrollTarget);
    }, 100);
  }

  #insertResponse(commentId: number, template: string): void {
    const item = this.#container.querySelector(`.commentList__item[data-comment-id="${commentId}"]`)!;
    let commentResponseList = item.querySelector<HTMLElement>(".commentResponseList");
    if (!commentResponseList) {
      const div = document.createElement("div");
      div.classList.add("comment__responses");
      item.append(div);

      commentResponseList = document.createElement("div");
      commentResponseList.classList.add("commentResponseList");
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
    console.debug(`Unable to find the container identified by '${elementId}'`);
    return;
  }

  new CommentList(element);
}
