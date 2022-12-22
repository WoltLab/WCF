/**
 * The `<woltlab-core-google-maps>` element creates a map via Google Maps.
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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
      "callback=woltlab_core_google_maps_callback";
    document.head.appendChild(script);
    initCalled = true;
  }

  return callbackPromise;
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

    void loadGoogleMaps(this.apiKey).then(() => {
      this.#map = new google.maps.Map(this, {
        zoom: this.zoom,
        center: {
          lat: this.lat,
          lng: this.lng,
        },
      });

      if (this.#mapLoadedResolve) {
        this.#mapLoadedResolve();
        this.#mapLoadedResolve = undefined;
      }
    });

    this.#rendered = true;
  }

  #validate(): void {
    if (!this.apiKey) {
      //throw new TypeError("Must provide an api key.");
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
