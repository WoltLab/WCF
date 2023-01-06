/**
 * Handles the comment list.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Component/Comment/Handler
 * @since 6.0
 */

import { dboAction } from "../../Ajax";
import DomChangeListener from "../../Dom/Change/Listener";
import DomUtil from "../../Dom/Util";
import { wheneverFirstSeen } from "../../Helper/Selector";
import { getPhrase } from "../../Language";
import { CommentAdd } from "./Add";
import { CommentResponseAdd } from "./Response/Add";
import * as UiScroll from "../../Ui/Scroll";
import WoltlabCoreCommentElement from "./woltlab-core-comment";
import WoltlabCoreCommentResponseElement from "./Response/woltlab-core-comment-response";

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

class CommentHandler {
  readonly #container: HTMLElement;
  #commentResponseAdd: CommentResponseAdd;

  constructor(container: HTMLElement) {
    this.#container = container;

    this.#initComments();
    this.#initResponses();
    this.#initLoadNextComments();
    this.#initCommentAdd();
    this.#initHashHandling();
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
      const comment = this.#container.querySelector<HTMLElement>(`.comment[data-comment-id="${matches[1]}"]`);
      if (comment) {
        if (matches[2]) {
          const response = this.#container.querySelector<HTMLElement>(
            `.commentResponse[data-response-id="${matches[2]}"]`,
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

    permaLinkComment = document.createElement("li");
    permaLinkComment.classList.add("commentPermalink", "commentPermalink--loading");
    permaLinkComment.innerHTML = '<fa-icon size="48" name="spinner" solid></fa-icon>';
    this.#container.querySelector(".commentList")?.prepend(permaLinkComment);

    const response = (await dboAction("loadComment", "wcf\\data\\comment\\CommentAction")
      .objectIds([commentId])
      .payload({
        responseID: responseId,
      })
      .dispatch()) as ResponseLoadComment;

    if (response.template === "") {
      permaLinkComment.remove();

      // comment id is invalid or there is a mismatch, silently ignore it
      return;
    }

    DomUtil.insertHtml(response.template, permaLinkComment, "before");
    permaLinkComment.remove();
    const comment = this.#container.querySelector<HTMLElement>(`.comment[data-comment-id="${commentId}"]`)!;
    comment.classList.add("commentPermalink");

    if (response.response) {
      const permalinkResponse = document.createElement("li");
      permalinkResponse.classList.add("commentResponsePermalink", "commentResponsePermalink--loading");
      permalinkResponse.innerHTML = '<fa-icon size="32" name="spinner" solid></fa-icon>';
      comment.querySelector(".commentResponseList")!.prepend(permalinkResponse);

      this.#insertResponseSegment(response.response);
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

    permalinkResponse = document.createElement("li");
    permalinkResponse.classList.add("commentResponsePermalink", "commentResponsePermalink--loading");
    permalinkResponse.innerHTML = '<fa-icon size="32" name="spinner" solid></fa-icon>';
    comment.querySelector(".commentResponseList")!.prepend(permalinkResponse);

    const response = (await dboAction("loadResponse", "wcf\\data\\comment\\CommentAction")
      .payload({
        responseID: responseId,
      })
      .dispatch()) as ResponseLoadResponse;

    if (response.template === "") {
      permalinkResponse.remove();

      // id is invalid or there is a mismatch, silently ignore it
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
      if (!this.#container.contains(element)) {
        return;
      }

      element.addEventListener("reply", () => {
        this.#showAddResponse(element.parentElement!, element.commentId);
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

    fragment.querySelectorAll<HTMLElement>(".comment").forEach((element) => {
      this.#container.querySelector(`.comment[data-comment-id="${element.dataset.commentId!}"]`)?.remove();
    });

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
