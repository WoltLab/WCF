/**
 * Search interface for the package server lists.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Acp/Ui/Package/Search
 */
define(["require", "exports", "tslib", "./PrepareInstallation", "../../../Ajax", "../../../Core"], function (require, exports, tslib_1, PrepareInstallation_1, Ajax, Core) {
    "use strict";
    PrepareInstallation_1 = tslib_1.__importDefault(PrepareInstallation_1);
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    class AcpUiPackageSearch {
        constructor() {
            this.isBusy = false;
            this.isFirstRequest = true;
            this.lastValue = "";
            this.request = undefined;
            this.timerDelay = undefined;
            this.input = document.getElementById("packageSearchInput");
            this.installation = new PrepareInstallation_1.default();
            this.options = {
                delay: 300,
                minLength: 3,
            };
            this.resultList = document.getElementById("packageSearchResultList");
            this.resultListContainer = document.getElementById("packageSearchResultContainer");
            this.resultCounter = document.getElementById("packageSearchResultCounter");
            this.input.addEventListener("keyup", () => this.keyup());
        }
        keyup() {
            const value = this.input.value.trim();
            if (this.lastValue === value) {
                return;
            }
            this.lastValue = value;
            if (value.length < this.options.minLength) {
                this.setStatus("idle");
                return;
            }
            if (this.isFirstRequest) {
                if (!this.isBusy) {
                    this.isBusy = true;
                    this.setStatus("refreshDatabase");
                    Ajax.api(this, {
                        actionName: "refreshDatabase",
                    });
                }
                return;
            }
            if (this.timerDelay !== null) {
                window.clearTimeout(this.timerDelay);
            }
            this.timerDelay = window.setTimeout(() => {
                this.setStatus("loading");
                this.search(value);
            }, this.options.delay);
        }
        search(value) {
            if (this.request) {
                this.request.abortPrevious();
            }
            this.request = Ajax.api(this, {
                parameters: {
                    searchString: value,
                },
            });
        }
        setStatus(status) {
            this.resultListContainer.dataset.status = status;
        }
        _ajaxSuccess(data) {
            switch (data.actionName) {
                case "refreshDatabase":
                    this.isFirstRequest = false;
                    this.lastValue = "";
                    this.keyup();
                    break;
                case "search":
                    if (data.returnValues.count > 0) {
                        this.resultList.innerHTML = data.returnValues.template;
                        this.resultCounter.textContent = data.returnValues.count.toString();
                        this.setStatus("showResults");
                        this.resultList.querySelectorAll(".jsInstallPackage").forEach((button) => {
                            button.addEventListener("click", (event) => {
                                event.preventDefault();
                                button.blur();
                                this.installation.start(button.dataset.package, button.dataset.packageVersion);
                            });
                        });
                    }
                    else {
                        this.setStatus("noResults");
                    }
                    break;
            }
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "search",
                    className: "wcf\\data\\package\\update\\PackageUpdateAction",
                },
                silent: true,
            };
        }
    }
    Core.enableLegacyInheritance(AcpUiPackageSearch);
    return AcpUiPackageSearch;
});
