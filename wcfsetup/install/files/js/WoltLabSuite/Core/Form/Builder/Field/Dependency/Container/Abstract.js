/**
 * Abstract implementation of a handler for the visibility of container due the dependencies
 * of its children.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2
 */
define(["require", "exports", "tslib", "../Manager"], function (require, exports, tslib_1, DependencyManager) {
    "use strict";
    DependencyManager = tslib_1.__importStar(DependencyManager);
    class Abstract {
        _container;
        constructor(containerId) {
            this.init(containerId);
        }
        /**
         * Returns `true` if the dependency is met and thus if the container should be shown.
         */
        checkContainer() {
            throw new Error("Missing implementation of WoltLabSuite/Core/Form/Builder/Field/Dependency/Container.checkContainer!");
        }
        /**
         * Initializes a new container dependency handler for the container with the given id.
         */
        init(containerId) {
            if (typeof containerId !== "string") {
                throw new TypeError("Container id has to be a string.");
            }
            this._container = document.getElementById(containerId);
            if (this._container === null) {
                throw new Error("Unknown container with id '" + containerId + "'.");
            }
            DependencyManager.addContainerCheckCallback(() => this.checkContainer());
        }
    }
    return Abstract;
});
