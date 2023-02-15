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
  const toolbar: ToolbarConfig = [
    "heading",

    "|",

    "bold",
    "italic",
    {
      label: "woltlabToolbarGroup_format",
      items: ["underline", "strikethrough", "subscript", "superscript", "code"],
    },

    "|",

    {
      label: "woltlabToolbarGroup_list",
      items: ["bulletedList", "numberedList"],
    },

    "alignment",
  ];

  if (features.link) {
    toolbar.push("link");
  }

  if (features.image) {
    ("insertImage");
  }

  const blocks = ["insertTable", "blockQuote", "codeBlock"];
  if (features.spoiler) {
    blocks.push("spoiler");
  }

  if (features.html) {
    blocks.push("htmlEmbed");
  }

  if (features.media) {
    blocks.push("woltlabBbcode_media");
  }

  toolbar.push({
    label: getPhrase("wcf.editor.button.group.block"),
    icon: "plus",
    items: blocks,
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
