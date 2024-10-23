/**
 * Developer tools for WoltLab Suite.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

let _settings = {
  editorAutosave: true,
  editorInspector: false,
  eventLogging: false,
};

function _updateConfig() {
  if (window.sessionStorage) {
    window.sessionStorage.setItem("__wsc_devtools_config", JSON.stringify(_settings));
  }
}

const Devtools = {
  /**
   * Prints the list of available commands.
   */
  help(): void {
    window.console.log("");
    window.console.log("%cAvailable commands:", "text-decoration: underline");

    Object.keys(Devtools)
      .filter((cmd) => cmd !== "_internal_")
      .sort()
      .forEach((cmd) => {
        window.console.log(`\tDevtools.${cmd}()`);
      });

    window.console.log("");
  },

  /**
   * Disables/re-enables the editor autosave feature.
   */
  toggleEditorAutosave(forceDisable: boolean): void {
    _settings.editorAutosave = forceDisable ? false : !_settings.editorAutosave;
    _updateConfig();

    window.console.log(
      "%c\tEditor autosave " + (_settings.editorAutosave ? "enabled" : "disabled"),
      "font-style: italic",
    );
  },

  /**
   * Enables or disables the inspector for the editor.
   */
  toggleEditorInspector(forceEnable: boolean): void {
    _settings.editorInspector = forceEnable ? true : !_settings.editorInspector;
    _updateConfig();

    window.console.log(
      "%c\tEditor inspector " + (_settings.editorInspector ? "enabled" : "disabled"),
      "font-style: italic",
    );
  },

  /**
   * Enables/disables logging for fired event listener events.
   */
  toggleEventLogging(forceEnable: boolean): void {
    _settings.eventLogging = forceEnable ? true : !_settings.eventLogging;
    _updateConfig();

    window.console.log("%c\tEvent logging " + (_settings.eventLogging ? "enabled" : "disabled"), "font-style: italic");
  },

  /**
   * Internal methods not meant to be called directly.
   */
  _internal_: {
    enable(): void {
      window.Devtools = Devtools;

      window.console.log("%cDevtools for WoltLab Suite loaded", "font-weight: bold");

      if (window.sessionStorage) {
        const settings = window.sessionStorage.getItem("__wsc_devtools_config");
        try {
          if (settings !== null) {
            _settings = JSON.parse(settings);
          }
        } catch {
          // Ignore JSON parsing failure.
        }

        if (!_settings.editorAutosave) {
          Devtools.toggleEditorAutosave(true);
        }
        if (_settings.editorInspector) {
          Devtools.toggleEditorInspector(true);
        }
        if (_settings.eventLogging) {
          Devtools.toggleEventLogging(true);
        }
      }

      window.console.log("Settings are saved per browser session, enter `Devtools.help()` to learn more.");
      window.console.log("");
    },

    editorAutosave(): boolean {
      return _settings.editorAutosave;
    },

    editorInspector(): boolean {
      return _settings.editorInspector;
    },

    eventLog(identifier: string, action: string): void {
      if (_settings.eventLogging) {
        window.console.log("[Devtools.EventLogging] Firing event: " + action + " @ " + identifier);
      }
    },
  },
};

export = Devtools;
