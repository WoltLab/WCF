/**
 * Manages the share providers shown in the share dialogs.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Message/Share/Providers
 */

type Identifier = string;
type Label = string;
type Icon = string;
export type ShareProvider = [Identifier, Label, Icon];

const providers = new Set<ShareProvider>();

export function addShareProvider(shareProvider: ShareProvider): void {
  providers.add(shareProvider);
}

export function addShareProviders(shareProviders: ShareProvider[]): void {
  shareProviders.forEach((shareProvider) => addShareProvider(shareProvider));
}

export function getShareProviders(): ReadonlySet<ShareProvider> {
  return providers;
}
