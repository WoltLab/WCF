import { getPhrase } from "../../Language";

import type { EditorConfig } from "@ckeditor/ckeditor5-core/src/editor/editorconfig";

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

    if (this.#features.code) {
      items.push("code");
    } else {
      this.#removePlugins.push("Code");
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
        items: ["bulletedList", "numberedList"],
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
    } else {
      this.#removePlugins.push("Image", "ImageInsertUI", "ImageToolbar", "ImageStyle", "ImageUpload", "ImageUploadUI");

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

    if (this.#features.media) {
      items.push("woltlabBbcode_media");
    } else {
      this.#removePlugins.push("WoltlabMedia");
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

    this.#setupBasicFormat();
    this.#setupTextFormat();

    this.#insertDivider();

    this.#setupList();
    this.#setupAlignment();
    this.#setupLink();
    this.#setupImage();
    this.#setupBlocks();

    this.#insertDivider();
  }

  toConfig(): EditorConfig {
    // TODO: The typings are both incompleted and outdated.
    return {
      removePlugins: this.#removePlugins,
      toolbar: this.#getToolbar(),
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
  heading: boolean;
  html: boolean;
  image: boolean;
  link: boolean;
  list: boolean;
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
