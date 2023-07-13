/**
 * Provides functions to add markers to a map.
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle all
 */

import WoltlabCoreGoogleMapsElement from "./woltlab-core-google-maps";

import "./woltlab-core-google-maps";

export async function addMarker(
  element: WoltlabCoreGoogleMapsElement,
  latitude: number,
  longitude: number,
  title: string,
  focus?: boolean,
): Promise<void> {
  const map = await element.getMap();

  const marker = new google.maps.Marker({
    map,
    position: new google.maps.LatLng(latitude, longitude),
    title,
  });

  if (focus) {
    map.setCenter(marker.getPosition()!);
  }
}

export async function addDraggableMarker(element: WoltlabCoreGoogleMapsElement): Promise<google.maps.Marker>;
export async function addDraggableMarker(
  element: WoltlabCoreGoogleMapsElement,
  latitude: number,
  longitude: number,
): Promise<google.maps.Marker>;
export async function addDraggableMarker(
  element: WoltlabCoreGoogleMapsElement,
  latitude?: number,
  longitude?: number,
): Promise<google.maps.Marker> {
  const map = await element.getMap();

  if (latitude === undefined) {
    latitude = element.lat;
  }
  if (longitude === undefined) {
    longitude = element.lng;
  }

  const marker = new google.maps.Marker({
    map,
    position: new google.maps.LatLng(latitude, longitude),
    draggable: true,
    clickable: false,
  });

  map.setCenter(marker.getPosition()!);

  return marker;
}
