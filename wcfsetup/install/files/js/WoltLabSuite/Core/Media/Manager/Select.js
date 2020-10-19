/**
 * Provides the media manager dialog for selecting media for input elements.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Media/Manager/Select
 */
define(['Core', 'Dom/Traverse', 'Dom/Util', 'Language', 'ObjectMap', 'Ui/Dialog', 'WoltLabSuite/Core/FileUtil', 'WoltLabSuite/Core/Media/Manager/Base'], function (Core, DomTraverse, DomUtil, Language, ObjectMap, UiDialog, FileUtil, MediaManagerBase) {
    "use strict";
    if (!COMPILER_TARGET_DEFAULT) {
        var Fake = function () { };
        Fake.prototype = {
            _addButtonEventListeners: function () { },
            _chooseMedia: function () { },
            _click: function () { },
            getMode: function () { },
            setupMediaElement: function () { },
            _removeMedia: function () { },
            _clipboardAction: function () { },
            _dialogClose: function () { },
            _dialogInit: function () { },
            _dialogSetup: function () { },
            _dialogShow: function () { },
            _editMedia: function () { },
            _editorClose: function () { },
            _editorSuccess: function () { },
            _removeClipboardCheckboxes: function () { },
            _setMedia: function () { },
            addMedia: function () { },
            getDialog: function () { },
            getOption: function () { },
            removeMedia: function () { },
            resetMedia: function () { },
            setMedia: function () { }
        };
        return Fake;
    }
    /**
     * @constructor
     */
    function MediaManagerSelect(options) {
        MediaManagerBase.call(this, options);
        this._activeButton = null;
        this._buttons = elByClass(this._options.buttonClass || 'jsMediaSelectButton');
        this._storeElements = new ObjectMap();
        for (var i = 0, length = this._buttons.length; i < length; i++) {
            var button = this._buttons[i];
            // only consider buttons with a proper store specified
            var store = elData(button, 'store');
            if (store) {
                var storeElement = elById(store);
                if (storeElement && storeElement.tagName === 'INPUT') {
                    this._buttons[i].addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
                    this._storeElements.set(button, storeElement);
                    // add remove button
                    var removeButton = elCreate('p');
                    removeButton.className = 'button';
                    DomUtil.insertAfter(removeButton, button);
                    var icon = elCreate('span');
                    icon.className = 'icon icon16 fa-times';
                    removeButton.appendChild(icon);
                    if (!storeElement.value)
                        elHide(removeButton);
                    removeButton.addEventListener(WCF_CLICK_EVENT, this._removeMedia.bind(this));
                }
            }
        }
    }
    Core.inherit(MediaManagerSelect, MediaManagerBase, {
        /**
         * @see	WoltLabSuite/Core/Media/Manager/Base#_addButtonEventListeners
         */
        _addButtonEventListeners: function () {
            MediaManagerSelect._super.prototype._addButtonEventListeners.call(this);
            if (!this._mediaManagerMediaList)
                return;
            var listItems = DomTraverse.childrenByTag(this._mediaManagerMediaList, 'LI');
            for (var i = 0, length = listItems.length; i < length; i++) {
                var listItem = listItems[i];
                var chooseIcon = elByClass('jsMediaSelectButton', listItem)[0];
                if (chooseIcon) {
                    chooseIcon.classList.remove('jsMediaSelectButton');
                    chooseIcon.addEventListener(WCF_CLICK_EVENT, this._chooseMedia.bind(this));
                }
            }
        },
        /**
         * Handles clicking on a media choose icon.
         *
         * @param	{Event}		event		click event
         */
        _chooseMedia: function (event) {
            if (this._activeButton === null) {
                throw new Error("Media cannot be chosen if no button is active.");
            }
            var media = this._media.get(~~elData(event.currentTarget, 'object-id'));
            // save selected media in store element
            elById(elData(this._activeButton, 'store')).value = media.mediaID;
            // display selected media
            var display = elData(this._activeButton, 'display');
            if (display) {
                var displayElement = elById(display);
                if (displayElement) {
                    if (media.isImage) {
                        displayElement.innerHTML = '<img src="' + (media.smallThumbnailLink ? media.smallThumbnailLink : media.link) + '" alt="' + (media.altText && media.altText[LANGUAGE_ID] ? media.altText[LANGUAGE_ID] : '') + '" />';
                    }
                    else {
                        var fileIcon = FileUtil.getIconNameByFilename(media.filename);
                        if (fileIcon) {
                            fileIcon = '-' + fileIcon;
                        }
                        displayElement.innerHTML = '<div class="box48" style="margin-bottom: 10px;">'
                            + '<span class="icon icon48 fa-file' + fileIcon + '-o"></span>'
                            + '<div class="containerHeadline">'
                            + '<h3>' + media.filename + '</h3>'
                            + '<p>' + media.formattedFilesize + '</p>'
                            + '</div>'
                            + '</div>';
                    }
                }
            }
            // show remove button
            elShow(this._activeButton.nextElementSibling);
            UiDialog.close(this);
        },
        /**
         * @see	WoltLabSuite/Core/Media/Manager/Base#_click
         */
        _click: function (event) {
            event.preventDefault();
            this._activeButton = event.currentTarget;
            MediaManagerSelect._super.prototype._click.call(this, event);
            if (!this._mediaManagerMediaList)
                return;
            var storeElement = this._storeElements.get(this._activeButton);
            var listItems = DomTraverse.childrenByTag(this._mediaManagerMediaList, 'LI'), listItem;
            for (var i = 0, length = listItems.length; i < length; i++) {
                listItem = listItems[i];
                if (storeElement.value && storeElement.value == elData(listItem, 'object-id')) {
                    listItem.classList.add('jsSelected');
                }
                else {
                    listItem.classList.remove('jsSelected');
                }
            }
        },
        /**
         * @see	WoltLabSuite/Core/Media/Manager/Base#getMode
         */
        getMode: function () {
            return 'select';
        },
        /**
         * @see	WoltLabSuite/Core/Media/Manager/Base#setupMediaElement
         */
        setupMediaElement: function (media, mediaElement) {
            MediaManagerSelect._super.prototype.setupMediaElement.call(this, media, mediaElement);
            // add media insertion icon
            var buttons = elBySel('nav.buttonGroupNavigation > ul', mediaElement);
            var listItem = elCreate('li');
            listItem.className = 'jsMediaSelectButton';
            elData(listItem, 'object-id', media.mediaID);
            buttons.appendChild(listItem);
            listItem.innerHTML = '<a><span class="icon icon16 fa-check jsTooltip" title="' + Language.get('wcf.media.button.select') + '"></span> <span class="invisible">' + Language.get('wcf.media.button.select') + '</span></a>';
        },
        /**
         * Handles clicking on the remove button.
         *
         * @param	{Event}		event		click event
         */
        _removeMedia: function (event) {
            event.preventDefault();
            var removeButton = event.currentTarget;
            elHide(removeButton);
            var button = removeButton.previousElementSibling;
            elById(elData(button, 'store')).value = 0;
            var display = elData(button, 'display');
            if (display) {
                var displayElement = elById(display);
                if (displayElement) {
                    displayElement.innerHTML = '';
                }
            }
        }
    });
    return MediaManagerSelect;
});
