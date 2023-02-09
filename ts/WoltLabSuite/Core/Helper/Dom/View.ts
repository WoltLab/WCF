/**
 * Helper class to provide access to a fragment of the
 * DOM for use with the decorators `@DomElement` and
 * `@DomElementList`.
 *
 * This base class is required to allow the decorators
 * to make assumptions about how to access the DOM,
 * preserving the scope instead of running selectors
 * against the entire document.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import DomUtil from "../../Dom/Util";

type DataSourceForRoot = HTMLElement | DocumentFragment | string;

export abstract class DomView {
  readonly #root: HTMLElement;

  constructor(root: DataSourceForRoot) {
    this.#root = this.#createRootElement(root);
  }

  get root(): HTMLElement {
    return this.#root;
  }

  #createRootElement(root: DataSourceForRoot): HTMLElement {
    if (root instanceof HTMLElement) {
      return root;
    }

    if (typeof root === "string") {
      root = DomUtil.createFragmentFromHtml(root);
    }

    if (root.children.length === 1) {
      return root.children[0] as HTMLElement;
    }

    const div = document.createElement("div");
    div.append(root);

    return div;
  }
}

export default DomView;
