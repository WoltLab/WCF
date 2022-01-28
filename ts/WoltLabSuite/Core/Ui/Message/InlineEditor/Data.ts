/**
 * @woltlabExcludeBundle all
 */

import { ResponseData } from "../../../Ajax/Data";

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

export interface AjaxResponseEditor extends ResponseData {
  returnValues: {
    template: string;
  };
}

export interface AjaxResponseMessage extends ResponseData {
  returnValues: {
    attachmentList?: string;
    message: string;
    poll?: string;
  };
}
