/**
 * Handles article trash, restore and delete.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Article/InlineEditor
 */

import * as Ajax from "../../../Ajax";
import { AjaxCallbackSetup, DatabaseObjectActionResponse } from "../../../Ajax/Data";
import * as ControllerClipboard from "../../../Controller/Clipboard";
import * as Core from "../../../Core";
import DomUtil from "../../../Dom/Util";
import * as EventHandler from "../../../Event/Handler";
import * as Language from "../../../Language";
import * as UiConfirmation from "../../../Ui/Confirmation";
import UiDialog from "../../../Ui/Dialog";
import * as UiNotification from "../../../Ui/Notification";

interface InlineEditorOptions {
  i18n: {
    defaultLanguageId: number;
    isI18n: boolean;
    languages: {
      [key: string]: string;
    };
  };
  redirectUrl: string;
}

interface ArticleData {
  buttons: {
    delete: HTMLAnchorElement;
    restore: HTMLAnchorElement;
    trash: HTMLAnchorElement;
  };
  element: HTMLElement | undefined;
  isArticleEdit: boolean;
}

interface ClipboardResponseData {
  objectIDs: number[];
}

interface ClipboardActionData {
  data: {
    actionName: string;
    internalData: {
      template: string;
    };
  };
  responseData: ClipboardResponseData | null;
}

const articles = new Map<number, ArticleData>();

class AcpUiArticleInlineEditor {
  private readonly options: InlineEditorOptions;

  /**
   * Initializes the ACP inline editor for articles.
   */
  constructor(objectId: number, options: InlineEditorOptions) {
    this.options = Core.extend(
      {
        i18n: {
          defaultLanguageId: 0,
          isI18n: false,
          languages: {},
        },
        redirectUrl: "",
      },
      options,
    ) as InlineEditorOptions;

    if (objectId) {
      this.initArticle(undefined, ~~objectId);
    } else {
      document.querySelectorAll(".jsArticleRow").forEach((article: HTMLElement) => this.initArticle(article, 0));

      EventHandler.add("com.woltlab.wcf.clipboard", "com.woltlab.wcf.article", (data) => this.clipboardAction(data));
    }
  }

  /**
   * Reacts to executed clipboard actions.
   */
  private clipboardAction(actionData: ClipboardActionData): void {
    // only consider events if the action has been executed
    if (actionData.responseData !== null) {
      const callbackFunction = new Map([
        ["com.woltlab.wcf.article.delete", (articleId: number) => this.triggerDelete(articleId)],
        ["com.woltlab.wcf.article.publish", (articleId: number) => this.triggerPublish(articleId)],
        ["com.woltlab.wcf.article.restore", (articleId: number) => this.triggerRestore(articleId)],
        ["com.woltlab.wcf.article.trash", (articleId: number) => this.triggerTrash(articleId)],
        ["com.woltlab.wcf.article.unpublish", (articleId: number) => this.triggerUnpublish(articleId)],
      ]);

      const triggerFunction = callbackFunction.get(actionData.data.actionName);
      if (triggerFunction) {
        actionData.responseData.objectIDs.forEach((objectId) => triggerFunction(objectId));

        UiNotification.show();
      }
    } else if (actionData.data.actionName === "com.woltlab.wcf.article.setCategory") {
      const dialog = UiDialog.openStatic("articleCategoryDialog", actionData.data.internalData.template, {
        title: Language.get("wcf.acp.article.setCategory"),
      });

      const submitButton = dialog.content.querySelector("[data-type=submit]") as HTMLButtonElement;
      submitButton.addEventListener("click", (ev) => this.submitSetCategory(ev, dialog.content));
    }
  }

  /**
   * Is called, if the set category dialog form is submitted.
   */
  private submitSetCategory(event: MouseEvent, content: HTMLElement): void {
    event.preventDefault();

    const innerError = content.querySelector(".innerError");
    const select = content.querySelector("select[name=categoryID]") as HTMLSelectElement;

    const categoryId = ~~select.value;
    if (categoryId) {
      Ajax.api(this, {
        actionName: "setCategory",
        parameters: {
          categoryID: categoryId,
          useMarkedArticles: true,
        },
      });

      if (innerError) {
        innerError.remove();
      }

      UiDialog.close("articleCategoryDialog");
    } else if (!innerError) {
      DomUtil.innerError(select, Language.get("wcf.global.form.error.empty"));
    }
  }

  /**
   * Initializes an article row element.
   */
  private initArticle(article: HTMLElement | undefined, objectId: number): void {
    let isArticleEdit = false;
    if (!article && ~~objectId > 0) {
      isArticleEdit = true;
      article = undefined;
    } else {
      objectId = ~~article!.dataset.objectId!;
    }

    const scope = article || document;

    const buttonDelete = scope.querySelector(".jsButtonDelete") as HTMLAnchorElement;
    buttonDelete.addEventListener("click", (ev) => this.prompt(ev, objectId, "delete"));

    const buttonRestore = scope.querySelector(".jsButtonRestore") as HTMLAnchorElement;
    buttonRestore.addEventListener("click", (ev) => this.prompt(ev, objectId, "restore"));

    const buttonTrash = scope.querySelector(".jsButtonTrash") as HTMLAnchorElement;
    buttonTrash.addEventListener("click", (ev) => this.prompt(ev, objectId, "trash"));

    if (isArticleEdit) {
      const buttonToggleI18n = scope.querySelector(".jsButtonToggleI18n") as HTMLAnchorElement;
      if (buttonToggleI18n !== null) {
        buttonToggleI18n.addEventListener("click", (ev) => this.toggleI18n(ev, objectId));
      }
    }

    articles.set(objectId, {
      buttons: {
        delete: buttonDelete,
        restore: buttonRestore,
        trash: buttonTrash,
      },
      element: article,
      isArticleEdit: isArticleEdit,
    });
  }

  /**
   * Prompts a user to confirm the clicked action before executing it.
   */
  private prompt(event: MouseEvent, objectId: number, actionName: string): void {
    event.preventDefault();

    const article = articles.get(objectId)!;

    UiConfirmation.show({
      confirm: () => {
        this.invoke(objectId, actionName);
      },
      message: article.buttons[actionName].dataset.confirmMessageHtml,
      messageIsHtml: true,
    });
  }

  /**
   * Toggles an article between i18n and monolingual.
   */
  private toggleI18n(event: MouseEvent, objectId: number): void {
    event.preventDefault();

    const phrase = Language.get(
      "wcf.acp.article.i18n." + (this.options.i18n.isI18n ? "fromI18n" : "toI18n") + ".confirmMessage",
    );
    let html = `<p>${phrase}</p>`;

    // build language selection
    if (this.options.i18n.isI18n) {
      html += `<dl><dt>${Language.get("wcf.acp.article.i18n.source")}</dt><dd>`;

      const defaultLanguageId = this.options.i18n.defaultLanguageId.toString();
      html += Object.entries(this.options.i18n.languages)
        .map(([languageId, languageName]) => {
          return `<label><input type="radio" name="i18nLanguage" value="${languageId}" ${
            defaultLanguageId === languageId ? "checked" : ""
          }> ${languageName}</label>`;
        })
        .join("");
      html += "</dd></dl>";
    }

    UiConfirmation.show({
      confirm: (parameters, content) => {
        let languageId = 0;
        if (this.options.i18n.isI18n) {
          const input = content.parentElement!.querySelector("input[name='i18nLanguage']:checked") as HTMLInputElement;
          languageId = ~~input.value;
        }

        Ajax.api(this, {
          actionName: "toggleI18n",
          objectIDs: [objectId],
          parameters: {
            languageID: languageId,
          },
        });
      },
      message: html,
      messageIsHtml: true,
    });
  }

  /**
   * Invokes the selected action.
   */
  private invoke(objectId: number, actionName: string): void {
    Ajax.api(this, {
      actionName: actionName,
      objectIDs: [objectId],
    });
  }

  /**
   * Handles an article being deleted.
   */
  private triggerDelete(articleId: number): void {
    const article = articles.get(articleId);
    if (!article) {
      // The affected article might be hidden by the filter settings.
      return;
    }

    if (article.isArticleEdit) {
      window.location.href = this.options.redirectUrl;
    } else {
      const tbody = article.element!.parentElement!;
      article.element!.remove();

      if (tbody.querySelector("tr") === null) {
        window.location.reload();
      }
    }
  }

  /**
   * Handles publishing an article via clipboard.
   */
  private triggerPublish(articleId: number): void {
    const article = articles.get(articleId);
    if (!article) {
      // The affected article might be hidden by the filter settings.
      return;
    }

    if (article.isArticleEdit) {
      // unsupported
    } else {
      const notice = article.element!.querySelector(".jsUnpublishedArticle")!;
      notice.remove();
    }
  }

  /**
   * Handles an article being restored.
   */
  private triggerRestore(articleId: number): void {
    const article = articles.get(articleId);
    if (!article) {
      // The affected article might be hidden by the filter settings.
      return;
    }

    DomUtil.hide(article.buttons.delete);
    DomUtil.hide(article.buttons.restore);
    DomUtil.show(article.buttons.trash);

    if (article.isArticleEdit) {
      const notice = document.querySelector(".jsArticleNoticeTrash") as HTMLElement;
      DomUtil.hide(notice);
    } else {
      const icon = article.element!.querySelector(".jsIconDeleted")!;
      icon.remove();
    }
  }

  /**
   * Handles an article being trashed.
   */
  private triggerTrash(articleId: number): void {
    const article = articles.get(articleId);
    if (!article) {
      // The affected article might be hidden by the filter settings.
      return;
    }

    DomUtil.show(article.buttons.delete);
    DomUtil.show(article.buttons.restore);
    DomUtil.hide(article.buttons.trash);

    if (article.isArticleEdit) {
      const notice = document.querySelector(".jsArticleNoticeTrash") as HTMLElement;
      DomUtil.show(notice);
    } else {
      const badge = document.createElement("span");
      badge.className = "badge label red jsIconDeleted";
      badge.textContent = Language.get("wcf.message.status.deleted");

      const h3 = article.element!.querySelector(".containerHeadline > h3") as HTMLHeadingElement;
      h3.insertAdjacentElement("afterbegin", badge);
    }
  }

  /**
   * Handles unpublishing an article via clipboard.
   */
  private triggerUnpublish(articleId: number): void {
    const article = articles.get(articleId);
    if (!article) {
      // The affected article might be hidden by the filter settings.
      return;
    }

    if (article.isArticleEdit) {
      // unsupported
    } else {
      const badge = document.createElement("span");
      badge.className = "badge jsUnpublishedArticle";
      badge.textContent = Language.get("wcf.acp.article.publicationStatus.unpublished");

      const h3 = article.element!.querySelector(".containerHeadline > h3") as HTMLHeadingElement;
      const a = h3.querySelector("a");

      h3.insertBefore(badge, a);
      h3.insertBefore(document.createTextNode(" "), a);
    }
  }

  _ajaxSuccess(data: DatabaseObjectActionResponse): void {
    let notificationCallback;

    switch (data.actionName) {
      case "delete":
        this.triggerDelete(data.objectIDs[0]);
        break;

      case "restore":
        this.triggerRestore(data.objectIDs[0]);
        break;

      case "setCategory":
      case "toggleI18n":
        notificationCallback = () => window.location.reload();
        break;

      case "trash":
        this.triggerTrash(data.objectIDs[0]);
        break;
    }

    UiNotification.show(undefined, notificationCallback);
    ControllerClipboard.reload();
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        className: "wcf\\data\\article\\ArticleAction",
      },
    };
  }
}

Core.enableLegacyInheritance(AcpUiArticleInlineEditor);

export = AcpUiArticleInlineEditor;
