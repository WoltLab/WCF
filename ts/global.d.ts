import DatePicker from "WoltLabSuite/Core/Date/Picker";
import Devtools from "WoltLabSuite/Core/Devtools";
import DomUtil from "WoltLabSuite/Core/Dom/Util";
import * as ColorUtil from "WoltLabSuite/Core/ColorUtil";
import * as EventHandler from "WoltLabSuite/Core/Event/Handler";
import UiDropdownSimple from "WoltLabSuite/Core/Ui/Dropdown/Simple";
import "@woltlab/editor";
import "@woltlab/zxcvbn";
import { Reaction } from "WoltLabSuite/Core/Ui/Reaction/Data";
import type WoltlabCoreDialogElement from "WoltLabSuite/Core/Element/woltlab-core-dialog";
import type WoltlabCoreDialogControlElement from "WoltLabSuite/Core/Element/woltlab-core-dialog-control";
import type WoltlabCoreGoogleMapsElement from "WoltLabSuite/Core/Component/GoogleMaps/woltlab-core-google-maps";
import type WoltlabCoreFileElement from "WoltLabSuite/Core/Component/File/woltlab-core-file";
import { Picker as EmojiPickerElement } from "emoji-picker-element";

type Codepoint = string;
type HasRegularVariant = boolean;
type IconMetadata = [Codepoint, HasRegularVariant];

type IconSize = 16 | 24 | 32 | 48 | 64 | 96 | 128 | 144;
type LoadingIndicatorIconSize = 24 | 48 | 96;

declare global {
  interface WoltLabTemplate {
    fetch(v: object): string;
  }

  interface Window {
    Devtools?: typeof Devtools;
    ENABLE_DEBUG_MODE: boolean;
    ENABLE_DEVELOPER_TOOLS: boolean;
    LANGUAGE_ID: number;
    PAGE_TITLE: string;
    REACTION_TYPES: {
      [key: string]: Reaction;
    };
    TIME_NOW: number;
    WCF_PATH: string;
    WSC_API_URL: string;
    WSC_RPC_API_URL: string;

    getFontAwesome6Metadata: () => Map<string, IconMetadata>;
    getFontAwesome6IconMetadata: (name: string) => IconMetadata | undefined;

    jQuery: JQueryStatic;
    WCF: any;
    bc_wcfDomUtil: typeof DomUtil;
    bc_wcfSimpleDropdown: typeof UiDropdownSimple;
    __wcf_bc_colorPickerInit?: () => void;
    __wcf_bc_colorUtil: typeof ColorUtil;
    __wcf_bc_datePicker: typeof DatePicker;
    __wcf_bc_eventHandler: typeof EventHandler;

    WoltLabLanguage: {
      getPhrase(key: string, parameters?: object): string;
      registerPhrase(key: string, value: string): void;
    };

    WoltLabTemplate: new (template: string) => WoltLabTemplate;
  }

  interface String {
    hashCode: () => string;
  }

  interface JQuery {
    sortable(...args: any[]): unknown;

    messageTabMenu(...args: any[]): unknown;
  }

  type ArbitraryObject = Record<string, unknown>;

  class HTMLParsedElement extends HTMLElement {
    parsedCallback(): void;
  }

  interface FaBrand extends HTMLElement {
    size: IconSize;
  }

  interface FaIcon extends HTMLElement {
    readonly name: string;
    readonly solid: boolean;
    size: IconSize;

    setIcon: (name: string, forceSolid?: boolean) => void;
  }

  interface WoltlabCoreDateTime extends HTMLElement {
    static: boolean;

    get date(): Date;
    set date(date: Date);
  }

  interface WoltlabCoreFileUploadElement extends HTMLElement {
    get disabled(): boolean;
    set disabled(disabled: boolean);
    get maximumCount(): number;
    get maximumSize(): number;
  }

  interface WoltlabCoreLoadingIndicatorElement extends HTMLElement {
    get size(): LoadingIndicatorIconSize;
    set size(size: LoadingIndicatorIconSize);
    get hideText(): boolean;
    set hideText(hideText: boolean);
  }

  interface WoltlabCoreReactionSummaryElement extends HTMLElement {
    get objectId(): number;
    get objectType(): string;
    setData: (data: Map<number, number>, selectedReaction?: number) => void;
  }

  interface WoltlabCorePaginationElement extends HTMLElement {
    getLinkUrl(page: number): string;
    jumpToPage(page: number): void;
    get count(): number;
    set count(count: number);
    get page(): number;
    set page(page: number);
    get url(): string;
    set url(url: string);
  }

  interface HTMLElementTagNameMap {
    "fa-brand": FaBrand;
    "fa-icon": FaIcon;
    "woltlab-core-dialog": WoltlabCoreDialogElement;
    "woltlab-core-dialog-control": WoltlabCoreDialogControlElement;
    "woltlab-core-date-time": WoltlabCoreDateTime;
    "woltlab-core-file": WoltlabCoreFileElement;
    "woltlab-core-file-upload": WoltlabCoreFileUploadElement;
    "woltlab-core-loading-indicator": WoltlabCoreLoadingIndicatorElement;
    "woltlab-core-pagination": WoltlabCorePaginationElement;
    "woltlab-core-google-maps": WoltlabCoreGoogleMapsElement;
    "woltlab-core-reaction-summary": WoltlabCoreReactionSummaryElement;
    "woltlab-core-emoji-picker": EmojiPickerElement;
  }
}
