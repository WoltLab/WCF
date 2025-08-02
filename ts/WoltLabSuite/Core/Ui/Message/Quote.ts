/**
 * @woltlabExcludeBundle tiny
 *
 * @deprecated 6.2 use `WoltLabSuite/Core/Component/Quote/Message` instead
 */

import { registerContainer } from "WoltLabSuite/Core/Component/Quote/Message";

// see WCF.Message.Quote.Manager
export interface WCFMessageQuoteManager {
  supportPaste: () => boolean;
  updateCount: (number, object) => void;
}

export class UiMessageQuote {
  /**
   * Initializes the quote handler for given object type.
   */
  constructor(
    _quoteManager: WCFMessageQuoteManager,
    className: string,
    objectType: string,
    containerSelector: string,
    messageBodySelector: string,
    _messageContentSelector: string,
    _supportDirectInsert: boolean,
  ) {
    // remove "Action" from className
    /*if (className.endsWith("Action")) {
      className = className.substring(0, className.length - 6);
    }*/

    registerContainer(containerSelector, messageBodySelector, className, objectType);
  }
}

export default UiMessageQuote;
