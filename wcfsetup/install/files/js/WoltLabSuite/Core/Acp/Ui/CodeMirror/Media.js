define(['WoltLabSuite/Core/Media/Manager/Editor'], function (MediaManagerEditor) {
    "use strict";
    function AcpUiCodeMirrorMedia(elementId) { this.init(elementId); }
    AcpUiCodeMirrorMedia.prototype = {
        init: function (elementId) {
            this._element = elById(elementId);
            var button = elById('codemirror-' + elementId + '-media');
            button.classList.add(button.id);
            new MediaManagerEditor({
                buttonClass: button.id,
                callbackInsert: this._insert.bind(this),
                editor: null
            });
        },
        _insert: function (mediaList, insertType, thumbnailSize) {
            var content = '';
            if (insertType === 'gallery') {
                var mediaIds = [];
                mediaList.forEach(function (item) {
                    mediaIds.push(item.mediaID);
                });
                content = '{{ mediaGallery="' + mediaIds.join(',') + '" }}';
            }
            else {
                mediaList.forEach(function (item) {
                    content += '{{ media="' + item.mediaID + '" size="' + thumbnailSize + '" }}';
                });
            }
            this._element.codemirror.replaceSelection(content);
        }
    };
    return AcpUiCodeMirrorMedia;
});
