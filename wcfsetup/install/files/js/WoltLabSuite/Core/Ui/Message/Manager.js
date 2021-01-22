/**
 * Provides access and editing of message properties.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Message/Manager
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Core", "../../Dom/Change/Listener", "../../Language", "../../StringUtil"], function (require, exports, tslib_1, Ajax, Core, Listener_1, Language, StringUtil) {
    "use strict";
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Language = tslib_1.__importStar(Language);
    StringUtil = tslib_1.__importStar(StringUtil);
    class UiMessageManager {
        /**
         * Initializes a new manager instance.
         */
        constructor(options) {
            this._elements = new Map();
            this._options = Core.extend({
                className: "",
                selector: "",
            }, options);
            this.rebuild();
            Listener_1.default.add(`Ui/Message/Manager${this._options.className}`, this.rebuild.bind(this));
        }
        /**
         * Rebuilds the list of observed messages. You should call this method whenever a
         * message has been either added or removed from the document.
         */
        rebuild() {
            this._elements.clear();
            document.querySelectorAll(this._options.selector).forEach((element) => {
                this._elements.set(element.dataset.objectId, element);
            });
        }
        /**
         * Returns a boolean value for the given permission. The permission should not start
         * with "can" or "can-" as this is automatically assumed by this method.
         */
        getPermission(objectId, permission) {
            permission = "can" + StringUtil.ucfirst(permission);
            const element = this._elements.get(objectId);
            if (element === undefined) {
                throw new Error(`Unknown object id '${objectId}' for selector '${this._options.selector}'`);
            }
            return Core.stringToBool(element.dataset[StringUtil.toCamelCase(permission)] || "");
        }
        getPropertyValue(objectId, propertyName, asBool) {
            const element = this._elements.get(objectId);
            if (element === undefined) {
                throw new Error(`Unknown object id '${objectId}' for selector '${this._options.selector}'`);
            }
            const value = element.dataset[StringUtil.toCamelCase(propertyName)] || "";
            if (asBool) {
                return Core.stringToBool(value);
            }
            return value;
        }
        /**
         * Invokes a method for given message object id in order to alter its state or properties.
         */
        update(objectId, actionName, parameters) {
            Ajax.api(this, {
                actionName: actionName,
                parameters: parameters || {},
                objectIDs: [objectId],
            });
        }
        /**
         * Updates properties and states for given object ids. Keep in mind that this method does
         * not support setting individual properties per message, instead all property changes
         * are applied to all matching message objects.
         */
        updateItems(objectIds, data) {
            if (!Array.isArray(objectIds)) {
                objectIds = [objectIds];
            }
            objectIds.forEach((objectId) => {
                const element = this._elements.get(objectId);
                if (element === undefined) {
                    return;
                }
                Object.entries(data).forEach(([key, value]) => {
                    this._update(element, key, value);
                });
            });
        }
        /**
         * Bulk updates the properties and states for all observed messages at once.
         */
        updateAllItems(data) {
            const objectIds = Array.from(this._elements.keys());
            this.updateItems(objectIds, data);
        }
        /**
         * Sets or removes a message note identified by its unique CSS class.
         */
        setNote(objectId, className, htmlContent) {
            const element = this._elements.get(objectId);
            if (element === undefined) {
                throw new Error(`Unknown object id '${objectId}' for selector '${this._options.selector}'`);
            }
            const messageFooterNotes = element.querySelector(".messageFooterNotes");
            let note = messageFooterNotes.querySelector(`.${className}`);
            if (htmlContent) {
                if (note === null) {
                    note = document.createElement("p");
                    note.className = "messageFooterNote " + className;
                    messageFooterNotes.appendChild(note);
                }
                note.innerHTML = htmlContent;
            }
            else if (note !== null) {
                note.remove();
            }
        }
        /**
         * Updates a single property of a message element.
         */
        _update(element, propertyName, propertyValue) {
            element.dataset[propertyName] = propertyValue.toString();
            // handle special properties
            const propertyValueBoolean = propertyValue == 1 || propertyValue === true || propertyValue === "true";
            this._updateState(element, propertyName, propertyValue, propertyValueBoolean);
        }
        /**
         * Updates the message element's state based upon a property change.
         */
        _updateState(element, propertyName, propertyValue, propertyValueBoolean) {
            switch (propertyName) {
                case "isDeleted":
                    if (propertyValueBoolean) {
                        element.classList.add("messageDeleted");
                    }
                    else {
                        element.classList.remove("messageDeleted");
                    }
                    this._toggleMessageStatus(element, "jsIconDeleted", "wcf.message.status.deleted", "red", propertyValueBoolean);
                    break;
                case "isDisabled":
                    if (propertyValueBoolean) {
                        element.classList.add("messageDisabled");
                    }
                    else {
                        element.classList.remove("messageDisabled");
                    }
                    this._toggleMessageStatus(element, "jsIconDisabled", "wcf.message.status.disabled", "green", propertyValueBoolean);
                    break;
            }
        }
        /**
         * Toggles the message status bade for provided element.
         */
        _toggleMessageStatus(element, className, phrase, badgeColor, addBadge) {
            let messageStatus = element.querySelector(".messageStatus");
            if (messageStatus === null) {
                const messageHeaderMetaData = element.querySelector(".messageHeaderMetaData");
                if (messageHeaderMetaData === null) {
                    // can't find appropriate location to insert badge
                    return;
                }
                messageStatus = document.createElement("ul");
                messageStatus.className = "messageStatus";
                messageHeaderMetaData.insertAdjacentElement("afterend", messageStatus);
            }
            let badge = messageStatus.querySelector(`.${className}`);
            if (addBadge) {
                if (badge !== null) {
                    // badge already exists
                    return;
                }
                badge = document.createElement("span");
                badge.className = `badge label ${badgeColor} ${className}`;
                badge.textContent = Language.get(phrase);
                const listItem = document.createElement("li");
                listItem.appendChild(badge);
                messageStatus.appendChild(listItem);
            }
            else {
                if (badge === null) {
                    // badge does not exist
                    return;
                }
                badge.parentElement.remove();
            }
        }
        /**
         * Transforms camel-cased property names into their attribute equivalent.
         *
         * @deprecated 5.4 Access the value via `element.dataset` which uses camel-case.
         */
        _getAttributeName(propertyName) {
            if (propertyName.indexOf("-") !== -1) {
                return propertyName;
            }
            return propertyName
                .split(/([A-Z][a-z]+)/)
                .map((s) => s.trim().toLowerCase())
                .filter((s) => s.length > 0)
                .join("-");
        }
        _ajaxSuccess(_data) {
            // This should be an abstract method, but cannot be marked as such for backwards compatibility.
            throw new Error("Method _ajaxSuccess() must be implemented by deriving functions.");
        }
        _ajaxSetup() {
            return {
                data: {
                    className: this._options.className,
                },
            };
        }
    }
    Core.enableLegacyInheritance(UiMessageManager);
    return UiMessageManager;
});
