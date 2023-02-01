import type { EditorConfig } from "@ckeditor/ckeditor5-core/src/editor/editorconfig";

// The typings for CKEditor’s toolbar are outdated.
type ToolbarItem = string | { label: string; icon?: string; items: string[] };
type ToolbarConfig = ToolbarItem[];

export type Features = {
  attachment: boolean;
  autosave: string;
  html: boolean;
  image: boolean;
  media: boolean;
  mention: boolean;
  spoiler: boolean;
  url: boolean;
};

export function createConfiguration(features: Features): EditorConfig {
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

  if (features.url) {
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
    label: "TODO: Insert block",
    icon: "plus",
    items: blocks,
  });

  const config: EditorConfig = {
    // The typings for the toolbar are outdated.
    toolbar: toolbar as any,
  };

  return config;
}
