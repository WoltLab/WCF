/**
 * Interfaces and data types for dialogs.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/Dialog/Data
 * @woltlabExcludeBundle all
 */

import { RequestPayload, ResponseData } from "../../Ajax/Data";
import { FocusTrap } from "focus-trap";

export type DialogHtml = DocumentFragment | string | null;

export type DialogCallbackSetup = () => DialogSettings;
export type CallbackSubmit = () => void;

export interface DialogCallbackObject {
  _dialogSetup: DialogCallbackSetup;
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
  focusTrap: FocusTrap;
  header: HTMLElement;

  onBeforeClose: CallbackOnBeforeClose;
  onClose: CallbackOnClose;
  onShow: CallbackOnShow;

  submitButton: HTMLElement | null;
  inputFields: Set<HTMLInputElement>;
}
