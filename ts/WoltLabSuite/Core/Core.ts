/**
 * Provides the basic core functionality.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

const _clone = function (variable: any): any {
  if (typeof variable === "object" && (Array.isArray(variable) || isPlainObject(variable))) {
    return _cloneObject(variable);
  }

  return variable;
};

const _cloneObject = function (obj: object | any[]): object | any[] | null {
  if (!obj) {
    return null;
  }

  if (Array.isArray(obj)) {
    return obj.slice();
  }

  const newObj = {};
  Object.keys(obj).forEach((key) => (newObj[key] = _clone(obj[key])));

  return newObj;
};

const _prefix = "wsc" + window.WCF_PATH.hashCode() + "-";

/**
 * Deep clones an object.
 */
export function clone(obj: object | any[]): object | any[] {
  return _clone(obj);
}

/**
 * Converts WCF 2.0-style URLs into the default URL layout.
 */
export function convertLegacyUrl(url: string): string {
  return url.replace(/^index\.php\/(.*?)\/\?/, (match: string, controller: string) => {
    const parts = controller.split(/([A-Z][a-z0-9]+)/);
    controller = "";
    for (let i = 0, length = parts.length; i < length; i++) {
      const part = parts[i].trim();
      if (part.length) {
        if (controller.length) {
          controller += "-";
        }
        controller += part.toLowerCase();
      }
    }

    return `index.php?${controller}/&`;
  });
}

/**
 * Merges objects with the first argument.
 *
 * @param  {object}  out    destination object
 * @param  {...object}  args  variable number of objects to be merged into the destination object
 * @return  {object}  destination object with all provided objects merged into
 */
export function extend(out: object, ...args: object[]): object {
  out = out || {};
  const newObj = clone(out);

  for (let i = 0, length = args.length; i < length; i++) {
    const obj = args[i];

    if (!obj) {
      continue;
    }

    Object.keys(obj).forEach((key) => {
      if (!Array.isArray(obj[key]) && typeof obj[key] === "object") {
        if (isPlainObject(obj[key])) {
          // object literals have the prototype of Object which in return has no parent prototype
          newObj[key] = extend(out[key], obj[key]);
        } else {
          newObj[key] = obj[key];
        }
      } else {
        newObj[key] = obj[key];
      }
    });
  }

  return newObj;
}

/**
 * Inherits the prototype methods from one constructor to another
 * constructor.
 *
 * Usage:
 *
 * function MyDerivedClass() {}
 * Core.inherit(MyDerivedClass, TheAwesomeBaseClass, {
 *      // regular prototype for `MyDerivedClass`
 *
 *      overwrittenMethodFromBaseClass: function(foo, bar) {
 *              // do stuff
 *
 *              // invoke parent
 *              MyDerivedClass._super.prototype.overwrittenMethodFromBaseClass.call(this, foo, bar);
 *      }
 * });
 *
 * @see  https://github.com/nodejs/node/blob/7d14dd9b5e78faabb95d454a79faa513d0bbc2a5/lib/util.js#L697-L735
 * @deprecated 5.4 Use the native `class` and `extends` keywords instead.
 */
export function inherit(constructor: new () => any, superConstructor: new () => any, propertiesObject: object): void {
  if (constructor === undefined || constructor === null) {
    throw new TypeError("The constructor must not be undefined or null.");
  }
  if (superConstructor === undefined || superConstructor === null) {
    throw new TypeError("The super constructor must not be undefined or null.");
  }
  if (superConstructor.prototype === undefined) {
    throw new TypeError("The super constructor must have a prototype.");
  }

  (constructor as any)._super = superConstructor;
  constructor.prototype = extend(
    Object.create(superConstructor.prototype, {
      constructor: {
        configurable: true,
        enumerable: false,
        value: constructor,
        writable: true,
      },
    }),
    propertiesObject || {},
  );
}

/**
 * Returns true if `obj` is an object literal.
 */
export function isPlainObject(obj: unknown): obj is Record<string, unknown> {
  if (typeof obj !== "object" || obj === null) {
    return false;
  }

  return Object.getPrototypeOf(obj) === Object.prototype;
}

/**
 * Returns the object's class name.
 */
export function getType(obj: object): string {
  return Object.prototype.toString.call(obj).replace(/^\[object (.+)]$/, "$1");
}

/**
 * Returns a RFC4122 version 4 compilant UUID.
 *
 * @see    http://stackoverflow.com/a/2117523
 */
export function getUuid(): string {
  return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, (c) => {
    const r = (Math.random() * 16) | 0,
      v = c == "x" ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
}

/**
 * Recursively serializes an object into an encoded URI parameter string.
 */
export function serialize(obj: object, prefix?: string): string {
  if (obj === null) {
    return "";
  }

  const parameters: string[] = [];
  Object.keys(obj).forEach((key) => {
    const parameterKey = prefix ? prefix + "[" + key + "]" : key;
    const value = obj[key];

    if (typeof value === "object") {
      parameters.push(serialize(value, parameterKey));
    } else {
      parameters.push(encodeURIComponent(parameterKey) + "=" + encodeURIComponent(value));
    }
  });

  return parameters.join("&");
}

/**
 * Triggers a custom or built-in event.
 */
export function triggerEvent(element: EventTarget, eventName: string): void {
  if (eventName === "click" && element instanceof HTMLElement) {
    element.click();
    return;
  }

  const event = new Event(eventName, {
    bubbles: true,
    cancelable: true,
  });

  element.dispatchEvent(event);
}

/**
 * Returns the unique prefix for the localStorage.
 */
export function getStoragePrefix(): string {
  return _prefix;
}

/**
 * Interprets a string value as a boolean value similar to the behavior of the
 * legacy functions `elAttrBool()` and `elDataBool()`.
 */
export function stringToBool(value: string | null): boolean {
  return value === "1" || value === "true";
}

type DebounceCallback = (...args: any[]) => void;

interface DebounceOptions {
  isImmediate: boolean;
}

/**
 * A function that emits a side effect and does not return anything.
 *
 * @see https://github.com/chodorowicz/ts-debounce/blob/62f30f2c3379b7b5e778fb1793e1fbfa17354894/src/index.ts
 */
export function debounce<F extends DebounceCallback>(
  func: F,
  waitMilliseconds = 50,
  options: DebounceOptions = {
    isImmediate: false,
  },
): (this: ThisParameterType<F>, ...args: Parameters<F>) => void {
  let timeoutId: ReturnType<typeof setTimeout> | undefined;

  return function (this: ThisParameterType<F>, ...args: Parameters<F>) {
    const doLater = () => {
      timeoutId = undefined;
      if (!options.isImmediate) {
        func.apply(this, args);
      }
    };

    const shouldCallNow = options.isImmediate && timeoutId === undefined;

    if (timeoutId !== undefined) {
      clearTimeout(timeoutId);
    }

    timeoutId = setTimeout(doLater, waitMilliseconds);

    if (shouldCallNow) {
      func.apply(this, args);
    }
  };
}

/**
 * @deprecated 6.0
 */
export function enableLegacyInheritance<T>(legacyClass: T): void {
  // This MUST NOT be an error to prevent bricking installations during the upgrade.
  console.error(
    "Relying on the legacy inheritance is no longer supported. Please migrate your code to use ES6 classes and inheritance.",
    legacyClass,
  );
}

export function getXsrfToken(): string {
  const cookies = document.cookie.split(";").map((c) => c.trim());
  const xsrfToken = cookies.find((c) => c.startsWith("XSRF-TOKEN="));

  if (xsrfToken === undefined) {
    return "COOKIE_NOT_FOUND";
  }

  const [_key, value] = xsrfToken.split(/=/, 2);

  return decodeURIComponent(value.trim());
}
