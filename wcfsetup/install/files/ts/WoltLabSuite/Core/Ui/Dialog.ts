/**
 * Modal dialog handler.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Ui/Dialog (alias)
 * @module  WoltLabSuite/Core/Ui/Dialog
 */

import * as Core from '../Core';
import DomChangeListener from '../Dom/Change/Listener';
import * as UiScreen from './Screen';
import DomUtil from '../Dom/Util';
import { DialogCallbackObject, DialogData, DialogId, DialogOptions, DialogHtml, AjaxInitialization } from './Dialog/Data';
import * as Language from '../Language';
import * as Environment from '../Environment';
import * as EventHandler from '../Event/Handler';

let _activeDialog: string | null = null;
let _callbackFocus: (event: FocusEvent) => void;
let _container: HTMLElement;
const _dialogs = new Map<ElementId, DialogData>();
let _dialogFullHeight = false;
const _dialogObjects = new WeakMap<DialogCallbackObject, DialogInternalData>();
const _dialogToObject = new Map<ElementId, DialogCallbackObject>();
let _focusedBeforeDialog: Element | null;
let _keyupListener: (event: KeyboardEvent) => boolean;
const _validCallbacks = ['onBeforeClose', 'onClose', 'onShow'];

// list of supported `input[type]` values for dialog submit
const _validInputTypes = ['number', 'password', 'search', 'tel', 'text', 'url'];

const _focusableElements = [
  'a[href]:not([tabindex^="-"]):not([inert])',
  'area[href]:not([tabindex^="-"]):not([inert])',
  'input:not([disabled]):not([inert])',
  'select:not([disabled]):not([inert])',
  'textarea:not([disabled]):not([inert])',
  'button:not([disabled]):not([inert])',
  'iframe:not([tabindex^="-"]):not([inert])',
  'audio:not([tabindex^="-"]):not([inert])',
  'video:not([tabindex^="-"]):not([inert])',
  '[contenteditable]:not([tabindex^="-"]):not([inert])',
  '[tabindex]:not([tabindex^="-"]):not([inert])',
];

/**
 * @exports  WoltLabSuite/Core/Ui/Dialog
 */
export = {
  /**
   * Sets up global container and internal variables.
   */
  setup(): void {
    _container = document.createElement('div');
    _container.classList.add('dialogOverlay');
    _container.setAttribute('aria-hidden', 'true');
    _container.addEventListener('mousedown', this._closeOnBackdrop.bind(this));
    _container.addEventListener('wheel', event => {
      if (event.target === _container) {
        event.preventDefault();
      }
    }, {passive: false});

    document.getElementById('content')!.appendChild(_container);

    _keyupListener = (event: KeyboardEvent): boolean => {
      if (event.key === "Escape") {
        const target = event.target as HTMLElement;
        if (target.nodeName !== 'INPUT' && target.nodeName !== 'TEXTAREA') {
          this.close(_activeDialog!);

          return false;
        }
      }

      return true;
    };

    UiScreen.on('screen-xs', {
      match() {
        _dialogFullHeight = true;
      },
      unmatch() {
        _dialogFullHeight = false;
      },
      setup() {
        _dialogFullHeight = true;
      },
    });

    this._initStaticDialogs();
    DomChangeListener.add('Ui/Dialog', this._initStaticDialogs.bind(this));

    UiScreen.setDialogContainer(_container);

    window.addEventListener('resize', () => {
      _dialogs.forEach(dialog => {
        if (!Core.stringToBool(dialog.dialog.getAttribute('aria-hidden'))) {
          this.rebuild(dialog.dialog.dataset.id || '');
        }
      });
    });
  },

  _initStaticDialogs(): void {
    document.querySelectorAll<HTMLElement>('.jsStaticDialog').forEach(button => {
      button.classList.remove('jsStaticDialog');

      const id = button.dataset.dialogId || '';
      if (id) {
        const container = document.getElementById(id);
        if (container !== null) {
          container.classList.remove('jsStaticDialogContent');
          container.dataset.isStaticDialog = 'true';
          DomUtil.hide(container);

          button.addEventListener('click', event => {
            event.preventDefault();

            this.openStatic(container.id, null, {title: container.dataset.title || ''});
          });
        }
      }
    });
  },

  /**
   * Opens the dialog and implicitly creates it on first usage.
   */
  open(callbackObject: DialogCallbackObject, html?: DialogHtml): DialogData | object {
    let dialogData = _dialogObjects.get(callbackObject);
    if (dialogData && Core.isPlainObject(dialogData)) {
      // dialog already exists
      return this.openStatic(dialogData.id, typeof html === 'undefined' ? null : html);
    }

    // initialize a new dialog
    if (typeof callbackObject._dialogSetup !== 'function') {
      throw new Error("Callback object does not implement the method '_dialogSetup()'.");
    }

    const setupData = callbackObject._dialogSetup();
    if (!Core.isPlainObject(setupData)) {
      throw new Error("Expected an object literal as return value of '_dialogSetup()'.");
    }

    const id = setupData.id;
    dialogData = {id};

    let createOnly = true;
    let dialogElement: HTMLElement | null;
    if (setupData.source === undefined) {
      dialogElement = document.getElementById(id);
      if (dialogElement === null) {
        throw new Error("Element id '" + id + "' is invalid and no source attribute was given. If you want to use the `html` argument instead, please add `source: null` to your dialog configuration.");
      }

      setupData.source = document.createDocumentFragment();
      setupData.source.appendChild(dialogElement);

      dialogElement.removeAttribute('id');
      DomUtil.show(dialogElement);
    } else if (setupData.source === null) {
      // `null` means there is no static markup and `html` should be used instead
      setupData.source = html;
    } else if (typeof setupData.source === 'function') {
      setupData.source();
    } else if (Core.isPlainObject(setupData.source)) {
      if (typeof html === 'string' && html.trim() !== '') {
        setupData.source = html;
      } else {
        import('../Ajax').then(Ajax => {
          const source = setupData.source as AjaxInitialization;
          Ajax.api(this as any, source.data, data => {
            if (data.returnValues && typeof data.returnValues.template === 'string') {
              this.open(callbackObject, data.returnValues.template);

              if (typeof source.after === 'function') {
                source.after(_dialogs.get(id)!.content, data);
              }
            }
          });
        });

        return {};
      }
    } else {
      if (typeof setupData.source === 'string') {
        dialogElement = document.createElement('div');
        dialogElement.id = id;
        DomUtil.setInnerHtml(dialogElement, setupData.source);

        setupData.source = document.createDocumentFragment();
        setupData.source.appendChild(dialogElement);
      }

      if (!setupData.source.nodeType || setupData.source.nodeType !== Node.DOCUMENT_FRAGMENT_NODE) {
        throw new Error("Expected at least a document fragment as 'source' attribute.");
      }

      createOnly = false;
    }

    _dialogObjects.set(callbackObject, dialogData);
    _dialogToObject.set(id, callbackObject);

    return this.openStatic(id, setupData.source as DialogHtml, setupData.options, createOnly);
  },

  /**
   * Opens an dialog, if the dialog is already open the content container
   * will be replaced by the HTML string contained in the parameter html.
   *
   * If id is an existing element id, html will be ignored and the referenced
   * element will be appended to the content element instead.
   */
  openStatic(id: string, html: DialogHtml, options?: DialogOptions, createOnly?: boolean): DialogData {
    UiScreen.pageOverlayOpen();

    if (Environment.platform() !== 'desktop') {
      if (!this.isOpen(id)) {
        UiScreen.scrollDisable();
      }
    }

    if (_dialogs.has(id)) {
      this._updateDialog(id, html as string);
    } else {
      options = Core.extend({
        backdropCloseOnClick: true,
        closable: true,
        closeButtonLabel: Language.get('wcf.global.button.close'),
        closeConfirmMessage: '',
        disableContentPadding: false,
        title: '',

        onBeforeClose: null,
        onClose: null,
        onShow: null,
      }, options || {}) as InternalDialogOptions;

      if (!options.closable) options.backdropCloseOnClick = false;
      if (options.closeConfirmMessage) {
        options.onBeforeClose = id => {
          import('./Confirmation').then(UiConfirmation => {
            UiConfirmation.show({
              confirm: this.close.bind(this, id),
              message: options!.closeConfirmMessage || '',
            });
          });
        };
      }

      this._createDialog(id, html, options as InternalDialogOptions, createOnly || false);
    }

    const data = _dialogs.get(id)!;

    // iOS breaks `position: fixed` when input elements or `contenteditable`
    // are focused, this will freeze the screen and force Safari to scroll
    // to the input field
    if (Environment.platform() === 'ios') {
      window.setTimeout(() => {
        data.content.querySelector<HTMLElement>('input, textarea')?.focus();
      }, 200);
    }

    return data;
  },

  /**
   * Sets the dialog title.
   */
  setTitle(id: ElementIdOrCallbackObject, title: string): void {
    id = this._getDialogId(id);

    const data = _dialogs.get(id);
    if (data === undefined) {
      throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
    }

    const dialogTitle = data.dialog.querySelector('.dialogTitle');
    if (dialogTitle) {
      dialogTitle.textContent = title;
    }
  },

  /**
   * Sets a callback function on runtime.
   */
  setCallback(id: ElementIdOrCallbackObject, key: string, value: (...args: any[]) => void | null): void {
    if (typeof id === 'object') {
      const dialogData = _dialogObjects.get(id);
      if (dialogData !== undefined) {
        id = dialogData.id;
      }
    }

    const data = _dialogs.get(id as string);
    if (data === undefined) {
      throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
    }

    if (_validCallbacks.indexOf(key) === -1) {
      throw new Error("Invalid callback identifier, '" + key + "' is not recognized.");
    }

    if (typeof value !== 'function' && value !== null) {
      throw new Error("Only functions or the 'null' value are acceptable callback values ('" + typeof value + "' given).");
    }

    data[key] = value;
  },

  /**
   * Creates the DOM for a new dialog and opens it.
   *
   * @param  {string}      id    element id, if exists the html parameter is ignored in favor of the existing element
   * @param  {?(string|DocumentFragment)}  html    content html
   * @param  {object<string, *>}    options    list of options
   * @param  {boolean=}      createOnly  create the dialog but do not open it
   */
  _createDialog(id: string, html: DialogHtml, options: InternalDialogOptions, createOnly: boolean): void {
    let element: HTMLElement | null = null;
    if (html === null) {
      element = document.getElementById(id);
      if (element === null) {
        throw new Error("Expected either a HTML string or an existing element id.");
      }
    }

    const dialog = document.createElement('div');
    dialog.classList.add('dialogContainer');
    dialog.setAttribute('aria-hidden', 'true');
    dialog.setAttribute('role', 'dialog');
    dialog.id = id;

    const header = document.createElement('header');
    dialog.appendChild(header);

    const titleId = DomUtil.getUniqueId();
    dialog.setAttribute('aria-labelledby', titleId);

    const title = document.createElement('span');
    title.classList.add('dialogTitle');
    title.textContent = options.title;
    title.id = titleId;
    header.appendChild(title);

    if (options.closable) {
      const closeButton = document.createElement('a');
      closeButton.className = 'dialogCloseButton jsTooltip';
      closeButton.href = '#';
      closeButton.setAttribute('role', 'button');
      closeButton.tabIndex = 0;
      closeButton.title = options.closeButtonLabel;
      closeButton.setAttribute('aria-label', options.closeButtonLabel);
      closeButton.addEventListener('click', this._close.bind(this));
      header.appendChild(closeButton);

      const span = document.createElement('span');
      span.className = 'icon icon24 fa-times';
      closeButton.appendChild(span);
    }

    const contentContainer = document.createElement('div');
    contentContainer.classList.add('dialogContent');
    if (options.disableContentPadding) contentContainer.classList.add('dialogContentNoPadding');
    dialog.appendChild(contentContainer);

    contentContainer.addEventListener('wheel', event => {
      let allowScroll = false;
      let element: HTMLElement | null = event.target as HTMLElement;
      let clientHeight, scrollHeight, scrollTop;
      while (true) {
        clientHeight = element.clientHeight;
        scrollHeight = element.scrollHeight;

        if (clientHeight < scrollHeight) {
          scrollTop = element.scrollTop;

          // negative value: scrolling up
          if (event.deltaY < 0 && scrollTop > 0) {
            allowScroll = true;
            break;
          } else if (event.deltaY > 0 && (scrollTop + clientHeight < scrollHeight)) {
            allowScroll = true;
            break;
          }
        }

        if (!element || element === contentContainer) {
          break;
        }

        element = element.parentNode as HTMLElement;
      }

      if (!allowScroll) {
        event.preventDefault();
      }
    }, {passive: false});

    let content: HTMLElement;
    if (element === null) {
      if (typeof html === 'string') {
        content = document.createElement('div');
        content.id = id;
        DomUtil.setInnerHtml(content, html);
      } else if (html instanceof DocumentFragment) {
        const children: HTMLElement[] = [];
        let node: Node;
        for (let i = 0, length = html.childNodes.length; i < length; i++) {
          node = html.childNodes[i];

          if (node.nodeType === Node.ELEMENT_NODE) {
            children.push(node as HTMLElement);
          }
        }

        if (children[0].nodeName !== 'DIV' || children.length > 1) {
          content = document.createElement('div');
          content.id = id;
          content.appendChild(html);
        } else {
          content = children[0];
        }
      } else {
        throw new TypeError("'html' must either be a string or a DocumentFragment");
      }
    } else {
      content = element;
    }

    contentContainer.appendChild(content);

    if (content.style.getPropertyValue('display') === 'none') {
      DomUtil.show(content);
    }

    _dialogs.set(id, {
      backdropCloseOnClick: options.backdropCloseOnClick,
      closable: options.closable,
      content: content,
      dialog: dialog,
      header: header,
      onBeforeClose: options.onBeforeClose!,
      onClose: options.onClose!,
      onShow: options.onShow!,

      submitButton: null,
      inputFields: new Set<HTMLInputElement>(),
    });

    _container.insertBefore(dialog, _container.firstChild);

    if (typeof options.onSetup === 'function') {
      options.onSetup(content);
    }

    if (!createOnly) {
      this._updateDialog(id, null);
    }
  },

  /**
   * Updates the dialog's content element.
   */
  _updateDialog(id: ElementId, html: string | null): void {
    const data = _dialogs.get(id);
    if (data === undefined) {
      throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
    }

    if (typeof html === 'string') {
      DomUtil.setInnerHtml(data.content, html);
    }

    if (Core.stringToBool(data.dialog.getAttribute('aria-hidden'))) {
      // close existing dropdowns
      // TODO
      //UiSimpleDropdown.closeAll();
      window.WCF.Dropdown.Interactive.Handler.closeAll();

      if (_callbackFocus === null) {
        _callbackFocus = this._maintainFocus.bind(this);
        document.body.addEventListener('focus', _callbackFocus, {capture: true});
      }

      if (data.closable && Core.stringToBool(_container.getAttribute('aria-hidden'))) {
        window.addEventListener('keyup', _keyupListener);
      }

      // Move the dialog to the front to prevent it being hidden behind already open dialogs
      // if it was previously visible.
      data.dialog.parentNode!.insertBefore(data.dialog, data.dialog.parentNode!.firstChild);

      data.dialog.setAttribute('aria-hidden', 'false');
      _container.setAttribute('aria-hidden', 'false');
      _container.setAttribute('close-on-click', (data.backdropCloseOnClick ? 'true' : 'false'));
      _activeDialog = id;

      // Keep a reference to the currently focused element to be able to restore it later.
      _focusedBeforeDialog = document.activeElement;

      // Set the focus to the first focusable child of the dialog element.
      const closeButton = data.header.querySelector('.dialogCloseButton');
      if (closeButton) closeButton.setAttribute('inert', 'true');
      this._setFocusToFirstItem(data.dialog, false);
      if (closeButton) closeButton.removeAttribute('inert');

      if (typeof data.onShow === 'function') {
        data.onShow(data.content);
      }

      if (Core.stringToBool(data.content.dataset.isStaticDialog || '')) {
        EventHandler.fire('com.woltlab.wcf.dialog', 'openStatic', {
          content: data.content,
          id: id,
        });
      }
    }

    this.rebuild(id);

    DomChangeListener.trigger();
  },

  _maintainFocus(event: FocusEvent): void {
    if (_activeDialog) {
      const data = _dialogs.get(_activeDialog) as DialogData;
      const target = event.target as HTMLElement;
      if (!data.dialog.contains(target) && !target.closest('.dropdownMenuContainer') && !target.closest('.datePicker')) {
        this._setFocusToFirstItem(data.dialog, true);
      }
    }
  },

  _setFocusToFirstItem(dialog: HTMLElement, maintain: boolean): void {
    let focusElement = this._getFirstFocusableChild(dialog);
    if (focusElement !== null) {
      if (maintain) {
        if (focusElement.id === 'username' || (focusElement as HTMLInputElement).name === 'username') {
          if (Environment.browser() === 'safari' && Environment.platform() === 'ios') {
            // iOS Safari's username/password autofill breaks if the input field is focused 
            focusElement = null;
          }
        }
      }

      if (focusElement) {
        // Setting the focus to a select element in iOS is pretty strange, because
        // it focuses it, but also displays the keyboard for a fraction of a second,
        // causing it to pop out from below and immediately vanish.
        // 
        // iOS will only show the keyboard if an input element is focused *and* the
        // focus is an immediate result of a user interaction. This method must be
        // assumed to be called from within a click event, but we want to set the
        // focus without triggering the keyboard.
        // 
        // We can break the condition by wrapping it in a setTimeout() call,
        // effectively tricking iOS into focusing the element without showing the
        // keyboard.
        setTimeout(() => {
          focusElement!.focus();
        }, 1);
      }
    }
  },

  _getFirstFocusableChild(element: HTMLElement): HTMLElement | null {
    const nodeList = element.querySelectorAll<HTMLElement>(_focusableElements.join(','));
    for (let i = 0, length = nodeList.length; i < length; i++) {
      if (nodeList[i].offsetWidth && nodeList[i].offsetHeight && nodeList[i].getClientRects().length) {
        return nodeList[i];
      }
    }

    return null;
  },

  /**
   * Rebuilds dialog identified by given id.
   */
  rebuild(id: string): void {
    id = this._getDialogId(id);

    const data = _dialogs.get(id);
    if (data === undefined) {
      throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
    }

    // ignore non-active dialogs
    if (Core.stringToBool(data.dialog.getAttribute('aria-hidden'))) {
      return;
    }

    const contentContainer = data.content.parentNode as HTMLElement;

    const formSubmit = data.content.querySelector('.formSubmit') as HTMLElement;
    let unavailableHeight = 0;
    if (formSubmit !== null) {
      contentContainer.classList.add('dialogForm');
      formSubmit.classList.add('dialogFormSubmit');

      unavailableHeight += DomUtil.outerHeight(formSubmit);

      // Calculated height can be a fractional value and depending on the
      // browser the results can vary. By subtracting a single pixel we're
      // working around fractional values, without visually changing anything.
      unavailableHeight -= 1;

      contentContainer.style.setProperty('margin-bottom', unavailableHeight + 'px', '');
    } else {
      contentContainer.classList.remove('dialogForm');
      contentContainer.style.removeProperty('margin-bottom');
    }

    unavailableHeight += DomUtil.outerHeight(data.header);

    const maximumHeight = (window.innerHeight * (_dialogFullHeight ? 1 : 0.8)) - unavailableHeight;
    contentContainer.style.setProperty('max-height', ~~maximumHeight + 'px', '');

    // fix for a calculation bug in Chrome causing the scrollbar to overlap the border
    if (Environment.browser() === 'chrome') {
      if (data.content.scrollHeight > maximumHeight) {
        data.content.style.setProperty('margin-right', '-1px', '');
      } else {
        data.content.style.removeProperty('margin-right');
      }
    }

    // Chrome and Safari use heavy anti-aliasing when the dialog's width
    // cannot be evenly divided, causing the whole text to become blurry
    if (Environment.browser() === 'chrome' || Environment.browser() === 'safari') {
      // The new Microsoft Edge is detected as "chrome", because effectively we're detecting
      // Chromium rather than Chrome specifically. The workaround for fractional pixels does
      // not work well in Edge, there seems to be a different logic for fractional positions,
      // causing the text to be blurry.
      // 
      // We can use `backface-visibility: hidden` to prevent the anti aliasing artifacts in
      // WebKit/Blink, which will also prevent some weird font rendering issues when resizing.
      contentContainer.classList.add('jsWebKitFractionalPixelFix');
    }

    const callbackObject = _dialogToObject.get(id);
    //noinspection JSUnresolvedVariable
    if (callbackObject !== undefined && typeof callbackObject._dialogSubmit === 'function') {
      const inputFields = data.content.querySelectorAll<HTMLInputElement>('input[data-dialog-submit-on-enter="true"]');

      const submitButton = data.content.querySelector('.formSubmit > input[type="submit"], .formSubmit > button[data-type="submit"]');
      if (submitButton === null) {
        // check if there is at least one input field with submit handling,
        // otherwise we'll assume the dialog has not been populated yet
        if (inputFields.length === 0) {
          console.warn("Broken dialog, expected a submit button.", data.content);
        }

        return;
      }

      if (data.submitButton !== submitButton) {
        data.submitButton = submitButton as HTMLElement;

        submitButton.addEventListener('click', event => {
          event.preventDefault();

          this._submit(id);
        });

        const _callbackKeydown = (event: KeyboardEvent): void => {
          if (event.key === 'Enter') {
            event.preventDefault();

            this._submit(id);
          }
        };

        // bind input fields
        let inputField: HTMLInputElement;
        for (let i = 0, length = inputFields.length; i < length; i++) {
          inputField = inputFields[i];

          if (data.inputFields.has(inputField)) continue;

          if (_validInputTypes.indexOf(inputField.type) === -1) {
            console.warn("Unsupported input type.", inputField);
            continue;
          }

          data.inputFields.add(inputField);

          inputField.addEventListener('keydown', _callbackKeydown);
        }
      }
    }
  },

  /**
   * Submits the dialog.
   */
  _submit(id: string): void {
    const data = _dialogs.get(id);

    let isValid = true;
    data!.inputFields.forEach(inputField => {
      if (inputField.required) {
        if (inputField.value.trim() === '') {
          DomUtil.innerError(inputField, Language.get('wcf.global.form.error.empty'));

          isValid = false;
        } else {
          DomUtil.innerError(inputField, false);
        }
      }
    });

    if (isValid) {
      const callbackObject = _dialogToObject.get(id) as DialogCallbackObject;
      if (typeof callbackObject._dialogSubmit === 'function') {
        callbackObject._dialogSubmit();
      }
    }
  },

  /**
   * Handles clicks on the close button or the backdrop if enabled.
   */
  _close(event: MouseEvent): boolean {
    event.preventDefault();

    const data = _dialogs.get(_activeDialog!) as DialogData;
    if (typeof data.onBeforeClose === 'function') {
      data.onBeforeClose(_activeDialog!);

      return false;
    }

    this.close(_activeDialog!);

    return true;
  },

  /**
   * Closes the current active dialog by clicks on the backdrop.
   */
  _closeOnBackdrop(event: MouseEvent): void {
    if (event.target !== _container) {
      return;
    }

    if (Core.stringToBool(_container.getAttribute('close-on-click'))) {
      this._close(event);
    } else {
      event.preventDefault();
    }
  },

  /**
   * Closes a dialog identified by given id.
   */
  close(id: ElementIdOrCallbackObject): void {
    id = this._getDialogId(id);

    let data = _dialogs.get(id);
    if (data === undefined) {
      throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
    }

    data.dialog.setAttribute('aria-hidden', 'true');

    // Move the keyboard focus away from a now hidden element.
    const activeElement = document.activeElement as HTMLElement;
    if (activeElement.closest('.dialogContainer') === data.dialog) {
      activeElement.blur();
    }

    if (typeof data.onClose === 'function') {
      data.onClose(id);
    }

    // get next active dialog
    _activeDialog = null;
    for (let i = 0; i < _container.childElementCount; i++) {
      const child = _container.children[i] as HTMLElement;
      if (!Core.stringToBool(child.getAttribute('aria-hidden'))) {
        _activeDialog = child.dataset.id || '';
        break;
      }
    }

    UiScreen.pageOverlayClose();

    if (_activeDialog === null) {
      _container.setAttribute('aria-hidden', 'true');
      _container.dataset.closeOnClick = 'false';

      if (data.closable) {
        window.removeEventListener('keyup', _keyupListener);
      }
    } else {
      data = _dialogs.get(_activeDialog) as DialogData;
      _container.dataset.closeOnClick = data.backdropCloseOnClick ? 'true' : 'false';
    }

    if (Environment.platform() !== 'desktop') {
      UiScreen.scrollEnable();
    }
  },

  /**
   * Returns the dialog data for given element id.
   */
  getDialog(id: ElementIdOrCallbackObject): DialogData | undefined {
    return _dialogs.get(this._getDialogId(id));
  },

  /**
   * Returns true for open dialogs.
   */
  isOpen(id: ElementIdOrCallbackObject): boolean {
    const data = this.getDialog(id);
    return data !== undefined && data.dialog.getAttribute('aria-hidden') === 'false';
  },

  /**
   * Destroys a dialog instance.
   *
   * @param  {Object}  callbackObject  the same object that was used to invoke `_dialogSetup()` on first call
   */
  destroy(callbackObject: DialogCallbackObject): void {
    if (typeof callbackObject !== 'object') {
      throw new TypeError("Expected the callback object as parameter.");
    }

    if (_dialogObjects.has(callbackObject)) {
      const id = _dialogObjects.get(callbackObject)!.id;
      if (this.isOpen(id)) {
        this.close(id);
      }

      // If the dialog is destroyed in the close callback, this method is
      // called twice resulting in `_dialogs.get(id)` being undefined for
      // the initial call.
      if (_dialogs.has(id)) {
        _dialogs.get(id)!.dialog.remove();
        _dialogs.delete(id);
      }
      _dialogObjects.delete(callbackObject);
    }
  },

  /**
   * Returns a dialog's id.
   *
   * @param  {(string|object)}  id  element id or callback object
   * @return      {string}
   * @protected
   */
  _getDialogId(id: ElementIdOrCallbackObject): DialogId {
    if (typeof id === 'object') {
      const dialogData = _dialogObjects.get(id);
      if (dialogData !== undefined) {
        return dialogData.id;
      }
    }

    return id.toString();
  },

  _ajaxSetup() {
    return {};
  },
};

interface DialogInternalData {
  id: string;
}

type ElementId = string;

type ElementIdOrCallbackObject = DialogCallbackObject | ElementId;

interface InternalDialogOptions extends DialogOptions {
  backdropCloseOnClick: boolean;
  closable: boolean;
  closeButtonLabel: string;
  closeConfirmMessage: string;
  disableContentPadding: boolean;
}
