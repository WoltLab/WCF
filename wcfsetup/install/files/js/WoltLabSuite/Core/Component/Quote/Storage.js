/**
 * Stores the quote data.
 *
 * @author Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Core", "WoltLabSuite/Core/Api/Messages/RenderQuote", "WoltLabSuite/Core/Api/Messages/Author", "WoltLabSuite/Core/Component/Quote/List", "WoltLabSuite/Core/Api/Messages/ResetRemovalQuotes", "WoltLabSuite/Core/Component/Quote/Message"], function (require, exports, tslib_1, Core, RenderQuote_1, Author_1, List_1, ResetRemovalQuotes_1, Message_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.saveQuote = saveQuote;
    exports.saveFullQuote = saveFullQuote;
    exports.getQuotes = getQuotes;
    exports.getMessage = getMessage;
    exports.removeQuotes = removeQuotes;
    exports.removeQuote = removeQuote;
    exports.markQuoteAsUsed = markQuoteAsUsed;
    exports.getUsedQuotes = getUsedQuotes;
    exports.clearQuotesForEditor = clearQuotesForEditor;
    exports.isFullQuoted = isFullQuoted;
    exports.getFullQuoteUuid = getFullQuoteUuid;
    exports.getKey = getKey;
    Core = tslib_1.__importStar(Core);
    const STORAGE_KEY = Core.getStoragePrefix() + "quotes";
    const usedQuotes = new Map();
    async function saveQuote(objectType, objectId, objectClassName, message) {
        const result = await (0, Author_1.messageAuthor)(objectClassName, objectId);
        if (!result.ok) {
            throw new Error("Error fetching author data");
        }
        const uuid = storeQuote(objectType, result.value, {
            message,
        });
        (0, List_1.refreshQuoteLists)();
        return {
            ...result.value,
            message,
            uuid,
        };
    }
    async function saveFullQuote(objectType, objectClassName, objectId) {
        const result = await (0, RenderQuote_1.renderQuote)(objectType, objectClassName, objectId);
        if (!result.ok) {
            throw new Error("Error fetching quote data");
        }
        const message = {
            objectID: result.value.objectID,
            time: result.value.time,
            title: result.value.title,
            link: result.value.link,
            authorID: result.value.authorID,
            author: result.value.author,
            avatar: result.value.avatar,
        };
        const quote = {
            message: result.value.message,
            rawMessage: result.value.rawMessage,
        };
        const uuid = storeQuote(objectType, message, quote);
        (0, List_1.refreshQuoteLists)();
        return {
            ...message,
            ...quote,
            uuid,
        };
    }
    function getQuotes() {
        return getStorage().quotes;
    }
    function getMessage(objectType, objectId) {
        const key = objectId ? getKey(objectType, objectId) : objectType;
        return getStorage().messages.get(key);
    }
    function removeQuotes(uuids) {
        const storage = getStorage();
        for (const uuid of uuids) {
            for (const quotes of storage.quotes.values()) {
                quotes.delete(uuid);
            }
        }
        for (const [key, quotes] of storage.quotes) {
            if (quotes.size === 0) {
                storage.quotes.delete(key);
                storage.messages.delete(key);
            }
        }
        saveStorage(storage);
        (0, List_1.refreshQuoteLists)();
        void (0, ResetRemovalQuotes_1.resetRemovalQuotes)();
    }
    function removeQuote(key, uuid) {
        const storage = getStorage();
        if (!storage.quotes.has(key)) {
            return;
        }
        storage.quotes.get(key).delete(uuid);
        if (storage.quotes.get(key).size === 0) {
            storage.quotes.delete(key);
            storage.messages.delete(key);
        }
        saveStorage(storage);
        (0, List_1.refreshQuoteLists)();
    }
    function markQuoteAsUsed(editorId, uuid) {
        if (!usedQuotes.has(editorId)) {
            usedQuotes.set(editorId, new Set());
        }
        usedQuotes.get(editorId).add(uuid);
    }
    function getUsedQuotes(editorId) {
        return usedQuotes.get(editorId) ?? new Set();
    }
    function clearQuotesForEditor(editorId) {
        const storage = getStorage();
        const fullQuotes = [];
        usedQuotes.get(editorId)?.forEach((uuid) => {
            for (const [key, quotes] of storage.quotes) {
                const quote = quotes.get(uuid);
                if (quote?.rawMessage !== undefined) {
                    fullQuotes.push(key);
                }
                quotes.delete(uuid);
            }
        });
        usedQuotes.delete(editorId);
        for (const [key, quotes] of storage.quotes) {
            if (quotes.size === 0) {
                storage.quotes.delete(key);
                storage.messages.delete(key);
            }
        }
        saveStorage(storage);
        (0, List_1.refreshQuoteLists)();
        fullQuotes.forEach((key) => {
            (0, Message_1.removeQuoteStatus)(key);
        });
    }
    function isFullQuoted(objectType, objectId) {
        const key = getKey(objectType, objectId);
        const storage = getStorage();
        const quotes = storage.quotes.get(key);
        if (quotes === undefined) {
            return false;
        }
        return (Array.from(quotes).filter(([, quote]) => {
            if (quote.rawMessage !== undefined) {
                return true;
            }
        }).length > 0);
    }
    function storeQuote(objectType, message, quote) {
        const storage = getStorage();
        const key = getKey(objectType, message.objectID);
        if (!storage.quotes.has(key)) {
            storage.quotes.set(key, new Map());
        }
        storage.messages.set(key, message);
        for (const [uuid, q] of storage.quotes.get(key)) {
            if ((q.rawMessage !== undefined && q.rawMessage === quote.rawMessage) || q.message === quote.message) {
                return uuid;
            }
        }
        const uuid = Core.getUuid();
        storage.quotes.get(key).set(uuid, quote);
        saveStorage(storage);
        return uuid;
    }
    function getFullQuoteUuid(objectType, objectId) {
        const storage = getStorage();
        const key = getKey(objectType, objectId);
        for (const [uuid, q] of storage.quotes.get(key)) {
            if (q.rawMessage !== undefined && q.message !== undefined) {
                return uuid;
            }
        }
        return undefined;
    }
    function getStorage() {
        const data = window.localStorage.getItem(STORAGE_KEY);
        return parseJson(data);
    }
    function parseJson(data) {
        if (!data) {
            return {
                quotes: new Map(),
                messages: new Map(),
            };
        }
        return JSON.parse(data, (key, value) => {
            if (key === "quotes") {
                const result = new Map(value);
                for (const [key, quotes] of result) {
                    result.set(key, new Map(quotes));
                }
                return result;
            }
            else if (key === "messages") {
                return new Map(value);
            }
            return value;
        });
    }
    function getKey(objectType, objectId) {
        return `${objectType}:${objectId}`;
    }
    function saveStorage(data) {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(data, (_key, value) => {
            if (value instanceof Map) {
                return Array.from(value.entries());
            }
            return value;
        }));
    }
    window.addEventListener("storage", (event) => {
        (0, List_1.refreshQuoteLists)();
        const oldValue = parseJson(event.oldValue);
        const newValue = parseJson(event.newValue);
        // Update the quote status if the quote was removed in another tab
        for (const [key, quotes] of oldValue.quotes) {
            for (const [, quote] of quotes) {
                if (quote.rawMessage !== undefined && !newValue.quotes.has(key)) {
                    (0, Message_1.removeQuoteStatus)(key);
                }
            }
        }
    });
});
