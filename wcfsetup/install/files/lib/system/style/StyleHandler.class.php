<?php

namespace wcf\system\style;

use wcf\data\style\ActiveStyle;
use wcf\data\style\Style;
use wcf\data\style\StyleEditor;
use wcf\system\cache\builder\StyleCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\request\RequestHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\JSON;

/**
 * Handles styles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class StyleHandler extends SingletonFactory
{
    /**
     * style information cache
     * @var array
     */
    protected $cache = [];

    /**
     * active style object
     * @var ActiveStyle
     */
    protected $style;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        // load cache
        $this->cache = StyleCacheBuilder::getInstance()->getData();
    }

    /**
     * Returns a list of all for the current user available styles.
     *
     * @return  Style[]
     */
    public function getAvailableStyles()
    {
        $styles = [];

        foreach ($this->cache['styles'] as $styleID => $style) {
            if (!$style->isDisabled || WCF::getSession()->getPermission('admin.style.canUseDisabledStyle')) {
                $styles[$styleID] = $style;
            }
        }

        return $styles;
    }

    /**
     * Returns a list of all styles.
     *
     * @return  Style[]
     */
    public function getStyles()
    {
        return $this->cache['styles'];
    }

    /**
     * Returns the active style.
     *
     * @return  ActiveStyle
     */
    public function getStyle()
    {
        if ($this->style === null) {
            $this->changeStyle();
        }

        return $this->style;
    }

    /**
     * Changes the active style.
     *
     * @param int $styleID
     * @param bool $ignorePermissions
     * @throws  SystemException
     */
    public function changeStyle($styleID = 0, $ignorePermissions = false)
    {
        // check permission
        if (!$ignorePermissions) {
            if (isset($this->cache['styles'][$styleID])) {
                if (
                    $this->cache['styles'][$styleID]->isDisabled
                    && !WCF::getSession()->getPermission('admin.style.canUseDisabledStyle')
                ) {
                    $styleID = 0;
                }
            }
        }

        // fallback to default style
        if (!isset($this->cache['styles'][$styleID])) {
            // get default style
            $styleID = $this->cache['default'];

            if (!isset($this->cache['styles'][$styleID])) {
                throw new SystemException('no default style defined');
            }
        }

        // init style
        $this->style = new ActiveStyle($this->cache['styles'][$styleID]);

        // set template group id
        if (WCF::getTPL()) {
            WCF::getTPL()->setTemplateGroupID($this->style->templateGroupID);
        }
    }

    /**
     * Returns the HTML tag to include current stylesheet.
     *
     * @param bool $isACP indicates if the request is an acp request
     * @return  string
     */
    public function getStylesheet($isACP = false)
    {
        $preload = '';

        if ($isACP) {
            // ACP
            $filename = 'acp/style/style' . (WCF::getLanguage()->get('wcf.global.pageDirection') == 'rtl' ? '-rtl' : '') . '.css';
            if (!\file_exists(WCF_DIR . $filename)) {
                StyleCompiler::getInstance()->compileACP();
            }
        } else {
            // frontend
            $filename = 'style/style-' . $this->getStyle()->styleID . (WCF::getLanguage()->get('wcf.global.pageDirection') == 'rtl' ? '-rtl' : '') . '.css';
            if (!\file_exists(WCF_DIR . $filename)) {
                StyleCompiler::getInstance()->compile($this->getStyle()->getDecoratedObject());
            }

            if (\is_readable(WCF_DIR . 'style/style-' . $this->getStyle()->styleID . '-preload.json')) {
                $decoded = JSON::decode(\file_get_contents(WCF_DIR . 'style/style-' . $this->getStyle()->styleID . '-preload.json'));
                if (isset($decoded['html']) && \is_array($decoded['html'])) {
                    $preload = \implode('', $decoded['html']);
                }
            }
        }

        return '<link rel="stylesheet" type="text/css" href="' . WCF::getPath() . $filename . '?m=' . \filemtime(WCF_DIR . $filename) . '">' . $preload;
    }

    /**
     * Resets stylesheet for given style.
     *
     * @param Style $style
     */
    public function resetStylesheet(Style $style)
    {
        $stylesheets = \glob(WCF_DIR . 'style/style-' . $style->styleID . '*.css');
        if ($stylesheets !== false) {
            foreach ($stylesheets as $stylesheet) {
                @\unlink($stylesheet);
            }
        }

        @\unlink(WCF_DIR . 'style/style-' . $style->styleID . '-preload.json');
    }

    /**
     * Returns number of available styles.
     *
     * @return  int
     */
    public function countStyles()
    {
        return \count($this->getAvailableStyles());
    }

    /**
     * Resets all stylesheets.
     *
     * @param bool $resetACP
     */
    public static function resetStylesheets($resetACP = true)
    {
        // frontend stylesheets
        $files = \glob(WCF_DIR . 'style/style-*.css');
        if ($files !== false) {
            foreach ($files as $file) {
                @\unlink($file);
            }
        }

        // preload data
        $files = \glob(WCF_DIR . 'style/style-*-preload.json');
        if ($files !== false) {
            foreach ($files as $file) {
                @\unlink($file);
            }
        }

        // ACP stylesheets
        if ($resetACP) {
            $files = \glob(WCF_DIR . 'acp/style/style*.css');
            if ($files !== false) {
                foreach ($files as $file) {
                    @\unlink($file);
                }
            }
        }
    }

    /**
     * Returns a style by package name, optionally filtering tainted styles.
     *
     * @param string $packageName style package name
     * @param bool $skipTainted ignore tainted styles
     * @return  StyleEditor|null
     * @since   3.0
     */
    public function getStyleByName($packageName, $skipTainted = false)
    {
        foreach ($this->cache['styles'] as $style) {
            if ($style->packageName === $packageName) {
                if (!$skipTainted || !$style->isTainted) {
                    return new StyleEditor($style);
                }
            }
        }

        return null;
    }

    /**
     * Returns true if there is more than one available style and the changer is to be displayed.
     *
     * @return      bool         true if style changer should be displayed
     */
    public function showStyleChanger(): bool
    {
        return \SHOW_STYLE_CHANGER && $this->countStyles() > 1;
    }

    /**
     * @since 6.0
     */
    public function showColorSchemeSelector(): bool
    {
        return !!$this->getStyle()->hasDarkMode;
    }

    /**
     * @deprecated 6.0 Unsupported, this exists temporarily for development purposes.
     */
    public function getIcons($toJSON = false)
    {
        if ($toJSON) {
            return JSON::encode([]);
        }

        return [];
    }

    /**
     * Retrieves the default style for requests originating from the ACP. May return `null`
     * if there is no default style.
     *
     * @return Style|null
     * @since   5.2
     */
    public function getDefaultStyle()
    {
        if (!RequestHandler::getInstance()->isACPRequest()) {
            throw new \LogicException('Illegal request, please use `getStyle()` for frontend requests.');
        }

        $styleID = $this->cache['default'];
        if ($styleID) {
            return $this->cache['styles'][$styleID];
        }

        return null;
    }

    /**
     * Returns the preferred color scheme of the user. For guests this value
     * will always be 'system' to indicate that the device preferences should
     * be used to negotiate the color scheme.
     *
     * @since 6.0
     */
    public function getColorScheme(): string
    {
        if (!RequestHandler::getInstance()->isACPRequest() && !$this->style->hasDarkMode) {
            return 'light';
        }

        if (!WCF::getUser()->userID) {
            return 'system';
        }

        return WCF::getUser()->getUserOption('colorScheme') ?? 'system';
    }
}
