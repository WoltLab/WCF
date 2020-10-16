/**
 * Versatile AJAX request handling.
 *
 * In case you want to issue JSONP requests, please use `AjaxJsonp` instead.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  AjaxRequest (alias)
 * @module  WoltLabSuite/Core/Ajax/Request
 */

import * as AjaxStatus from './Status';
import { ResponseData, RequestOptions, RequestData } from './Data';
import * as Core from '../Core';
import DomChangeListener from '../Dom/Change/Listener';

let _didInit = false;
let _ignoreAllErrors = false;

/**
 * @constructor
 */
class AjaxRequest {
  private readonly _options: RequestOptions;
  private readonly _data: RequestData;
  private _previousXhr?: XMLHttpRequest;
  private _xhr?: XMLHttpRequest;

  constructor(options: RequestOptions) {
    this._options = Core.extend({
      data: {},
      contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
      responseType: 'application/json',
      type: 'POST',
      url: '',
      withCredentials: false,

      // behavior
      autoAbort: false,
      ignoreError: false,
      pinData: false,
      silent: false,
      includeRequestedWith: true,

      // callbacks
      failure: null,
      finalize: null,
      success: null,
      progress: null,
      uploadProgress: null,

      callbackObject: null,
    }, options);

    if (typeof options.callbackObject === 'object') {
      this._options.callbackObject = options.callbackObject;
    }

    this._options.url = Core.convertLegacyUrl(this._options.url!);
    if (this._options.url.indexOf('index.php') === 0) {
      this._options.url = window.WSC_API_URL + this._options.url;
    }

    if (this._options.url.indexOf(window.WSC_API_URL) === 0) {
      this._options.includeRequestedWith = true;
      // always include credentials when querying the very own server
      this._options.withCredentials = true;
    }

    if (this._options.pinData) {
      this._data = this._options.data!;
    }

    if (this._options.callbackObject) {
      if (typeof this._options.callbackObject._ajaxFailure === 'function') this._options.failure = this._options.callbackObject._ajaxFailure.bind(this._options.callbackObject);
      if (typeof this._options.callbackObject._ajaxFinalize === 'function') this._options.finalize = this._options.callbackObject._ajaxFinalize.bind(this._options.callbackObject);
      if (typeof this._options.callbackObject._ajaxSuccess === 'function') this._options.success = this._options.callbackObject._ajaxSuccess.bind(this._options.callbackObject);
      if (typeof this._options.callbackObject._ajaxProgress === 'function') this._options.progress = this._options.callbackObject._ajaxProgress.bind(this._options.callbackObject);
      if (typeof this._options.callbackObject._ajaxUploadProgress === 'function') this._options.uploadProgress = this._options.callbackObject._ajaxUploadProgress.bind(this._options.callbackObject);
    }

    if (!_didInit) {
      _didInit = true;

      window.addEventListener('beforeunload', () => _ignoreAllErrors = true);
    }
  }

  /**
   * Dispatches a request, optionally aborting a currently active request.
   */
  sendRequest(abortPrevious?: boolean): void {
    if (abortPrevious || this._options.autoAbort) {
      this.abortPrevious();
    }

    if (!this._options.silent) {
      AjaxStatus.show();
    }

    if (this._xhr instanceof XMLHttpRequest) {
      this._previousXhr = this._xhr;
    }

    this._xhr = new XMLHttpRequest();
    this._xhr.open(this._options.type!, this._options.url!, true);
    if (this._options.contentType) {
      this._xhr.setRequestHeader('Content-Type', this._options.contentType);
    }
    if (this._options.withCredentials || this._options.includeRequestedWith) {
      this._xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    }
    if (this._options.withCredentials) {
      this._xhr.withCredentials = true;
    }

    const self = this;
    const options = Core.clone(this._options) as RequestOptions;
    this._xhr.onload = function () {
      if (this.readyState === XMLHttpRequest.DONE) {
        if (this.status >= 200 && this.status < 300 || this.status === 304) {
          if (options.responseType && this.getResponseHeader('Content-Type')!.indexOf(options.responseType) !== 0) {
            // request succeeded but invalid response type
            self._failure(this, options);
          } else {
            self._success(this, options);
          }
        } else {
          self._failure(this, options);
        }
      }
    };
    this._xhr.onerror = function () {
      self._failure(this, options);
    };

    if (this._options.progress) {
      this._xhr.onprogress = this._options.progress;
    }
    if (this._options.uploadProgress) {
      this._xhr.upload.onprogress = this._options.uploadProgress;
    }

    if (this._options.type === 'POST') {
      let data: string | RequestData = this._options.data!;
      if (typeof data === 'object' && Core.getType(data) !== 'FormData') {
        data = Core.serialize(data);
      }

      this._xhr.send(data as any);
    } else {
      this._xhr.send();
    }
  }

  /**
   * Aborts a previous request.
   */
  abortPrevious(): void {
    if (!this._previousXhr) {
      return;
    }

    this._previousXhr.abort();
    this._previousXhr = undefined;

    if (!this._options.silent) {
      AjaxStatus.hide();
    }
  }

  /**
   * Sets a specific option.
   */
  setOption(key: string, value: any): void {
    this._options[key] = value;
  }

  /**
   * Returns an option by key or undefined.
   */
  getOption(key: string): unknown | null {
    if (this._options.hasOwnProperty(key)) {
      return this._options[key];
    }

    return null;
  }

  /**
   * Sets request data while honoring pinned data from setup callback.
   */
  setData(data: RequestData): void {
    if (this._data !== null && Core.getType(data) !== 'FormData') {
      data = Core.extend(this._data, data);
    }

    this._options.data = data;
  }

  /**
   * Handles a successful request.
   */
  _success(xhr: XMLHttpRequest, options: RequestOptions): void {
    if (!options.silent) {
      AjaxStatus.hide();
    }

    if (typeof options.success === 'function') {
      let data: ResponseData | null = null;
      if (xhr.getResponseHeader('Content-Type')!.split(';', 1)[0].trim() === 'application/json') {
        try {
          data = JSON.parse(xhr.responseText);
        } catch (e) {
          // invalid JSON
          this._failure(xhr, options);

          return;
        }

        // trim HTML before processing, see http://jquery.com/upgrade-guide/1.9/#jquery-htmlstring-versus-jquery-selectorstring
        if (data && data.returnValues && data.returnValues.template !== undefined) {
          data.returnValues.template = data.returnValues.template.trim();
        }

        // force-invoke the background queue
        if (data && data.forceBackgroundQueuePerform) {
          // TODO
          throw new Error('TODO: Invoking the BackgroundQueue is not yet supported.');
          /*
          require(['WoltLabSuite/Core/BackgroundQueue'], function (BackgroundQueue) {
            BackgroundQueue.invoke();
          });
           */
        }
      }

      options.success(data!, xhr.responseText, xhr, options.data!);
    }

    this._finalize(options);
  }

  /**
   * Handles failed requests, this can be both a successful request with
   * a non-success status code or an entirely failed request.
   */
  _failure(xhr: XMLHttpRequest, options: RequestOptions): void {
    if (_ignoreAllErrors) {
      return;
    }

    if (!options.silent) {
      AjaxStatus.hide();
    }

    let data: ResponseData | null = null;
    try {
      data = JSON.parse(xhr.responseText);
    } catch (e) {
    }

    let showError = true;
    if (typeof options.failure === 'function') {
      showError = options.failure((data || {}), (xhr.responseText || ''), xhr, options.data!);
    }

    if (options.ignoreError !== true && showError) {
      const html = this.getErrorHtml(data, xhr);

      if (html) {
        // TODO
        throw new Error('TODO: Yielding dialogs is not yet supported.');
        /*
        if (UiDialog === undefined) UiDialog = require('Ui/Dialog');
        UiDialog.openStatic(DomUtil.getUniqueId(), html, {
          title: Language.get('wcf.global.error.title'),
        });
         */
      }
    }

    this._finalize(options);
  }

  /**
   * Returns the inner HTML for an error/exception display.
   */
  getErrorHtml(data: ResponseData | null, xhr: XMLHttpRequest): string | null {
    let details = '';
    let message: string;

    if (data !== null) {
      if (data.returnValues && data.returnValues.description) {
        details += '<br><p>Description:</p><p>' + data.returnValues.description + '</p>';
      }

      if (data.file && data.line) {
        details += '<br><p>File:</p><p>' + data.file + ' in line ' + data.line + '</p>';
      }

      if (data.stacktrace) details += '<br><p>Stacktrace:</p><p>' + data.stacktrace + '</p>';
      else if (data.exceptionID) details += '<br><p>Exception ID: <code>' + data.exceptionID + '</code></p>';

      message = data.message;

      data.previous.forEach(function (previous) {
        details += '<hr><p>' + previous.message + '</p>';
        details += '<br><p>Stacktrace</p><p>' + previous.stacktrace + '</p>';
      });
    } else {
      message = xhr.responseText;
    }

    if (!message || message === 'undefined') {
      if (!window.ENABLE_DEBUG_MODE) return null;

      message = 'XMLHttpRequest failed without a responseText. Check your browser console.';
    }

    return '<div class="ajaxDebugMessage"><p>' + message + '</p>' + details + '</div>';
  }

  /**
   * Finalizes a request.
   *
   * @param  {Object}  options    request options
   */
  _finalize(options: RequestOptions): void {
    if (typeof options.finalize === 'function') {
      options.finalize(this._xhr!);
    }

    this._previousXhr = undefined;

    DomChangeListener.trigger();

    // fix anchor tags generated through WCF::getAnchor()
    document.querySelectorAll<HTMLAnchorElement>('a[href*="#"]').forEach(link => {
      let href = link.href;
      if (href.indexOf('AJAXProxy') !== -1 || href.indexOf('ajax-proxy') !== -1) {
        href = href.substr(href.indexOf('#'));
        link.href = document.location.toString().replace(/#.*/, '') + href;
      }
    });
  }
}

export = AjaxRequest;
