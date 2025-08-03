/**
 * @woltlabExcludeBundle tiny
 *
 * @deprecated 6.2 use `WoltLabSuite/Core/Component/Quote/Message` instead
 */

import { registerContainer } from "WoltLabSuite/Core/Component/Quote/Message";

/**
 * @deprecated 6.2 Use `registerContainer()` without the className parameter.
 */
export class UiMessageQuote {
  /**
   * Initializes the quote handler for given object type.
   */
  constructor(
    _quoteManager: typeof window.WCF.Message.Quote.Manager,
    className: string,
    objectType: string,
    containerSelector: string,
    messageBodySelector: string,
    _messageContentSelector: string,
    _supportDirectInsert: boolean,
  ) {
    registerContainer(containerSelector, messageBodySelector, objectType, className);
  }
}

export default UiMessageQuote;
