/**
 * Manages the autosave process storing the current editor message in the local
 * storage to recover it on browser crash or accidental navigation.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Redactor/Autosave
 */
define(['Core', 'Devtools', 'EventHandler', 'Language', 'Dom/Traverse', './Metacode'], function (Core, Devtools, EventHandler, Language, DomTraverse, UiRedactorMetacode) {
    "use strict";
    if (!COMPILER_TARGET_DEFAULT) {
        var Fake = function () { };
        Fake.prototype = {
            init: function () { },
            getInitialValue: function () { },
            getMetaData: function () { },
            watch: function () { },
            destroy: function () { },
            clear: function () { },
            createOverlay: function () { },
            hideOverlay: function () { },
            _saveToStorage: function () { },
            _cleanup: function () { }
        };
        return Fake;
    }
    // time between save requests in seconds
    var _frequency = 15;
    /**
     * @param       {Element}       element         textarea element
     * @constructor
     */
    function UiRedactorAutosave(element) { this.init(element); }
    UiRedactorAutosave.prototype = {
        /**
         * Initializes the autosave handler and removes outdated messages from storage.
         *
         * @param       {Element}       element         textarea element
         */
        init: function (element) {
            this._container = null;
            this._metaData = {};
            this._editor = null;
            this._element = element;
            this._isActive = true;
            this._isPending = false;
            this._key = Core.getStoragePrefix() + elData(this._element, 'autosave');
            this._lastMessage = '';
            this._originalMessage = '';
            this._overlay = null;
            this._restored = false;
            this._timer = null;
            this._cleanup();
            // remove attribute to prevent Redactor's built-in autosave to kick in
            this._element.removeAttribute('data-autosave');
            var form = DomTraverse.parentByTag(this._element, 'FORM');
            if (form !== null) {
                form.addEventListener('submit', this.destroy.bind(this));
            }
            // export meta data
            EventHandler.add('com.woltlab.wcf.redactor2', 'getMetaData_' + this._element.id, (function (data) {
                for (var key in this._metaData) {
                    if (this._metaData.hasOwnProperty(key)) {
                        data[key] = this._metaData[key];
                    }
                }
            }).bind(this));
            // clear editor content on reset
            EventHandler.add('com.woltlab.wcf.redactor2', 'reset_' + this._element.id, this.hideOverlay.bind(this));
            document.addEventListener('visibilitychange', this._onVisibilityChange.bind(this));
        },
        _onVisibilityChange: function () {
            if (document.hidden) {
                this._isActive = false;
                this._isPending = true;
            }
            else {
                this._isActive = true;
                this._isPending = false;
            }
        },
        /**
         * Returns the initial value for the textarea, used to inject message
         * from storage into the editor before initialization.
         *
         * @return      {string}        message content
         */
        getInitialValue: function () {
            //noinspection JSUnresolvedVariable
            if (window.ENABLE_DEVELOPER_TOOLS && Devtools._internal_.editorAutosave() === false) {
                //noinspection JSUnresolvedVariable
                return this._element.value;
            }
            var value = '';
            try {
                value = window.localStorage.getItem(this._key);
            }
            catch (e) {
                window.console.warn("Unable to access local storage: " + e.message);
            }
            try {
                value = JSON.parse(value);
            }
            catch (e) {
                value = '';
            }
            // Check if the storage is outdated.
            if (value !== null && typeof value === 'object' && value.content) {
                var lastEditTime = ~~elData(this._element, 'autosave-last-edit-time');
                if (lastEditTime * 1000 <= value.timestamp) {
                    // Compare the stored version with the editor content, but only use the `innerText` property
                    // in order to ignore differences in whitespace, e. g. caused by indentation of HTML tags.
                    var div1 = elCreate('div');
                    div1.innerHTML = this._element.value;
                    var div2 = elCreate('div');
                    div2.innerHTML = value.content;
                    if (div1.innerText.trim() !== div2.innerText.trim()) {
                        //noinspection JSUnresolvedVariable
                        this._originalMessage = this._element.value;
                        this._restored = true;
                        this._metaData = value.meta || {};
                        return value.content;
                    }
                }
            }
            //noinspection JSUnresolvedVariable
            return this._element.value;
        },
        /**
         * Returns the stored meta data.
         *
         * @return      {Object}
         */
        getMetaData: function () {
            return this._metaData;
        },
        /**
         * Enables periodical save of editor contents to local storage.
         *
         * @param       {$.Redactor}    editor  redactor instance
         */
        watch: function (editor) {
            this._editor = editor;
            if (this._timer !== null) {
                throw new Error("Autosave timer is already active.");
            }
            this._timer = window.setInterval(this._saveToStorage.bind(this), _frequency * 1000);
            this._saveToStorage();
            this._isPending = false;
        },
        /**
         * Disables autosave handler, for use on editor destruction.
         */
        destroy: function () {
            this.clear();
            this._editor = null;
            window.clearInterval(this._timer);
            this._timer = null;
            this._isPending = false;
        },
        /**
         * Removed the stored message, for use after a message has been submitted.
         */
        clear: function () {
            this._metaData = {};
            this._lastMessage = '';
            try {
                window.localStorage.removeItem(this._key);
            }
            catch (e) {
                window.console.warn("Unable to remove from local storage: " + e.message);
            }
        },
        /**
         * Creates the autosave controls, used to keep or discard the restored draft.
         */
        createOverlay: function () {
            if (!this._restored) {
                return;
            }
            var container = elCreate('div');
            container.className = 'redactorAutosaveRestored active';
            var title = elCreate('span');
            title.textContent = Language.get('wcf.editor.autosave.restored');
            container.appendChild(title);
            var button = elCreate('a');
            button.className = 'jsTooltip';
            button.href = '#';
            button.title = Language.get('wcf.editor.autosave.keep');
            button.innerHTML = '<span class="icon icon16 fa-check green"></span>';
            button.addEventListener('click', (function (event) {
                event.preventDefault();
                this.hideOverlay();
            }).bind(this));
            container.appendChild(button);
            button = elCreate('a');
            button.className = 'jsTooltip';
            button.href = '#';
            button.title = Language.get('wcf.editor.autosave.discard');
            button.innerHTML = '<span class="icon icon16 fa-times red"></span>';
            button.addEventListener('click', (function (event) {
                event.preventDefault();
                // remove from storage
                this.clear();
                // set code
                var content = UiRedactorMetacode.convertFromHtml(this._editor.core.element()[0].id, this._originalMessage);
                this._editor.code.start(content);
                // set value
                this._editor.core.textarea().val(this._editor.clean.onSync(this._editor.$editor.html()));
                this.hideOverlay();
            }).bind(this));
            container.appendChild(button);
            this._editor.core.box()[0].appendChild(container);
            var callback = (function () {
                this._editor.core.editor()[0].removeEventListener('click', callback);
                this.hideOverlay();
            }).bind(this);
            this._editor.core.editor()[0].addEventListener('click', callback);
            this._container = container;
        },
        /**
         * Hides the autosave controls.
         */
        hideOverlay: function () {
            if (this._container !== null) {
                this._container.classList.remove('active');
                window.setTimeout((function () {
                    if (this._container !== null) {
                        elRemove(this._container);
                    }
                    this._container = null;
                    this._originalMessage = '';
                }).bind(this), 1000);
            }
        },
        /**
         * Saves the current message to storage unless there was no change.
         *
         * @protected
         */
        _saveToStorage: function () {
            if (!this._isActive) {
                if (!this._isPending)
                    return;
                // save one last time before suspending
                this._isPending = false;
            }
            //noinspection JSUnresolvedVariable
            if (window.ENABLE_DEVELOPER_TOOLS && Devtools._internal_.editorAutosave() === false) {
                //noinspection JSUnresolvedVariable
                return;
            }
            var content = this._editor.code.get();
            if (this._editor.utils.isEmpty(content)) {
                content = '';
            }
            if (this._lastMessage === content) {
                // break if content hasn't changed
                return;
            }
            if (content === '') {
                return this.clear();
            }
            try {
                EventHandler.fire('com.woltlab.wcf.redactor2', 'autosaveMetaData_' + this._element.id, this._metaData);
                window.localStorage.setItem(this._key, JSON.stringify({
                    content: content,
                    meta: this._metaData,
                    timestamp: Date.now()
                }));
                this._lastMessage = content;
            }
            catch (e) {
                window.console.warn("Unable to write to local storage: " + e.message);
            }
        },
        /**
         * Removes stored messages older than one week.
         *
         * @protected
         */
        _cleanup: function () {
            var oneWeekAgo = Date.now() - (7 * 24 * 3600 * 1000), removeKeys = [];
            var i, key, length, value;
            for (i = 0, length = window.localStorage.length; i < length; i++) {
                key = window.localStorage.key(i);
                // check if key matches our prefix
                if (key.indexOf(Core.getStoragePrefix()) !== 0) {
                    continue;
                }
                try {
                    value = window.localStorage.getItem(key);
                }
                catch (e) {
                    window.console.warn("Unable to access local storage: " + e.message);
                }
                try {
                    value = JSON.parse(value);
                }
                catch (e) {
                    value = { timestamp: 0 };
                }
                if (!value || value.timestamp < oneWeekAgo) {
                    removeKeys.push(key);
                }
            }
            for (i = 0, length = removeKeys.length; i < length; i++) {
                try {
                    window.localStorage.removeItem(removeKeys[i]);
                }
                catch (e) {
                    window.console.warn("Unable to remove from local storage: " + e.message);
                }
            }
        }
    };
    return UiRedactorAutosave;
});
