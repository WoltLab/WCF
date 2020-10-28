/**
 * Handles main menu overflow and a11y.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Page/Header/Menu
 */

import * as Environment from '../../../Environment';
import * as Language from '../../../Language';
import * as UiScreen from '../../Screen';

let _enabled = false;

let _buttonShowNext: HTMLAnchorElement;
let _buttonShowPrevious: HTMLAnchorElement;
let _firstElement: HTMLElement;
let _menu: HTMLElement;

let _marginLeft = 0;
let _invisibleLeft: HTMLElement[] = [];
let _invisibleRight: HTMLElement[] = [];

/**
 * Enables the overflow handler.
 */
function enable(): void {
  _enabled = true;

  // Safari waits three seconds for a font to be loaded which causes the header menu items
  // to be extremely wide while waiting for the font to be loaded. The extremely wide menu
  // items in turn can cause the overflow controls to be shown even if the width of the header
  // menu, after the font has been loaded successfully, does not require them. This width
  // issue results in the next button being shown for a short time. To circumvent this issue,
  // we wait a second before showing the obverflow controls in Safari.
  // see https://webkit.org/blog/6643/improved-font-loading/
  if (Environment.browser() === 'safari') {
    window.setTimeout(rebuildVisibility, 1000);
  } else {
    rebuildVisibility();

    // IE11 sometimes suffers from a timing issue
    window.setTimeout(rebuildVisibility, 1000);
  }
}

/**
 * Disables the overflow handler.
 */
function disable(): void {
  _enabled = false;
}

/**
 * Displays the next three menu items.
 */
function showNext(event: MouseEvent): void {
  event.preventDefault();

  if (_invisibleRight.length) {
    const showItem = _invisibleRight.slice(0, 3).pop()!;
    setMarginLeft(_menu.clientWidth - (showItem.offsetLeft + showItem.clientWidth));

    if (_menu.lastElementChild === showItem) {
      _buttonShowNext.classList.remove('active');
    }

    _buttonShowPrevious.classList.add('active');
  }
}

/**
 * Displays the previous three menu items.
 */
function showPrevious(event: MouseEvent): void {
  event.preventDefault();

  if (_invisibleLeft.length) {
    const showItem = _invisibleLeft.slice(-3)[0];
    setMarginLeft(showItem.offsetLeft * -1);

    if (_menu.firstElementChild === showItem) {
      _buttonShowPrevious.classList.remove('active');
    }

    _buttonShowNext.classList.add('active');
  }
}

/**
 * Sets the first item's margin-left value that is
 * used to move the menu contents around.
 */
function setMarginLeft(offset: number): void {
  _marginLeft = Math.min(_marginLeft + offset, 0);

  _firstElement.style.setProperty('margin-left', _marginLeft + 'px', '');
}

/**
 * Toggles button overlays and rebuilds the list
 * of invisible items from left to right.
 */
function rebuildVisibility(): void {
  if (!_enabled) return;

  _invisibleLeft = [];
  _invisibleRight = [];

  const menuWidth = _menu.clientWidth;
  if (_menu.scrollWidth > menuWidth || _marginLeft < 0) {
    Array.from(_menu.children).forEach((child: HTMLElement) => {
      const offsetLeft = child.offsetLeft;
      if (offsetLeft < 0) {
        _invisibleLeft.push(child);
      } else if (offsetLeft + child.clientWidth > menuWidth) {
        _invisibleRight.push(child);
      }
    });
  }

  _buttonShowPrevious.classList[(_invisibleLeft.length ? 'add' : 'remove')]('active');
  _buttonShowNext.classList[(_invisibleRight.length ? 'add' : 'remove')]('active');
}

/**
 * Builds the UI and binds the event listeners.
 */
function setup(): void {
  setupOverflow();
  setupA11y();
}

/**
 * Setups overflow handling.
 */
function setupOverflow(): void {
  const menuParent = _menu.parentElement!;

  _buttonShowNext = document.createElement('a');
  _buttonShowNext.className = 'mainMenuShowNext';
  _buttonShowNext.href = '#';
  _buttonShowNext.innerHTML = '<span class="icon icon32 fa-angle-right"></span>';
  _buttonShowNext.setAttribute('aria-hidden', 'true');
  _buttonShowNext.addEventListener('click', showNext);

  menuParent.appendChild(_buttonShowNext);

  _buttonShowPrevious = document.createElement('a');
  _buttonShowPrevious.className = 'mainMenuShowPrevious';
  _buttonShowPrevious.href = '#';
  _buttonShowPrevious.innerHTML = '<span class="icon icon32 fa-angle-left"></span>';
  _buttonShowPrevious.setAttribute('aria-hidden', 'true');
  _buttonShowPrevious.addEventListener('click', showPrevious);

  menuParent.insertBefore(_buttonShowPrevious, menuParent.firstChild);

  _firstElement.addEventListener('transitionend', rebuildVisibility);

  window.addEventListener('resize', () => {
    _firstElement.style.setProperty('margin-left', '0px', '');
    _marginLeft = 0;

    rebuildVisibility();
  });

  enable();
}

/**
 * Setups a11y improvements.
 */
function setupA11y(): void {
  _menu.querySelectorAll('.boxMenuHasChildren').forEach(element => {
    const link = element.querySelector('.boxMenuLink')!;
    link.setAttribute('aria-haspopup', 'true');
    link.setAttribute('aria-expanded', 'false');

    const showMenuButton = document.createElement('button');
    showMenuButton.className = 'visuallyHidden';
    showMenuButton.tabIndex = 0;
    showMenuButton.setAttribute('role', 'button');
    showMenuButton.setAttribute('aria-label', Language.get('wcf.global.button.showMenu'));
    element.insertBefore(showMenuButton, link.nextSibling);

    let showMenu = false;
    showMenuButton.addEventListener('click', () => {
      showMenu = !showMenu;
      link.setAttribute('aria-expanded', showMenu ? 'true' : 'false');
      showMenuButton.setAttribute('aria-label', Language.get(showMenu ? 'wcf.global.button.hideMenu' : 'wcf.global.button.showMenu'));
    });
  });
}

/**
 * Initializes the main menu overflow handling.
 */
export function init(): void {
  const menu = document.querySelector('.mainMenu .boxMenu') as HTMLElement;
  const firstElement = (menu && menu.childElementCount) ? menu.children[0] as HTMLElement : null;
  if (firstElement === null) {
    throw new Error("Unable to find the main menu.");
  }

  _menu = menu;
  _firstElement = firstElement;

  UiScreen.on('screen-lg', {
    match: enable,
    unmatch: disable,
    setup: setup,
  });
}
