/**
 * Provides functions to add markers to a map.
 *
 * @author  Marcel Werk
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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

  const marker = new google.maps.marker.AdvancedMarkerElement({
    map,
    position: new google.maps.LatLng(latitude, longitude),
    title,
  });

  if (focus) {
    map.setCenter(marker.position!);
  }
}

export async function addDraggableMarker(
  element: WoltlabCoreGoogleMapsElement,
): Promise<google.maps.marker.AdvancedMarkerElement>;
export async function addDraggableMarker(
  element: WoltlabCoreGoogleMapsElement,
  latitude: number,
  longitude: number,
): Promise<google.maps.marker.AdvancedMarkerElement>;
export async function addDraggableMarker(
  element: WoltlabCoreGoogleMapsElement,
  latitude?: number,
  longitude?: number,
): Promise<google.maps.marker.AdvancedMarkerElement> {
  const map = await element.getMap();

  if (latitude === undefined) {
    latitude = element.lat;
  }
  if (longitude === undefined) {
    longitude = element.lng;
  }

  const marker = new google.maps.marker.AdvancedMarkerElement({
    map,
    position: new google.maps.LatLng(latitude, longitude),
    gmpDraggable: true,
    gmpClickable: false,
  });

  map.setCenter(marker.position!);

  return marker;
}
