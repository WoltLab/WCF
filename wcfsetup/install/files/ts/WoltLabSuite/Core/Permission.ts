/**
 * Manages user permissions.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Permission (alias)
 * @module  WoltLabSuite/Core/Permission
 */

const _permissions = new Map<string, boolean>();

/**
 * Adds a single permission to the store.
 */
export function add(permission: string, value: boolean): void {
  if (typeof (value as any) !== 'boolean') {
    throw new TypeError('The permission value has to be boolean.');
  }

  _permissions.set(permission, value);
}

/**
 * Adds all the permissions in the given object to the store.
 */
export function addObject(object: PermissionObject): void {
  for (const key in object) {
    if (object.hasOwnProperty(key)) {
      add(key, object[key]);
    }
  }
}


/**
 * Returns the value of a permission.
 *
 * If the permission is unknown, false is returned.
 */
export function get(permission: string): boolean {
  if (_permissions.has(permission)) {
    return _permissions.get(permission)!;
  }

  return false;
}

interface PermissionObject {
  [key: string]: boolean;
}
