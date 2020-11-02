/**
 * Sortable lists with optimized handling per device sizes.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Sortable/List
 */

import * as Core from "../../Core";
import * as UiScreen from "../Screen";

interface UnknownObject {
  [key: string]: unknown;
}

interface SortableListOptions {
  containerId: string;
  className: string;
  offset: number;
  options: UnknownObject;
  isSimpleSorting: boolean;
  additionalParameters: UnknownObject;
}

class UiSortableList {
  protected readonly _options: SortableListOptions;

  /**
   * Initializes the sortable list controller.
   */
  constructor(opts: Partial<SortableListOptions>) {
    this._options = Core.extend(
      {
        containerId: "",
        className: "",
        offset: 0,
        options: {},
        isSimpleSorting: false,
        additionalParameters: {},
      },
      opts
    ) as SortableListOptions;

    UiScreen.on("screen-sm-md", {
      match: () => this._enable(true),
      unmatch: () => this._disable(),
      setup: () => this._enable(true),
    });

    UiScreen.on("screen-lg", {
      match: () => this._enable(false),
      unmatch: () => this._disable(),
      setup: () => this._enable(false),
    });
  }

  /**
   * Enables sorting with an optional sort handle.
   */
  protected _enable(hasHandle: boolean): void {
    const options = this._options.options;
    if (hasHandle) {
      options.handle = ".sortableNodeHandle";
    }

    new window.WCF.Sortable.List(
      this._options.containerId,
      this._options.className,
      this._options.offset,
      options,
      this._options.isSimpleSorting,
      this._options.additionalParameters
    );
  }

  /**
   * Disables sorting for registered containers.
   */
  protected _disable(): void {
    window
      .jQuery(`#${this._options.containerId} .sortableList`)
      [this._options.isSimpleSorting ? "sortable" : "nestedSortable"]("destroy");
  }
}

Core.enableLegacyInheritance(UiSortableList);

export = UiSortableList;
