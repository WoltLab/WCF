/**
 * Handles the configuration of the visible columns of grid views. The
 * configuration is not persisted, but it is part of the url.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "../../Helper/PromiseMutex", "../Dialog"], function (require, exports, PromiseMutex_1, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ColumnConfiguration = void 0;
    // eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
    class ColumnConfiguration extends EventTarget {
        #button;
        #visibleColumns;
        constructor(gridId, visibleColumns) {
            super();
            this.#visibleColumns = visibleColumns;
            this.#button = document.getElementById(`${gridId}_columnConfigurationButton`);
            this.#button?.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => this.#showDialog()));
        }
        /**
         * Returns the ids of the visible columns or `undefined` if the user
         * did not configure the columns.
         */
        getVisibleColumns() {
            return this.#visibleColumns !== undefined ? [...this.#visibleColumns] : undefined;
        }
        getQueryParameters() {
            if (this.#visibleColumns === undefined) {
                return [];
            }
            return this.#visibleColumns.map((column) => ["columns[]", column]);
        }
        /**
         * Reads the visible columns from the given search params and returns
         * true if they differ from the current configuration.
         */
        updateFromSearchParams(params) {
            const columns = [];
            params.forEach((value, key) => {
                if (/^columns\[\d*\]$/.test(key)) {
                    columns.push(value);
                }
            });
            const visibleColumns = columns.length > 0 ? columns : undefined;
            const hasChanged = visibleColumns?.join(",") !== this.#visibleColumns?.join(",");
            this.#visibleColumns = visibleColumns;
            return hasChanged;
        }
        async #showDialog() {
            const url = new URL(this.#button.dataset.endpoint);
            this.#visibleColumns?.forEach((column, index) => {
                url.searchParams.set(`columns[${index}]`, column);
            });
            const { ok, result } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(url.toString());
            if (ok) {
                // `null` represents the default configuration.
                this.#visibleColumns = result ?? undefined;
                this.dispatchEvent(new CustomEvent("grid-view:change"));
            }
        }
    }
    exports.ColumnConfiguration = ColumnConfiguration;
    exports.default = ColumnConfiguration;
});
