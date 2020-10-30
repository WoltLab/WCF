import { RequestPayload, ResponseData } from "../../Ajax/Data";

export type DialogHtml = DocumentFragment | string | null;

export type CallbackSetup = () => DialogSettings;
export type CallbackSubmit = () => void;

export interface DialogCallbackObject {
  _dialogSetup: CallbackSetup;
  _dialogSubmit?: CallbackSubmit;
}

export interface AjaxInitialization extends RequestPayload {
  after?: (content: HTMLElement, responseData: ResponseData) => void;
}

export type ExternalInitialization = () => void;

export type DialogId = string;

export interface DialogSettings {
  id: DialogId;
  source?: AjaxInitialization | DocumentFragment | ExternalInitialization | string | null;
  options?: DialogOptions;
}

type CallbackOnBeforeClose = (id: string) => void;
type CallbackOnClose = (id: string) => void;
type CallbackOnSetup = (content: HTMLElement) => void;
type CallbackOnShow = (content: HTMLElement) => void;

export interface DialogOptions {
  backdropCloseOnClick?: boolean;
  closable?: boolean;
  closeButtonLabel?: string;
  closeConfirmMessage?: string;
  disableContentPadding?: boolean;
  title?: string;

  onBeforeClose?: CallbackOnBeforeClose | null;
  onClose?: CallbackOnClose | null;
  onSetup?: CallbackOnSetup | null;
  onShow?: CallbackOnShow | null;
}

export interface DialogData {
  backdropCloseOnClick: boolean;
  closable: boolean;
  content: HTMLElement;
  dialog: HTMLElement;
  header: HTMLElement;

  onBeforeClose: CallbackOnBeforeClose;
  onClose: CallbackOnClose;
  onShow: CallbackOnShow;

  submitButton: HTMLElement | null;
  inputFields: Set<HTMLInputElement>;
}
