/**
 * Prevents concurrent runs of the callback promise by blocking subsequent calls
 * while the previous promise has not been resolved or rejected.
 *
 * @author Tim Düsterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

export function promiseMutex<T extends (...args: any[]) => Promise<unknown>>(
  promise: T,
): (...args: Parameters<T>) => boolean {
  let pending = false;

  return function (...args: Parameters<T>): boolean {
    if (pending) {
      return false;
    }

    pending = true;

    void promise(...args).finally(() => {
      pending = false;
    });

    return true;
  };
}
