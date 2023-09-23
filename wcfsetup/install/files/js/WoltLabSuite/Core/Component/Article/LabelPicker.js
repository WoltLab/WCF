define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    function toggleVisibility(showLabelGroupIds) {
        if (showLabelGroupIds === undefined) {
            showLabelGroupIds = [];
        }
        // TODO: Missing typings for `<woltlab-core-label-picker>`
        document.querySelectorAll("woltlab-core-label-picker").forEach((labelPicker) => {
            const groupId = parseInt(labelPicker.dataset.groupId);
            if (showLabelGroupIds.includes(groupId)) {
                labelPicker.disabled = false;
                labelPicker.closest("dl").hidden = false;
            }
            else {
                labelPicker.disabled = true;
                labelPicker.closest("dl").hidden = true;
            }
        });
    }
    function setup(categoryMapping) {
        if (categoryMapping.size === 0) {
            return;
        }
        const categoryId = document.getElementById("categoryID");
        function updateVisibility() {
            const value = parseInt(categoryId.value);
            toggleVisibility(categoryMapping.get(value));
        }
        categoryId.addEventListener("change", () => {
            updateVisibility();
        });
        updateVisibility();
    }
    exports.setup = setup;
});
