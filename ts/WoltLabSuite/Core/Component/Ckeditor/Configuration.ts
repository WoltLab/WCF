/**
 * Helper class to construct the CKEditor configuration.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import { getPhrase } from "../../Language";

import type { EditorConfig } from "./Types";

// The typings for CKEditor’s toolbar are outdated.
type ToolbarItem = string | { label: string; icon?: string; items: string[] };
type ToolbarConfig = ToolbarItem[];

class ConfigurationBuilder {
  readonly #features: Features;
  readonly #divider = "|";
  readonly #removePlugins: string[] = [];
  readonly #toolbar: ToolbarConfig = [];
  readonly #toolbarGroups: Record<string, { icon: string; label: string }> = {};

  constructor(features: Features) {
    this.#features = features;
  }

  #setupHeading(): void {
    if (this.#features.heading) {
      this.#toolbar.push("heading");
    } else {
      this.#removePlugins.push("Heading");
    }
  }

  #setupBasicFormat(): void {
    this.#toolbar.push("bold", "italic");
  }

  #setupTextFormat(): void {
    const items: string[] = [];
    if (this.#features.underline) {
      items.push("underline");
    } else {
      this.#removePlugins.push("Underline");
    }

    if (this.#features.strikethrough) {
      items.push("strikethrough");
    } else {
      this.#removePlugins.push("Strikethrough");
    }

    if (this.#features.subscript) {
      items.push("subscript");
    } else {
      this.#removePlugins.push("Subscript");
    }

    if (this.#features.superscript) {
      items.push("superscript");
    } else {
      this.#removePlugins.push("Superscript");
    }

    if (this.#features.mark) {
      items.push("highlight");
    } else {
      this.#removePlugins.push("Highlight");
    }

    if (this.#features.fontColor) {
      items.push("fontColor");
    } else {
      this.#removePlugins.push("FontColor");
    }

    if (this.#features.fontFamily) {
      items.push("fontFamily");
    } else {
      this.#removePlugins.push("FontFamily");
    }

    if (this.#features.fontSize) {
      items.push("fontSize");
    } else {
      this.#removePlugins.push("FontSize");
    }

    if (this.#features.code) {
      items.push("code");
    } else {
      this.#removePlugins.push("Code");
    }

    if (items.length !== 0) {
      items.push(this.#divider, "removeFormat");
    }

    if (items.length > 0) {
      this.#toolbar.push({
        label: "woltlabToolbarGroup_format",
        items,
      });

      this.#toolbarGroups["format"] = {
        icon: "ellipsis;false",
        label: getPhrase("wcf.editor.button.group.format"),
      };
    }
  }

  #setupList(): void {
    if (this.#features.list) {
      this.#toolbar.push({
        label: "woltlabToolbarGroup_list",
        items: ["bulletedList", "numberedList", "outdent", "indent"],
      });

      this.#toolbarGroups["list"] = {
        icon: "list;false",
        label: getPhrase("wcf.editor.button.group.list"),
      };
    } else {
      this.#removePlugins.push("List");
    }
  }

  #setupAlignment(): void {
    if (this.#features.alignment) {
      this.#toolbar.push("alignment");
    } else {
      this.#removePlugins.push("Alignment");
    }
  }

  #setupLink(): void {
    if (this.#features.link) {
      this.#toolbar.push("link");
    } else {
      this.#removePlugins.push("Link", "LinkImage");
    }
  }

  #setupImage(): void {
    if (this.#features.image) {
      this.#toolbar.push("insertImage");

      if (!this.#features.attachment) {
        this.#removePlugins.push("ImageUpload", "ImageUploadUI", "WoltlabAttachment");
      }
    } else {
      this.#removePlugins.push("ImageInsertUI");

      if (this.#features.link) {
        this.#removePlugins.push("LinkImage");
      }
    }
  }

  #setupBlocks(): void {
    const items: string[] = [];

    if (this.#features.table) {
      items.push("insertTable");
    } else {
      this.#removePlugins.push("Table", "TableToolbar");
    }

    if (this.#features.quoteBlock) {
      items.push("blockQuote");
    } else {
      this.#removePlugins.push("BlockQuote", "WoltlabBlockQuote");
    }

    if (this.#features.codeBlock) {
      items.push("codeBlock");
    } else {
      this.#removePlugins.push("CodeBlock", "WoltlabCodeBlock");
    }

    if (this.#features.spoiler) {
      items.push("spoiler");
    } else {
      this.#removePlugins.push("WoltlabSpoiler");
    }

    if (this.#features.html) {
      items.push("htmlEmbed");
    } else {
      this.#removePlugins.push("HtmlEmbed");
    }

    if (items.length > 0) {
      this.#toolbar.push({
        label: getPhrase("wcf.editor.button.group.block"),
        icon: "plus",
        items,
      });
    }
  }

  #insertDivider(): void {
    this.#toolbar.push(this.#divider);
  }

  #setupMedia(): void {
    if (!this.#features.media) {
      this.#removePlugins.push("WoltlabMedia");
    }
  }

  #setupMention(): void {
    if (!this.#features.mention) {
      this.#removePlugins.push("Mention", "WoltlabMention");
    }
  }

  #getToolbar(): ToolbarConfig {
    let allowDivider = false;
    const toolbar = this.#toolbar.filter((item) => {
      if (typeof item === "string" && item === this.#divider) {
        if (!allowDivider) {
          return false;
        }

        allowDivider = false;

        return true;
      }

      allowDivider = true;

      return true;
    });

    return toolbar;
  }

  build(): void {
    if (this.#removePlugins.length > 0 || this.#toolbar.length > 0) {
      throw new Error("Cannot build the configuration twice.");
    }

    this.#setupHeading();

    this.#insertDivider();

    this.#insertDivider();

    this.#setupBasicFormat();
    this.#setupTextFormat();

    this.#insertDivider();

    this.#setupList();
    this.#setupAlignment();
    this.#setupLink();
    this.#setupImage();
    this.#setupBlocks();

    this.#insertDivider();

    this.#setupMedia();
    this.#setupMention();
  }

  toConfig(): EditorConfig {
    const language = Object.keys(window.CKEDITOR_TRANSLATIONS).find((language) => language !== "en");

    const key = language ? language : "en";
    const { dictionary } = window.CKEDITOR_TRANSLATIONS[key];

    dictionary["Author"] = getPhrase("wcf.ckeditor.quote.author");
    dictionary["Filename"] = getPhrase("wcf.ckeditor.code.fileName");
    dictionary["Line number"] = getPhrase("wcf.ckeditor.code.lineNumber");
    dictionary["Quote"] = getPhrase("wcf.ckeditor.quote");
    dictionary["Quote from %0"] = getPhrase("wcf.ckeditor.quoteFrom");
    dictionary["Spoiler"] = getPhrase("wcf.editor.button.spoiler");

    // TODO: The typings are both incompleted and outdated.
    return {
      alignment: {
        options: [
          { name: "center", className: "text-center" },
          { name: "left", className: "text-left" },
          { name: "justify", className: "text-justify" },
          { name: "right", className: "text-right" },
        ],
      },
      highlight: {
        options: [
          {
            model: "markerWarning",
            class: "marker-warning",
            title: getPhrase("wcf.ckeditor.marker.warning"),
            color: "var(--marker-warning)",
            type: "marker",
          },
          {
            model: "markerError",
            class: "marker-error",
            title: getPhrase("wcf.ckeditor.marker.error"),
            color: "var(--marker-error)",
            type: "marker",
          },
          {
            model: "markerInfo",
            class: "marker-info",
            title: getPhrase("wcf.ckeditor.marker.info"),
            color: "var(--marker-info)",
            type: "marker",
          },
          {
            model: "markerSuccess",
            class: "marker-success",
            title: getPhrase("wcf.ckeditor.marker.success"),
            color: "var(--marker-success)",
            type: "marker",
          },
        ],
      },
      language,
      removePlugins: this.#removePlugins,
      fontFamily: {
        options: [
          "default",
          "Arial, Helvetica, sans-serif",
          "Comic Sans MS, Marker Felt, cursive",
          "Consolas, Courier New, Courier, monospace",
          "Georgia, serif",
          "Lucida Sans Unicode, Lucida Grande, sans-serif",
          "Tahoma, Geneva, sans-serif",
          "Times New Roman, Times, serif",
          'Trebuchet MS", Helvetica, sans-serif',
          "Verdana, Geneva, sans-serif",
        ],
      },
      fontSize: {
        options: [8, 10, 12, "default", 18, 24, 36],
      },
      toolbar: this.#getToolbar(),
      ui: {
        viewportOffset: {
          top: 50,
        },
      },
      woltlabToolbarGroup: this.#toolbarGroups,
    } as any;
  }
}

export type Features = {
  alignment: boolean;
  attachment: boolean;
  autosave: string;
  code: boolean;
  codeBlock: boolean;
  fontColor: boolean;
  fontFamily: boolean;
  fontSize: boolean;
  heading: boolean;
  html: boolean;
  image: boolean;
  link: boolean;
  list: boolean;
  mark: boolean;
  media: boolean;
  mention: boolean;
  quoteBlock: boolean;
  spoiler: boolean;
  strikethrough: boolean;
  submitOnEnter: boolean;
  subscript: boolean;
  superscript: boolean;
  table: boolean;
  underline: boolean;
};

export function createConfigurationFor(features: Features): EditorConfig {
  const configuration = new ConfigurationBuilder(features);
  configuration.build();

  return configuration.toConfig();
}
