/**
 * Stores the quote data.
 *
 * @author Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 * @woltlabExcludeBundle tiny
 */

import * as Core from "WoltLabSuite/Core/Core";
import { renderQuote } from "WoltLabSuite/Core/Api/Messages/RenderQuote";
import { getMessageAuthor } from "WoltLabSuite/Core/Api/Messages/Author";
import { refreshQuoteLists } from "WoltLabSuite/Core/Component/Quote/List";
import { resetRemovalQuotes } from "WoltLabSuite/Core/Api/Messages/ResetRemovalQuotes";
import { removeQuoteStatus } from "WoltLabSuite/Core/Component/Quote/Message";
import { dboAction } from "WoltLabSuite/Core/Ajax";

interface Message {
  objectID: number;
  time: string;
  title: string;
  link: string;
  authorID: number | null;
  author: string;
  avatar: string;
}

interface Quote {
  message: string;
  rawMessage?: string;
}

interface StorageData {
  quotes: Map<string, Map<string, Quote>>;
  messages: Map<string, Message>;
}

const STORAGE_KEY = Core.getStoragePrefix() + "quotes";
const usedQuotes = new Map<string, Set<string>>();

export async function legacySaveQuote(
  objectType: string,
  objectId: number,
  className: string,
  message: string,
): Promise<void> {
  const result = await dboAction("saveQuote", className)
    .objectIds([objectId])
    .payload({
      message,
      renderQuote: true,
    })
    .dispatch();

  debugger;
}

export async function saveQuote(
  objectType: string,
  objectId: number,
  objectClassName: string,
  message: string,
): Promise<Message & Quote & { uuid: string }> {
  const result = await getMessageAuthor(objectClassName, objectId);
  if (!result.ok) {
    throw new Error("Error fetching author data");
  }

  const uuid = storeQuote(objectType, result.value, {
    message,
  });

  refreshQuoteLists();

  return {
    ...result.value,
    message,
    uuid,
  };
}

export async function saveFullQuote(
  objectType: string,
  objectClassName: string,
  objectId: number,
): Promise<Message & Quote & { uuid: string }> {
  const result = await renderQuote(objectType, objectClassName, objectId);
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
    message: result.value.message!,
    rawMessage: result.value.rawMessage!,
  };

  const uuid = storeQuote(objectType, message, quote);

  refreshQuoteLists();

  return {
    ...message,
    ...quote,
    uuid,
  };
}

export function getQuotes(): Map<string, Map<string, Quote>> {
  return getStorage().quotes;
}

export function getMessage(objectType: string, objectId?: number): Message | undefined {
  const key = objectId ? getKey(objectType, objectId) : objectType;

  return getStorage().messages.get(key);
}

export function removeQuotes(uuids: string[]): void {
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
  refreshQuoteLists();

  void resetRemovalQuotes();
}

export function removeQuote(key: string, uuid: string): void {
  const storage = getStorage();
  if (!storage.quotes.has(key)) {
    return;
  }

  storage.quotes.get(key)!.delete(uuid);

  if (storage.quotes.get(key)!.size === 0) {
    storage.quotes.delete(key);
    storage.messages.delete(key);
  }

  saveStorage(storage);

  refreshQuoteLists();
}

export function markQuoteAsUsed(editorId: string, uuid: string): void {
  if (!usedQuotes.has(editorId)) {
    usedQuotes.set(editorId, new Set());
  }

  usedQuotes.get(editorId)!.add(uuid);
}

export function getUsedQuotes(editorId: string): Set<string> {
  return usedQuotes.get(editorId) ?? new Set();
}

export function clearQuotesForEditor(editorId: string): void {
  const storage = getStorage();
  const fullQuotes: string[] = [];

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
  refreshQuoteLists();

  fullQuotes.forEach((key) => {
    removeQuoteStatus(key);
  });
}

export function isFullQuoted(objectType: string, objectId: number): boolean {
  const key = getKey(objectType, objectId);
  const storage = getStorage();
  const quotes = storage.quotes.get(key);

  if (quotes === undefined) {
    return false;
  }

  return (
    Array.from(quotes).filter(([, quote]) => {
      if (quote.rawMessage !== undefined) {
        return true;
      }
    }).length > 0
  );
}

function storeQuote(objectType: string, message: Message, quote: Quote): string {
  const storage = getStorage();

  const key = getKey(objectType, message.objectID);
  if (!storage.quotes.has(key)) {
    storage.quotes.set(key, new Map());
  }

  storage.messages.set(key, message);

  for (const [uuid, q] of storage.quotes.get(key)!) {
    if ((q.rawMessage !== undefined && q.rawMessage === quote.rawMessage) || q.message === quote.message) {
      return uuid;
    }
  }

  const uuid = Core.getUuid();
  storage.quotes.get(key)!.set(uuid, quote);

  saveStorage(storage);

  return uuid;
}

export function getFullQuoteUuid(objectType: string, objectId: number): string | undefined {
  const storage = getStorage();
  const key = getKey(objectType, objectId);

  for (const [uuid, q] of storage.quotes.get(key)!) {
    if (q.rawMessage !== undefined && q.message !== undefined) {
      return uuid;
    }
  }

  return undefined;
}

function getStorage(): StorageData {
  const data = window.localStorage.getItem(STORAGE_KEY);

  return parseJson(data);
}

function parseJson(data: string | null): StorageData {
  if (!data) {
    return {
      quotes: new Map(),
      messages: new Map(),
    };
  }

  return JSON.parse(data, (key, value) => {
    if (key === "quotes") {
      const result = new Map<string, Map<string, Quote>>(value);
      for (const [key, quotes] of result) {
        result.set(key, new Map(quotes));
      }

      return result;
    } else if (key === "messages") {
      return new Map<string, Message>(value);
    }

    return value;
  });
}

export function getKey(objectType: string, objectId: number): string {
  return `${objectType}:${objectId}`;
}

function saveStorage(data: StorageData) {
  window.localStorage.setItem(
    STORAGE_KEY,
    JSON.stringify(data, (_key, value) => {
      if (value instanceof Map) {
        return Array.from(value.entries());
      }

      return value;
    }),
  );
}

window.addEventListener("storage", (event) => {
  refreshQuoteLists();

  const oldValue = parseJson(event.oldValue);
  const newValue = parseJson(event.newValue);

  // Update the quote status if the quote was removed in another tab
  for (const [key, quotes] of oldValue.quotes) {
    for (const [, quote] of quotes) {
      if (quote.rawMessage !== undefined && !newValue.quotes.has(key)) {
        removeQuoteStatus(key);
      }
    }
  }
});
