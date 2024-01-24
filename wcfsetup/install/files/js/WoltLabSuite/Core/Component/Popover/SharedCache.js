define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend"], function (require, exports, Backend_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.SharedCache = void 0;
    class SharedCache {
        #data = new Map();
        #endpoint;
        constructor(endpoint) {
            this.#endpoint = new URL(endpoint);
        }
        async get(objectId) {
            let content = this.#data.get(objectId);
            if (content !== undefined) {
                return content;
            }
            this.#endpoint.searchParams.set("id", objectId.toString());
            const response = await (0, Backend_1.prepareRequest)(this.#endpoint).get().fetchAsResponse();
            if (!response?.ok) {
                return "";
            }
            content = await response.text();
            this.#data.set(objectId, content);
            return content;
        }
    }
    exports.SharedCache = SharedCache;
    exports.default = SharedCache;
});
