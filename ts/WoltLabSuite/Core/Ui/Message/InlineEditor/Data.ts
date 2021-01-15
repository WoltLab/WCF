export interface MessageInlineEditorOptions {
  canEditInline: boolean;

  className: string;
  containerId: string;
  dropdownIdentifier: string;
  editorPrefix: string;

  messageSelector: string;

  // This is the legacy jQuery based class.
  quoteManager: any;
}

export interface ItemData {
  item: "divider" | "editItem" | string;
  label?: string;
}

export interface ElementVisibility {
  [key: string]: boolean;
}
