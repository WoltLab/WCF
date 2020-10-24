/**
 * Callback-based pagination.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Pagination
 */

import * as Core from '../Core';
import * as Language from '../Language';
import * as StringUtil from '../StringUtil';
import * as UiPageJumpTo from './Page/JumpTo';

class UiPagination {
  /**
   * maximum number of displayed page links, should match the PHP implementation
   */
  static readonly showLinks = 11;

  private activePage: number;
  private readonly maxPage: number;

  private readonly element: HTMLElement;

  private readonly callbackSwitch: CallbackSwitch | null = null;
  private readonly callbackShouldSwitch: CallbackShouldSwitch | null = null;

  /**
   * Initializes the pagination.
   *
   * @param  {Element}  element    container element
   * @param  {object}  options    list of initialization options
   */
  constructor(element: HTMLElement, options: PaginationOptions) {
    this.element = element;
    this.activePage = options.activePage;
    this.maxPage = options.maxPage;
    if (typeof options.callbackSwitch === 'function') {
      this.callbackSwitch = options.callbackSwitch;
    }
    if (typeof options.callbackShouldSwitch === 'function') {
      this.callbackShouldSwitch = options.callbackShouldSwitch;
    }

    this.element.classList.add('pagination');
    this.rebuild();
  }

  /**
   * Rebuilds the entire pagination UI.
   */
  private rebuild() {
    let hasHiddenPages = false;

    // clear content
    this.element.innerHTML = '';

    const list = document.createElement('ul');
    let listItem = document.createElement('li');
    listItem.className = 'skip';
    list.appendChild(listItem);

    let iconClassNames = 'icon icon24 fa-chevron-left';
    if (this.activePage > 1) {
      const link = document.createElement('a');
      link.className = iconClassNames + ' jsTooltip';
      link.href = '#';
      link.title = Language.get('wcf.global.page.previous');
      link.rel = 'prev';
      listItem.appendChild(link);
      link.addEventListener('click', this.switchPage.bind(this, this.activePage - 1));
    } else {
      listItem.innerHTML = '<span class="' + iconClassNames + '"></span>';
      listItem.classList.add('disabled');
    }

    // add first page
    list.appendChild(this.createLink(1));

    // calculate page links
    let maxLinks = UiPagination.showLinks - 4;
    let linksBefore = this.activePage - 2;
    if (linksBefore < 0) {
      linksBefore = 0;
    }

    let linksAfter = this.maxPage - (this.activePage + 1);
    if (linksAfter < 0) {
      linksAfter = 0;
    }
    if (this.activePage > 1 && this.activePage < this.maxPage) {
      maxLinks--;
    }

    const half = maxLinks / 2;
    let left = this.activePage;
    let right = this.activePage;
    if (left < 1) {
      left = 1;
    }
    if (right < 1) {
      right = 1;
    }
    if (right > this.maxPage - 1) {
      right = this.maxPage - 1;
    }

    if (linksBefore >= half) {
      left -= half;
    } else {
      left -= linksBefore;
      right += half - linksBefore;
    }

    if (linksAfter >= half) {
      right += half;
    } else {
      right += linksAfter;
      left -= half - linksAfter;
    }

    right = Math.ceil(right);
    left = Math.ceil(left);
    if (left < 1) {
      left = 1;
    }
    if (right > this.maxPage) {
      right = this.maxPage;
    }

    // left ... links
    const jumpToHtml = '<a class="jsTooltip" title="' + Language.get('wcf.page.jumpTo') + '">&hellip;</a>';
    if (left > 1) {
      if (left - 1 < 2) {
        list.appendChild(this.createLink(2));
      } else {
        listItem = document.createElement('li');
        listItem.className = 'jumpTo';
        listItem.innerHTML = jumpToHtml;
        list.appendChild(listItem);
        hasHiddenPages = true;
      }
    }

    // visible links
    for (let i = left + 1; i < right; i++) {
      list.appendChild(this.createLink(i));
    }

    // right ... links
    if (right < this.maxPage) {
      if (this.maxPage - right < 2) {
        list.appendChild(this.createLink(this.maxPage - 1));
      } else {
        listItem = document.createElement('li');
        listItem.className = 'jumpTo';
        listItem.innerHTML = jumpToHtml;
        list.appendChild(listItem);
        hasHiddenPages = true;
      }
    }

    // add last page
    list.appendChild(this.createLink(this.maxPage));

    // add next button
    listItem = document.createElement('li');
    listItem.className = 'skip';
    list.appendChild(listItem);
    iconClassNames = 'icon icon24 fa-chevron-right';
    if (this.activePage < this.maxPage) {
      const link = document.createElement('a');
      link.className = iconClassNames + ' jsTooltip';
      link.href = '#';
      link.title = Language.get('wcf.global.page.next');
      link.rel = 'next';
      listItem.appendChild(link);
      link.addEventListener('click', this.switchPage.bind(this, this.activePage + 1));
    } else {
      listItem.innerHTML = '<span class="' + iconClassNames + '"></span>';
      listItem.classList.add('disabled');
    }

    if (hasHiddenPages) {
      list.dataset.pages = this.maxPage.toString();
      UiPageJumpTo.init(list, this.switchPage.bind(this));
    }

    this.element.appendChild(list);
  }

  /**
   * Creates a link to a specific page.
   */
  private createLink(pageNo: number): HTMLElement {
    const listItem = document.createElement('li');
    if (pageNo !== this.activePage) {
      const link = document.createElement('a');
      link.textContent = StringUtil.addThousandsSeparator(pageNo);
      link.addEventListener('click', this.switchPage.bind(this, pageNo));
      listItem.appendChild(link);
    } else {
      listItem.classList.add('active');
      listItem.innerHTML = '<span>' + StringUtil.addThousandsSeparator(pageNo) + '</span><span class="invisible">' + Language.get('wcf.page.pagePosition', {
        pageNo: pageNo,
        pages: this.maxPage,
      }) + '</span>';
    }
    return listItem;
  }

  /**
   * Returns the active page.
   */
  getActivePage(): number {
    return this.activePage;
  }

  /**
   * Returns the pagination Ui element.
   */
  getElement(): HTMLElement {
    return this.element;
  }

  /**
   * Returns the maximum page.
   */
  getMaxPage(): number {
    return this.maxPage;
  }

  /**
   * Switches to given page number.
   */
  switchPage(pageNo: number, event?: MouseEvent): void {
    if (event instanceof MouseEvent) {
      event.preventDefault();

      const target = event.currentTarget as HTMLElement;
      // force tooltip to vanish and strip positioning
      if (target && target.dataset.tooltip) {
        const tooltip = document.getElementById('balloonTooltip');
        if (tooltip) {
          Core.triggerEvent(target, 'mouseleave');
          tooltip.style.removeProperty('top');
          tooltip.style.removeProperty('bottom');
        }
      }
    }

    pageNo = ~~pageNo;
    if (pageNo > 0 && this.activePage !== pageNo && pageNo <= this.maxPage) {
      if (this.callbackShouldSwitch !== null) {
        if (!this.callbackShouldSwitch(pageNo)) {
          return;
        }
      }

      this.activePage = pageNo;
      this.rebuild();

      if (this.callbackSwitch !== null) {
        this.callbackSwitch(pageNo);
      }
    }
  }
}

export = UiPagination

type CallbackSwitch = (pageNo: number) => void;
type CallbackShouldSwitch = (pageNo: number) => boolean;

interface PaginationOptions {
  activePage: number;
  maxPage: number;
  callbackShouldSwitch?: CallbackShouldSwitch | null,
  callbackSwitch?: CallbackShouldSwitch | null,
}
