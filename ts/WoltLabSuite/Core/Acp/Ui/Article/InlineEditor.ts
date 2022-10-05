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
import { confirmationFactory } from "../../../Component/Confirmation";
import * as ControllerClipboard from "../../../Controller/Clipboard";
import * as Core from "../../../Core";
import DomUtil from "../../../Dom/Util";
import * as EventHandler from "../../../Event/Handler";
import * as Language from "../../../Language";
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
    delete: HTMLButtonElement;
    restore: HTMLButtonElement;
    trash: HTMLButtonElement;
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
      this.initArticle(undefined, objectId);
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

    const categoryId = parseInt(select.value);
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
    if (!article && objectId > 0) {
      isArticleEdit = true;
      article = undefined;
    } else {
      objectId = parseInt(article!.dataset.objectId!);
    }

    const scope = article || document;

    let title: string;
    if (isArticleEdit) {
      const languageId = this.options.i18n.isI18n ? this.options.i18n.defaultLanguageId : 0;
      const inputField = document.getElementById(`title${languageId}`) as HTMLInputElement;
      title = inputField.value;
    }

    const buttonDelete = scope.querySelector(".jsButtonDelete") as HTMLButtonElement;
    buttonDelete.addEventListener("click", async () => {
      const result = await confirmationFactory().delete(title);
      if (result) {
        this.invoke(objectId, "delete");
      }
    });

    const buttonRestore = scope.querySelector(".jsButtonRestore") as HTMLButtonElement;
    buttonRestore.addEventListener("click", async () => {
      const result = await confirmationFactory().restore(title);
      if (result) {
        this.invoke(objectId, "restore");
      }
    });

    const buttonTrash = scope.querySelector(".jsButtonTrash") as HTMLButtonElement;
    buttonTrash.addEventListener("click", async () => {
      const result = await confirmationFactory().softDelete(title, false);

      if (result) {
        this.invoke(objectId, "trash");
      }
    });

    if (isArticleEdit) {
      const buttonToggleI18n = scope.querySelector(".jsButtonToggleI18n") as HTMLButtonElement;
      if (buttonToggleI18n !== null) {
        buttonToggleI18n.addEventListener("click", () => void this.toggleI18n(objectId));
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
   * Toggles an article between i18n and monolingual.
   */
  private async toggleI18n(objectId: number): Promise<void> {
    const phraseType = this.options.i18n.isI18n ? "convertFromI18n" : "convertToI18n";
    const phrase = Language.get(`wcf.article.${phraseType}.question`);

    let dl: HTMLDListElement | undefined;
    if (this.options.i18n.isI18n) {
      const defaultLanguageId = this.options.i18n.defaultLanguageId.toString();
      const html = Object.entries(this.options.i18n.languages)
        .map(([languageId, languageName]) => {
          return `<label><input type="radio" name="i18nLanguage" value="${languageId}" ${
            defaultLanguageId === languageId ? "checked" : ""
          }> ${languageName}</label>`;
        })
        .join("");

      dl = document.createElement("dl");
      dl.innerHTML = `
        <dt>${Language.get("wcf.acp.article.i18n.source")}</dt>
        <dd>${html}</dd>
      `;
    }

    const { result } = await confirmationFactory()
      .custom(phrase)
      .withFormElements((dialog) => {
        const p = document.createElement("p");
        p.innerHTML = Language.get(`wcf.article.${phraseType}.description`);
        dialog.content.append(p);

        if (dl !== undefined) {
          dialog.content.append(dl);
        }
      });

    if (result) {
      let languageId = 0;
      if (dl !== undefined) {
        const input = dl.querySelector("input[name='i18nLanguage']:checked") as HTMLInputElement;
        languageId = parseInt(input.value);
      }

      Ajax.api(this, {
        actionName: "toggleI18n",
        objectIDs: [objectId],
        parameters: {
          languageID: languageId,
        },
      });
    }
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
