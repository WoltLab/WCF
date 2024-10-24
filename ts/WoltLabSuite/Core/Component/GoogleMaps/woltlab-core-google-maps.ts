/**
 * The `<woltlab-core-google-maps>` element creates a map via Google Maps.
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

let initCalled = false;
const callbackPromise = new Promise<void>((resolve) => {
  (window as any).woltlab_core_google_maps_callback = resolve;
});

const loadGoogleMaps = (apiKey: string): Promise<void> => {
  if (!initCalled) {
    const script = document.createElement("script");
    script.src =
      "https://maps.googleapis.com/maps/api/js?" +
      (apiKey ? `key=${apiKey}&` : "") +
      "callback=woltlab_core_google_maps_callback&libraries=marker";
    document.head.appendChild(script);
    initCalled = true;
  }

  return callbackPromise;
};

type Bounds = {
  southWest: {
    latitude: number;
    longitude: number;
  };
  northEast: {
    latitude: number;
    longitude: number;
  };
};

export class WoltlabCoreGoogleMapsElement extends HTMLElement {
  #map?: google.maps.Map;
  #mapLoaded: Promise<void>;
  #mapLoadedResolve?: () => void;
  #rendered = false;
  #geocoder?: google.maps.Geocoder;

  constructor() {
    super();

    this.#mapLoaded = new Promise<void>((resolve) => {
      this.#mapLoadedResolve = resolve;
    });
  }

  connectedCallback() {
    if (!this.hidden) {
      this.#render();
    }
  }

  attributeChangedCallback(name: string, oldValue: string | null, newValue: string | null): void {
    if (name === "hidden" && newValue === null) {
      this.#render();
    }
  }

  static get observedAttributes(): string[] {
    return ["hidden"];
  }

  #render(): void {
    if (this.#rendered) {
      return;
    }
    this.#validate();

    this.#rendered = true;

    void loadGoogleMaps(this.apiKey).then(() => {
      if (this.hasAttribute("access-user-location")) {
        navigator.geolocation.getCurrentPosition(
          (response) => {
            this.setAttribute("lat", response.coords.latitude.toString());
            this.setAttribute("lng", response.coords.longitude.toString());

            this.#initMap();
          },
          () => {
            this.#initMap();
          },
        );
      } else {
        this.#initMap();
      }
    });
  }

  #initMap(): void {
    this.#map = new google.maps.Map(this, {
      zoom: this.zoom,
      center: {
        lat: this.lat,
        lng: this.lng,
      },
    });

    void this.#setBounds();

    if (this.#mapLoadedResolve) {
      this.#mapLoadedResolve();
      this.#mapLoadedResolve = undefined;
    }
  }

  async #setBounds(): Promise<void> {
    await this.#mapLoaded;

    const bounds = this.bounds;
    if (bounds) {
      this.#map!.fitBounds(
        new google.maps.LatLngBounds(
          new google.maps.LatLng(bounds.southWest.latitude, bounds.southWest.longitude),
          new google.maps.LatLng(bounds.northEast.latitude, bounds.northEast.longitude),
        ),
      );
    }
  }

  #validate(): void {
    if (!this.apiKey) {
      throw new TypeError("Must provide an api key.");
    }
  }

  get apiKey(): string {
    return this.getAttribute("api-key") || "";
  }

  async getMap(): Promise<google.maps.Map> {
    await this.#mapLoaded;

    return this.#map!;
  }

  get lat(): number {
    return this.getAttribute("lat") ? parseFloat(this.getAttribute("lat")!) : 0;
  }

  get lng(): number {
    return this.getAttribute("lng") ? parseFloat(this.getAttribute("lng")!) : 0;
  }

  get zoom(): number {
    return this.getAttribute("zoom") ? parseInt(this.getAttribute("zoom")!) : 13;
  }

  get bounds(): Bounds | null {
    if (this.getAttribute("bounds")) {
      return JSON.parse(this.getAttribute("bounds")!) as Bounds;
    }

    return null;
  }

  async getGeocoder(): Promise<google.maps.Geocoder> {
    await this.#mapLoaded;

    if (this.#geocoder === undefined) {
      this.#geocoder = new google.maps.Geocoder();
    }

    return this.#geocoder;
  }
}

window.customElements.define("woltlab-core-google-maps", WoltlabCoreGoogleMapsElement);

export default WoltlabCoreGoogleMapsElement;
