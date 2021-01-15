/**
 * Provides reliable checks for common key presses, uses `Event.key` on supported browsers
 * or the deprecated `Event.which`.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  EventKey (alias)
 * @module  WoltLabSuite/Core/Event/Key
 */

function _test(event: KeyboardEvent, key: string, which: number) {
  if (!(event instanceof Event)) {
    throw new TypeError("Expected a valid event when testing for key '" + key + "'.");
  }

  return event.key === key || event.which === which;
}

/**
 * Returns true if the pressed key equals 'ArrowDown'.
 *
 * @deprecated 5.4 Use `event.key === "ArrowDown"` instead.
 */
export function ArrowDown(event: KeyboardEvent): boolean {
  return _test(event, "ArrowDown", 40);
}

/**
 * Returns true if the pressed key equals 'ArrowLeft'.
 *
 * @deprecated 5.4 Use `event.key === "ArrowLeft"` instead.
 */
export function ArrowLeft(event: KeyboardEvent): boolean {
  return _test(event, "ArrowLeft", 37);
}

/**
 * Returns true if the pressed key equals 'ArrowRight'.
 *
 * @deprecated 5.4 Use `event.key === "ArrowRight"` instead.
 */
export function ArrowRight(event: KeyboardEvent): boolean {
  return _test(event, "ArrowRight", 39);
}

/**
 * Returns true if the pressed key equals 'ArrowUp'.
 *
 * @deprecated 5.4 Use `event.key === "ArrowUp"` instead.
 */
export function ArrowUp(event: KeyboardEvent): boolean {
  return _test(event, "ArrowUp", 38);
}

/**
 * Returns true if the pressed key equals 'Comma'.
 *
 * @deprecated 5.4 Use `event.key === ","` instead.
 */
export function Comma(event: KeyboardEvent): boolean {
  return _test(event, ",", 44);
}

/**
 * Returns true if the pressed key equals 'End'.
 *
 * @deprecated 5.4 Use `event.key === "End"` instead.
 */
export function End(event: KeyboardEvent): boolean {
  return _test(event, "End", 35);
}

/**
 * Returns true if the pressed key equals 'Enter'.
 *
 * @deprecated 5.4 Use `event.key === "Enter"` instead.
 */
export function Enter(event: KeyboardEvent): boolean {
  return _test(event, "Enter", 13);
}

/**
 * Returns true if the pressed key equals 'Escape'.
 *
 * @deprecated 5.4 Use `event.key === "Escape"` instead.
 */
export function Escape(event: KeyboardEvent): boolean {
  return _test(event, "Escape", 27);
}

/**
 * Returns true if the pressed key equals 'Home'.
 *
 * @deprecated 5.4 Use `event.key === "Home"` instead.
 */
export function Home(event: KeyboardEvent): boolean {
  return _test(event, "Home", 36);
}

/**
 * Returns true if the pressed key equals 'Space'.
 *
 * @deprecated 5.4 Use `event.key === "Space"` instead.
 */
export function Space(event: KeyboardEvent): boolean {
  return _test(event, "Space", 32);
}

/**
 * Returns true if the pressed key equals 'Tab'.
 *
 * @deprecated 5.4 Use `event.key === "Tab"` instead.
 */
export function Tab(event: KeyboardEvent): boolean {
  return _test(event, "Tab", 9);
}
