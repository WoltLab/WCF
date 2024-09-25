define(["require", "exports", "tslib", "../../../../Ajax", "../../../../Language", "../../../../Ui/Notification", "../../../../Component/Dialog"], function (require, exports, tslib_1, Ajax, Language, UiNotification, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Ajax = tslib_1.__importStar(Ajax);
    Language = tslib_1.__importStar(Language);
    UiNotification = tslib_1.__importStar(UiNotification);
    class AcpUiDevtoolsProjectSync {
        buttons = new Map();
        buttonStatus = new Map();
        buttonSyncAll = document.getElementById("devtoolsSyncAll");
        container = document.getElementById("syncPipMatches");
        pips = [];
        projectId;
        queue = [];
        constructor(projectId) {
            this.projectId = projectId;
            const restrictedSync = document.getElementById("syncShowOnlyMatches");
            restrictedSync.addEventListener("change", () => {
                this.container.classList.toggle("jsShowOnlyMatches");
            });
            const existingPips = [];
            const knownPips = [];
            const tmpPips = [];
            this.container
                .querySelectorAll(".jsHasPipTargets:not(.jsSkipTargetDetection)")
                .forEach((pip) => {
                const pluginName = pip.dataset.pluginName;
                const targets = [];
                this.container
                    .querySelectorAll(`.jsHasPipTargets[data-plugin-name="${pluginName}"] .jsInvokePip`)
                    .forEach((button) => {
                    const target = button.dataset.target;
                    targets.push(target);
                    button.addEventListener("click", (event) => {
                        event.preventDefault();
                        if (this.queue.length > 0) {
                            return;
                        }
                        this.sync(pluginName, target);
                    });
                    const identifier = this.getButtonIdentifier(pluginName, target);
                    this.buttons.set(identifier, button);
                    this.buttonStatus.set(identifier, this.container.querySelector(`.jsHasPipTargets[data-plugin-name="${pluginName}"] .jsInvokePipResult[data-target="${target}"]`));
                });
                const data = {
                    dependencies: JSON.parse(pip.dataset.syncDependencies),
                    pluginName,
                    targets,
                };
                if (data.dependencies.length > 0) {
                    tmpPips.push(data);
                }
                else {
                    this.pips.push(data);
                    knownPips.push(pluginName);
                }
                existingPips.push(pluginName);
            });
            let resolvedDependency = false;
            while (tmpPips.length > 0) {
                resolvedDependency = false;
                tmpPips.forEach((item, index) => {
                    if (resolvedDependency) {
                        return;
                    }
                    const openDependencies = item.dependencies.filter((dependency) => {
                        // Ignore any dependencies that are not present.
                        if (existingPips.indexOf(dependency) === -1) {
                            window.console.info(`The dependency "${dependency}" does not exist and has been ignored.`);
                            return false;
                        }
                        return !knownPips.includes(dependency);
                    });
                    if (openDependencies.length === 0) {
                        knownPips.push(item.pluginName);
                        this.pips.push(item);
                        tmpPips.splice(index, 1);
                        resolvedDependency = true;
                    }
                });
                if (!resolvedDependency) {
                    // We could not resolve any dependency, either because there is no more pip
                    // in `tmpPips` or we're facing a circular dependency. In case there are items
                    // left, we simply append them to the end and hope for the operation to
                    // complete anyway, despite unmatched dependencies.
                    tmpPips.forEach((pip) => {
                        window.console.warn("Unable to resolve dependencies for", pip);
                        this.pips.push(pip);
                    });
                    break;
                }
            }
            this.buttonSyncAll.addEventListener("click", () => this.syncAll());
        }
        sync(pluginName, target) {
            const identifier = this.getButtonIdentifier(pluginName, target);
            this.buttons.get(identifier).disabled = true;
            this.buttonStatus.get(identifier).innerHTML = '<fa-icon name="spinner" solid></fa-icon>';
            Ajax.api(this, {
                parameters: {
                    pluginName,
                    target,
                },
            });
        }
        syncAll() {
            if (this.buttonSyncAll.classList.contains("disabled")) {
                return;
            }
            this.buttonSyncAll.classList.add("disabled");
            this.queue = [];
            this.pips.forEach((pip) => {
                pip.targets.forEach((target) => {
                    this.queue.push([pip.pluginName, target]);
                });
            });
            this.syncNext();
        }
        syncNext() {
            if (this.queue.length === 0) {
                this.buttonSyncAll.classList.remove("disabled");
                UiNotification.show();
                return;
            }
            const next = this.queue.shift();
            this.sync(next[0], next[1]);
        }
        getButtonIdentifier(pluginName, target) {
            return `${pluginName}-${target}`;
        }
        _ajaxSuccess(data) {
            const identifier = this.getButtonIdentifier(data.returnValues.pluginName, data.returnValues.target);
            this.buttons.get(identifier).disabled = false;
            this.buttonStatus.get(identifier).innerHTML = data.returnValues.timeElapsed;
            if (data.returnValues.invokeAgain) {
                this.sync(data.returnValues.pluginName, data.returnValues.target);
            }
            else {
                this.syncNext();
            }
        }
        _ajaxFailure(data, responseText, xhr, requestData) {
            const identifier = this.getButtonIdentifier(requestData.parameters.pluginName, requestData.parameters.target);
            this.buttons.get(identifier).disabled = false;
            const buttonStatus = this.buttonStatus.get(identifier);
            buttonStatus.innerHTML = '<a href="#">' + Language.get("wcf.acp.devtools.sync.status.failure") + "</a>";
            buttonStatus.children[0].addEventListener("click", (event) => {
                event.preventDefault();
                const html = Ajax.getRequestObject(this).getErrorHtml(data, xhr);
                if (html instanceof HTMLIFrameElement) {
                    const dialog = (0, Dialog_1.dialogFactory)()
                        .fromHtml(`<div class="dialog__iframeContainer">${html.outerHTML}</div>`)
                        .asAlert();
                    dialog.show(Language.get("wcf.global.error.title"));
                    dialog.querySelector("dialog").classList.add("dialog--iframe");
                }
                else if (html) {
                    const dialog = (0, Dialog_1.dialogFactory)().fromHtml(html).asAlert();
                    dialog.show(Language.get("wcf.global.error.title"));
                }
            });
            this.buttonSyncAll.classList.remove("disabled");
            return true;
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "invoke",
                    className: "wcf\\data\\package\\installation\\plugin\\PackageInstallationPluginAction",
                    parameters: {
                        projectID: this.projectId,
                    },
                },
            };
        }
        _dialogSetup() {
            return {
                id: "devtoolsProjectSyncPipError",
                options: {
                    title: Language.get("wcf.global.error.title"),
                },
                source: null,
            };
        }
    }
    let acpUiDevtoolsProjectSync;
    function init(projectId) {
        if (!acpUiDevtoolsProjectSync) {
            acpUiDevtoolsProjectSync = new AcpUiDevtoolsProjectSync(projectId);
        }
    }
});
