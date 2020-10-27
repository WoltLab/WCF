/**
 * Simple drop-down implementation.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Ui/SimpleDropdown (alias)
 * @module  WoltLabSuite/Core/Ui/Dropdown/Simple
 */

import CallbackList from '../../CallbackList';
import * as Core from '../../Core';
import DomChangeListener from '../../Dom/Change/Listener';
import * as DomTraverse from '../../Dom/Traverse';
import DomUtil from '../../Dom/Util';
import * as UiAlignment from '../Alignment';
import UiCloseOverlay from '../CloseOverlay';
import { AllowFlip } from '../Alignment';

let _availableDropdowns: HTMLCollectionOf<HTMLElement>;
const _callbacks = new CallbackList();
let _didInit = false;
const _dropdowns = new Map<string, HTMLElement>();
const _menus = new Map<string, HTMLElement>();
let _menuContainer: HTMLElement;
let _activeTargetId = '';

/**
 * Handles drop-down positions in overlays when scrolling in the overlay.
 */
function onDialogScroll(event: WheelEvent): void {
  const dialogContent = event.currentTarget as HTMLElement;
  const dropdowns = dialogContent.querySelectorAll('.dropdown.dropdownOpen');

  for (let i = 0, length = dropdowns.length; i < length; i++) {
    const dropdown = dropdowns[i];
    const containerId = DomUtil.identify(dropdown);
    const offset = DomUtil.offset(dropdown);
    const dialogOffset = DomUtil.offset(dialogContent);

    // check if dropdown toggle is still (partially) visible
    if (offset.top + dropdown.clientHeight <= dialogOffset.top) {
      // top check
      UiDropdownSimple.toggleDropdown(containerId);
    } else if (offset.top >= dialogOffset.top + dialogContent.offsetHeight) {
      // bottom check
      UiDropdownSimple.toggleDropdown(containerId);
    } else if (offset.left <= dialogOffset.left) {
      // left check
      UiDropdownSimple.toggleDropdown(containerId);
    } else if (offset.left >= dialogOffset.left + dialogContent.offsetWidth) {
      // right check
      UiDropdownSimple.toggleDropdown(containerId);
    } else {
      UiDropdownSimple.setAlignment(_dropdowns.get(containerId)!, _menus.get(containerId)!);
    }
  }
}

/**
 * Recalculates drop-down positions on page scroll.
 */
function onScroll() {
  _dropdowns.forEach((dropdown, containerId) => {
    if (dropdown.classList.contains('dropdownOpen')) {
      if (Core.stringToBool(dropdown.dataset.isOverlayDropdownButton || '')) {
        UiDropdownSimple.setAlignment(dropdown, _menus.get(containerId)!);
      } else {
        const menu = _menus.get(dropdown.id) as HTMLElement;
        if (!Core.stringToBool(menu.dataset.dropdownIgnorePageScroll || '')) {
          UiDropdownSimple.close(containerId);
        }
      }
    }
  });
}

type NotificationAction = 'close' | 'open';
type NotificationCallback = (containerId: string, action: NotificationAction) => void;

/**
 * Notifies callbacks on status change.
 */
function notifyCallbacks(containerId: string, action: NotificationAction): void {
  _callbacks.forEach(containerId, callback => {
    callback(containerId, action);
  });
}

/**
 * Toggles the drop-down's state between open and close.
 */
function toggle(event: KeyboardEvent | MouseEvent | null, targetId?: string, alternateElement?: HTMLElement, disableAutoFocus?: boolean): boolean {
  if (event !== null) {
    event.preventDefault();
    event.stopPropagation();

    const target = event.currentTarget as HTMLElement;
    targetId = target.dataset.target;

    if (disableAutoFocus === undefined && event instanceof MouseEvent) {
      disableAutoFocus = true;
    }
  }

  let dropdown = _dropdowns.get(targetId!) as HTMLElement;
  let preventToggle = false;
  if (dropdown !== undefined) {
    let button, parent;

    // check if the dropdown is still the same, as some components (e.g. page actions)
    // re-create the parent of a button
    if (event) {
      button = event.currentTarget;
      parent = button.parentNode;
      if (parent !== dropdown) {
        parent.classList.add('dropdown');
        parent.id = dropdown.id;

        // remove dropdown class and id from old parent
        dropdown.classList.remove('dropdown');
        dropdown.id = '';

        dropdown = parent;
        _dropdowns.set(targetId!, parent);
      }
    }

    if (disableAutoFocus === undefined) {
      button = dropdown.closest('.dropdownToggle');
      if (!button) {
        button = dropdown.querySelector('.dropdownToggle');

        if (!button && dropdown.id) {
          button = document.querySelector('[data-target="' + dropdown.id + '"]');
        }
      }

      if (button && Core.stringToBool(button.dataset.dropdownLazyInit || '')) {
        disableAutoFocus = true;
      }
    }

    // Repeated clicks on the dropdown button will not cause it to close, the only way
    // to close it is by clicking somewhere else in the document or on another dropdown
    // toggle. This is used with the search bar to prevent the dropdown from closing by
    // setting the caret position in the search input field.
    if (Core.stringToBool(dropdown.dataset.dropdownPreventToggle || '') && dropdown.classList.contains('dropdownOpen')) {
      preventToggle = true;
    }

    // check if 'isOverlayDropdownButton' is set which indicates that the dropdown toggle is within an overlay
    if (dropdown.dataset.isOverlayDropdownButton === '') {
      const dialogContent = DomTraverse.parentByClass(dropdown, 'dialogContent');
      dropdown.dataset.isOverlayDropdownButton = (dialogContent !== null) ? 'true' : 'false';

      if (dialogContent !== null) {
        dialogContent.addEventListener('scroll', onDialogScroll);
      }
    }
  }

  // close all dropdowns
  _activeTargetId = '';
  _dropdowns.forEach((dropdown, containerId) => {
    const menu = _menus.get(containerId) as HTMLElement;
    let firstListItem: HTMLLIElement | null = null;

    if (dropdown.classList.contains('dropdownOpen')) {
      if (!preventToggle) {
        dropdown.classList.remove('dropdownOpen');
        menu.classList.remove('dropdownOpen');

        const button = dropdown.querySelector('.dropdownToggle');
        if (button) button.setAttribute('aria-expanded', 'false');

        notifyCallbacks(containerId, 'close');
      } else {
        _activeTargetId = targetId!;
      }
    } else if (containerId === targetId && menu.childElementCount > 0) {
      _activeTargetId = targetId;
      dropdown.classList.add('dropdownOpen');
      menu.classList.add('dropdownOpen');

      const button = dropdown.querySelector('.dropdownToggle');
      if (button) button.setAttribute('aria-expanded', 'true');

      const list: HTMLElement | null = menu.childElementCount > 0 ? menu.children[0] as HTMLElement : null;
      if (list && Core.stringToBool(list.dataset.scrollToActive || '')) {
        delete list.dataset.scrollToActive;

        let active: HTMLElement | null = null;
        for (let i = 0, length = list.childElementCount; i < length; i++) {
          if (list.children[i].classList.contains('active')) {
            active = list.children[i] as HTMLElement;
            break;
          }
        }

        if (active) {
          list.scrollTop = Math.max((active.offsetTop + active.clientHeight) - menu.clientHeight, 0);
        }
      }

      const itemList = menu.querySelector('.scrollableDropdownMenu');
      if (itemList !== null) {
        itemList.classList[(itemList.scrollHeight > itemList.clientHeight ? 'add' : 'remove')]('forceScrollbar');
      }

      notifyCallbacks(containerId, 'open');

      if (!disableAutoFocus) {
        menu.setAttribute('role', 'menu');
        menu.tabIndex = -1;
        menu.removeEventListener('keydown', dropdownMenuKeyDown);
        menu.addEventListener('keydown', dropdownMenuKeyDown);
        menu.querySelectorAll('li').forEach(listItem => {
          if (!listItem.clientHeight) return;
          if (firstListItem === null) firstListItem = listItem;
          else if (listItem.classList.contains('active')) firstListItem = listItem;

          listItem.setAttribute('role', 'menuitem');
          listItem.tabIndex = -1;
        });
      }

      UiDropdownSimple.setAlignment(dropdown, menu, alternateElement);

      if (firstListItem !== null) {
        firstListItem.focus();
      }
    }
  });

  window.WCF.Dropdown.Interactive.Handler.closeAll();

  return (event === null);
}

function handleKeyDown(event: KeyboardEvent): void {
  // <input> elements are not valid targets for drop-down menus. However, some developers
  // might still decide to combine them, in which case we try not to break things even more.
  const target = event.currentTarget as HTMLElement;
  if (target.nodeName === 'INPUT') {
    return;
  }

  if (event.key === 'Enter' || event.key === 'Space') {
    event.preventDefault();
    toggle(event);
  }
}

function dropdownMenuKeyDown(event: KeyboardEvent): void {
  let button, dropdown;

  const activeItem = document.activeElement as HTMLElement;
  if (activeItem.nodeName !== 'LI') {
    return;
  }

  if (event.key === 'ArrowDown' || event.key === 'ArrowUp' || event.key === 'End' || event.key === 'Home') {
    event.preventDefault();

    const listItems = Array.from<HTMLElement>(activeItem.closest('.dropdownMenu')!.querySelectorAll('li'));
    if (event.key === 'ArrowUp' || event.key === 'End') {
      listItems.reverse();
    }

    let newActiveItem: HTMLElement | null = null;
    const isValidItem = listItem => {
      return !listItem.classList.contains('dropdownDivider') && listItem.clientHeight > 0;
    };

    let activeIndex = listItems.indexOf(activeItem);
    if (event.key === 'End' || event.key === 'Home') {
      activeIndex = -1;
    }

    for (let i = activeIndex + 1; i < listItems.length; i++) {
      if (isValidItem(listItems[i])) {
        newActiveItem = listItems[i];
        break;
      }
    }

    if (newActiveItem === null) {
      for (let i = 0; i < listItems.length; i++) {
        if (isValidItem(listItems[i])) {
          newActiveItem = listItems[i];
          break;
        }
      }
    }

    if (newActiveItem !== null) {
      newActiveItem.focus();
    }
  } else if (event.key === 'Enter' || event.key === 'Space') {
    event.preventDefault();

    let target = activeItem;
    if (target.childElementCount === 1 && (target.children[0].nodeName === 'SPAN' || target.children[0].nodeName === 'A')) {
      target = target.children[0] as HTMLElement;
    }

    dropdown = _dropdowns.get(_activeTargetId);
    button = dropdown.querySelector('.dropdownToggle');

    const mouseEvent = dropdown.dataset.a11yMouseEvent || 'click';
    Core.triggerEvent(target, mouseEvent);

    if (button) button.focus();
  } else if (event.key === 'Escape' || event.key === 'Tab') {
    event.preventDefault();

    dropdown = _dropdowns.get(_activeTargetId);
    button = dropdown.querySelector('.dropdownToggle');
    // Remote controlled drop-down menus may not have a dedicated toggle button, instead the
    // `dropdown` element itself is the button.
    if (button === null && !dropdown.classList.contains('dropdown')) {
      button = dropdown;
    }

    toggle(null, _activeTargetId);
    if (button) button.focus();
  }
}

const UiDropdownSimple = {
  /**
   * Performs initial setup such as setting up dropdowns and binding listeners.
   */
  setup(): void {
    if (_didInit) return;
    _didInit = true;

    _menuContainer = document.createElement('div');
    _menuContainer.className = 'dropdownMenuContainer';
    document.body.appendChild(_menuContainer);

    _availableDropdowns = document.getElementsByClassName('dropdownToggle') as HTMLCollectionOf<HTMLElement>;

    UiDropdownSimple.initAll();

    UiCloseOverlay.add('WoltLabSuite/Core/Ui/Dropdown/Simple', UiDropdownSimple.closeAll);
    DomChangeListener.add('WoltLabSuite/Core/Ui/Dropdown/Simple', UiDropdownSimple.initAll);

    document.addEventListener('scroll', onScroll);

    // expose on window object for backward compatibility
    window.bc_wcfSimpleDropdown = this;
  },

  /**
   * Loops through all possible dropdowns and registers new ones.
   */
  initAll(): void {
    for (let i = 0, length = _availableDropdowns.length; i < length; i++) {
      UiDropdownSimple.init(_availableDropdowns[i], false);
    }
  },

  /**
   * Initializes a dropdown.
   */
  init(button: HTMLElement, isLazyInitialization: boolean | MouseEvent): boolean {
    UiDropdownSimple.setup();

    button.setAttribute('role', 'button');
    button.tabIndex = 0;
    button.setAttribute('aria-haspopup', 'true');
    button.setAttribute('aria-expanded', 'false');

    if (button.classList.contains('jsDropdownEnabled') || button.dataset.target) {
      return false;
    }

    const dropdown = DomTraverse.parentByClass(button, 'dropdown') as HTMLElement;
    if (dropdown === null) {
      throw new Error("Invalid dropdown passed, button '" + DomUtil.identify(button) + "' does not have a parent with .dropdown.");
    }

    const menu = DomTraverse.nextByClass(button, 'dropdownMenu') as HTMLElement;
    if (menu === null) {
      throw new Error("Invalid dropdown passed, button '" + DomUtil.identify(button) + "' does not have a menu as next sibling.");
    }

    // move menu into global container
    _menuContainer.appendChild(menu);

    const containerId = DomUtil.identify(dropdown);
    if (!_dropdowns.has(containerId)) {
      button.classList.add('jsDropdownEnabled');
      button.addEventListener('click', toggle);
      button.addEventListener('keydown', handleKeyDown);

      _dropdowns.set(containerId, dropdown);
      _menus.set(containerId, menu);

      if (!containerId.match(/^wcf\d+$/)) {
        menu.dataset.source = containerId;
      }

      // prevent page scrolling
      if (menu.childElementCount && menu.children[0].classList.contains('scrollableDropdownMenu')) {
        const child = menu.children[0] as HTMLElement;
        child.dataset.scrollToActive = 'true';

        let menuHeight: number | null = null;
        let menuRealHeight: number | null = null;
        child.addEventListener('wheel', event => {
          if (menuHeight === null) menuHeight = child.clientHeight;
          if (menuRealHeight === null) menuRealHeight = child.scrollHeight;

          // negative value: scrolling up
          if (event.deltaY < 0 && child.scrollTop === 0) {
            event.preventDefault();
          } else if (event.deltaY > 0 && (child.scrollTop + menuHeight === menuRealHeight)) {
            event.preventDefault();
          }
        }, {passive: false});
      }
    }

    button.dataset.target= containerId;

    if (isLazyInitialization) {
      setTimeout(() => {
        button.dataset.dropdownLazyInit = (isLazyInitialization instanceof MouseEvent) ? 'true' : 'false';

        Core.triggerEvent(button, 'click');

        setTimeout(() => {
          delete button.dataset.dropdownLazyInit;
        }, 10);
      }, 10);
    }

    return true;
  },

  /**
   * Initializes a remote-controlled dropdown.
   *
   * @param  {Element}  dropdown  dropdown wrapper element
   * @param  {Element}  menu    menu list element
   */
  initFragment(dropdown: HTMLElement, menu: HTMLElement): void {
    UiDropdownSimple.setup();

    const containerId = DomUtil.identify(dropdown);
    if (_dropdowns.has(containerId)) {
      return;
    }

    _dropdowns.set(containerId, dropdown);
    _menuContainer.appendChild(menu);

    _menus.set(containerId, menu);
  },

  /**
   * Registers a callback for open/close events.
   */
  registerCallback(containerId: string, callback: NotificationCallback): void {
    _callbacks.add(containerId, callback);
  },

  /**
   * Returns the requested dropdown wrapper element.
   */
  getDropdown(containerId: string): HTMLElement | undefined {
    return _dropdowns.get(containerId);
  },

  /**
   * Returns the requested dropdown menu list element.
   */
  getDropdownMenu(containerId: string): HTMLElement | undefined {
    return _menus.get(containerId);
  },

  /**
   * Toggles the requested dropdown between opened and closed.
   */
  toggleDropdown(containerId: string, referenceElement?: HTMLElement, disableAutoFocus?: boolean): void {
    toggle(null, containerId, referenceElement, disableAutoFocus);
  },

  /**
   * Calculates and sets the alignment of given dropdown.
   */
  setAlignment(dropdown: HTMLElement, dropdownMenu: HTMLElement, alternateElement?: HTMLElement): void {
    // check if button belongs to an i18n textarea
    const button = dropdown.querySelector('.dropdownToggle');
    const parent = (button !== null) ? button.parentNode as HTMLElement : null;
    let refDimensionsElement;
    if (parent && parent.classList.contains('inputAddonTextarea')) {
      refDimensionsElement = button;
    }

    UiAlignment.set(dropdownMenu, alternateElement || dropdown, {
      pointerClassNames: ['dropdownArrowBottom', 'dropdownArrowRight'],
      refDimensionsElement: refDimensionsElement || null,

      // alignment
      horizontal: dropdownMenu.dataset.dropdownAlignmentHorizontal === 'right' ? 'right' : 'left',
      vertical: dropdownMenu.dataset.dropdownAlignmentVertical === 'top' ? 'top' : 'bottom',

      allowFlip: dropdownMenu.dataset.dropdownAllowFlip as AllowFlip || 'both',
    });
  },

  /**
   * Calculates and sets the alignment of the dropdown identified by given id.
   */
  setAlignmentById(containerId: string): void {
    const dropdown = _dropdowns.get(containerId);
    if (dropdown === undefined) {
      throw new Error("Unknown dropdown identifier '" + containerId + "'.");
    }

    const menu = _menus.get(containerId) as HTMLElement;

    UiDropdownSimple.setAlignment(dropdown, menu);
  },

  /**
   * Returns true if target dropdown exists and is open.
   */
  isOpen(containerId: string): boolean {
    const menu = _menus.get(containerId);
    return (menu !== undefined && menu.classList.contains('dropdownOpen'));
  },

  /**
   * Opens the dropdown unless it is already open.
   */
  open(containerId: string, disableAutoFocus?: boolean): void {
    const menu = _menus.get(containerId);
    if (menu !== undefined && !menu.classList.contains('dropdownOpen')) {
      UiDropdownSimple.toggleDropdown(containerId, undefined, disableAutoFocus);
    }
  },

  /**
   * Closes the dropdown identified by given id without notifying callbacks.
   */
  close(containerId: string): void {
    const dropdown = _dropdowns.get(containerId);
    if (dropdown !== undefined) {
      dropdown.classList.remove('dropdownOpen');
      _menus.get(containerId)!.classList.remove('dropdownOpen');
    }
  },

  /**
   * Closes all dropdowns.
   */
  closeAll(): void {
    _dropdowns.forEach((function (dropdown, containerId) {
      if (dropdown.classList.contains('dropdownOpen')) {
        dropdown.classList.remove('dropdownOpen');
        _menus.get(containerId)!.classList.remove('dropdownOpen');

        notifyCallbacks(containerId, 'close');
      }
    }).bind(this));
  },

  /**
   * Destroys a dropdown identified by given id.
   */
  destroy(containerId: string): boolean {
    if (!_dropdowns.has(containerId)) {
      return false;
    }

    try {
      UiDropdownSimple.close(containerId);

      _menus.get(containerId)?.remove();
    } catch (e) {
      // the elements might not exist anymore thus ignore all errors while cleaning up
    }

    _menus.delete(containerId);
    _dropdowns.delete(containerId);

    return true;
  },
};

export = UiDropdownSimple;
