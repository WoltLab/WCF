import { getPhrase } from "../../Language";

import type { EditorConfig } from "@ckeditor/ckeditor5-core/src/editor/editorconfig";

// The typings for CKEditor’s toolbar are outdated.
type ToolbarItem = string | { label: string; icon?: string; items: string[] };
type ToolbarConfig = ToolbarItem[];

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
  subscript: boolean;
  superscript: boolean;
  table: boolean;
  underline: boolean;
};

export function createConfigurationFor(features: Features): EditorConfig {
  const Divider = "|";

  // TODO: This works, but is pretty much unreadable.
  const removePlugins: string[] = [];
  let toolbar: ToolbarConfig = [];

  if (features.heading) {
    toolbar.push("heading");
  } else {
    removePlugins.push("Heading");
  }

  toolbar.push(Divider);

  toolbar.push("bold", "italic");

  let items: string[] = [];
  if (features.underline) {
    items.push("underline");
  } else {
    removePlugins.push("Underline");
  }

  if (features.strikethrough) {
    items.push("strikethrough");
  } else {
    removePlugins.push("Strikethrough");
  }

  if (features.subscript) {
    items.push("subscript");
  } else {
    removePlugins.push("Subscript");
  }

  if (features.superscript) {
    items.push("superscript");
  } else {
    removePlugins.push("Superscript");
  }

  if (features.code) {
    items.push("code");
  } else {
    removePlugins.push("Code");
  }

  if (items.length > 0) {
    toolbar.push({
      label: "woltlabToolbarGroup_format",
      items,
    });
  }

  toolbar.push(Divider);

  if (features.list) {
    toolbar.push({
      label: "woltlabToolbarGroup_list",
      items: ["bulletedList", "numberedList"],
    });
  } else {
    removePlugins.push("List");
  }

  if (features.alignment) {
    toolbar.push("alignment");
  } else {
    removePlugins.push("Alignment");
  }

  if (features.link) {
    toolbar.push("link");
  } else {
    removePlugins.push("Link", "LinkImage");
  }

  if (features.image) {
    toolbar.push("insertImage");
  } else {
    removePlugins.push("Image", "ImageInsertUI", "ImageToolbar", "ImageStyle", "ImageUpload", "ImageUploadUI");

    if (features.link) {
      removePlugins.push("LinkImage");
    }
  }

  items = [];

  if (features.table) {
    items.push("insertTable");
  } else {
    removePlugins.push("Table", "TableToolbar");
  }

  if (features.quoteBlock) {
    items.push("blockQuote");
  } else {
    removePlugins.push("BlockQuote", "WoltlabBlockQuote");
  }

  if (features.codeBlock) {
    items.push("codeBlock");
  } else {
    removePlugins.push("CodeBlock", "WoltlabCodeBlock");
  }

  if (features.spoiler) {
    items.push("spoiler");
  } else {
    removePlugins.push("WoltlabSpoiler");
  }

  if (features.html) {
    items.push("htmlEmbed");
  } else {
    removePlugins.push("HtmlEmbed");
  }

  if (features.media) {
    items.push("woltlabBbcode_media");
  } else {
    removePlugins.push("WoltlabMedia");
  }

  if (items.length > 0) {
    toolbar.push({
      label: getPhrase("wcf.editor.button.group.block"),
      icon: "plus",
      items,
    });
  }

  let allowDivider = false;
  toolbar = toolbar.filter((item) => {
    if (typeof item === "string" && item === Divider) {
      if (!allowDivider) {
        return false;
      }

      allowDivider = false;

      return true;
    }

    allowDivider = true;

    return true;
  });

  const woltlabToolbarGroup = {
    format: {
      icon: "ellipsis;false",
      label: getPhrase("wcf.editor.button.group.format"),
    },
    list: {
      icon: "list;false",
      label: getPhrase("wcf.editor.button.group.list"),
    },
  };

  // TODO: The typings are both outdated and incomplete.
  const config: Record<string, unknown> = {
    toolbar,
    woltlabToolbarGroup,
  };

  return config as EditorConfig;
}
