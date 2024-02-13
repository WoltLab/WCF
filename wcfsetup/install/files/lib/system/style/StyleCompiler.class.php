<?php

namespace wcf\system\style;

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;
use ScssPhp\ScssPhp\ValueConverter;
use wcf\data\application\Application;
use wcf\data\option\Option;
use wcf\data\style\Style;
use wcf\system\application\ApplicationHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;
use wcf\util\StyleUtil;
use wcf\util\Url;

/**
 * Provides access to the SCSS PHP compiler.
 *
 * @author Tim Duesterhus, Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class StyleCompiler extends SingletonFactory
{
    /**
     * Contains all files, which are compiled for a style.
     * @var string[]
     */
    private $files;

    /**
     * names of option types which are supported as additional variables
     * @var string[]
     */
    public static $supportedOptionType = ['boolean', 'float', 'integer', 'radioButton', 'select'];

    /**
     * file used to store global SCSS declarations, relative to `WCF_DIR`
     * @var string
     */
    const FILE_GLOBAL_VALUES = 'style/ui/zzz_wsc_style_global_values.scss';

    /**
     * registry keys for data storage
     * @var string
     */
    const REGISTRY_GLOBAL_VALUES = 'styleGlobalValues';

    public const SYSTEM_FONT_NAME = 'system';

    public const SYSTEM_FONT_FAMILY = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
        "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans",
        "Helvetica Neue", Arial, sans-serif';

    public const SYSTEM_FONT_FAMILY_MONOSPACE = 'ui-monospace, Menlo, Monaco, "Cascadia Mono",
        "Segoe UI Mono", "Roboto Mono", "Oxygen Mono", "Ubuntu Monospace", "Source Code Pro",
        "Fira Mono", "Droid Sans Mono", "Courier New", monospace';

    /**
     * Returns a fresh instance of the scssphp compiler.
     */
    private function makeCompiler(): Compiler
    {
        $compiler = new Compiler();
        $compiler->setImportPaths([WCF_DIR]);

        if (\ENABLE_DEBUG_MODE && \ENABLE_DEVELOPER_TOOLS) {
            $compiler->setOutputStyle(OutputStyle::EXPANDED);
        } else {
            $compiler->setOutputStyle(OutputStyle::COMPRESSED);
        }

        return $compiler;
    }

    /**
     * Returns the default style variables as array.
     *
     * @return string[]
     * @since 5.3
     */
    public static function getDefaultVariables(): array
    {
        $sql = "SELECT      variable.variableName, variable.defaultValue
                FROM        wcf" . WCF_N . "_style_variable variable
                ORDER BY    variable.variableID ASC";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        $variables = $statement->fetchMap('variableName', 'defaultValue');

        // see https://github.com/WoltLab/WCF/issues/2636
        if (empty($variables['wcfPageThemeColor'])) {
            $variables['wcfPageThemeColor'] = $variables['wcfHeaderBackground'];
        }

        return $variables;
    }

    /**
     * Test a style with the given imagePath and variables. If the style is valid and does not throw an
     * error, null is returned. Otherwise the exception is returned (!).
     *
     * @param mixed[] $variables
     * @since 5.3
     */
    public function testStyle(
        string $testFileDir,
        string $styleName,
        string $imagePath,
        array $variables,
        ?string $customCustomSCSSFile = null,
    ): ?\Exception {
        $individualScss = '';
        if (isset($variables['individualScss'])) {
            $individualScss = $variables['individualScss'];
            unset($variables['individualScss']);
            unset($variables[Style::DARK_MODE_PREFIX . 'individualScss']);
        }
        if (isset($variables['individualScssDarkMode'])) {
            $individualScssDarkMode = $variables['individualScssDarkMode'];
            unset($variables['individualScssDarkMode']);
            unset($variables[Style::DARK_MODE_PREFIX . 'individualScssDarkMode']);

            if ($individualScssDarkMode) {
                $individualScss .= \sprintf(
                    "\nhtml[data-color-scheme=\"dark\"] {\n%s\n}",
                    $individualScssDarkMode,
                );
            }
        }

        // add style image path
        if ($imagePath) {
            $imagePath = FileUtil::getRelativePath(WCF_DIR . 'style/', WCF_DIR . $imagePath);
            $imagePath = FileUtil::addTrailingSlash(FileUtil::unifyDirSeparator($imagePath));
        } else {
            $imagePath = '../images/';
        }
        $variables['style_image_path'] = "'{$imagePath}'";

        // apply overrides
        if (isset($variables['overrideScss'])) {
            $lines = \explode("\n", StringUtil::unifyNewlines($variables['overrideScss']));
            foreach ($lines as $line) {
                if (\preg_match('~^@([a-zA-Z]+): ?([@a-zA-Z0-9 ,\.\(\)\%\#-]+);$~', $line, $matches)) {
                    $variables[$matches[1]] = $matches[2];
                }
            }
            unset($variables['overrideScss']);
        }

        // api version
        $variables['apiVersion'] = Style::API_VERSION;

        $variables = $this->prepareVariables($variables);

        $parameters = ['scss' => ''];
        EventHandler::getInstance()->fireAction($this, 'compile', $parameters);

        $files = $this->getFiles();

        if ($customCustomSCSSFile !== null) {
            if (($customSCSSFileKey = \array_search(WCF_DIR . self::FILE_GLOBAL_VALUES, $files)) !== false) {
                unset($files[$customSCSSFileKey]);
            }

            $files[] = $customCustomSCSSFile;
        }

        $scss = "/*!\n\nstylesheet for '" . $styleName . "', generated on " . \gmdate('r') . " -- DO NOT EDIT\n\n*/\n";
        $scss .= $this->bootstrap($variables);
        foreach ($files as $file) {
            $scss .= $this->prepareFile($file);
        }
        $scss .= $individualScss;
        if (!empty($parameters['scss'])) {
            $scss .= "\n" . $parameters['scss'];
        }

        try {
            $css = $this->compileStylesheet(
                $scss,
                $variables
            );

            $preloadManifest = $this->buildPreloadManifest(
                $this->extractPreloadRequests($css)
            );

            $this->writeCss(
                FileUtil::addTrailingSlash($testFileDir) . 'style',
                $css,
                $preloadManifest
            );
        } catch (\Exception $e) {
            return $e;
        }

        return null;
    }

    /**
     * Returns a array with all files, which should be compiled for a style.
     *
     * @return string[]
     * @since 5.3
     */
    private function getFiles(): array
    {
        if (!$this->files) {
            $files = $this->getCoreFiles();

            // read stylesheets in dependency order
            $sql = "SELECT      filename, application
                    FROM        wcf" . WCF_N . "_package_installation_file_log
                    WHERE       CONVERT(filename using utf8) REGEXP ?
                            AND packageID <> ?
                    ORDER BY    packageID";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([
                '^style/([a-zA-Z0-9\-\.]+)\.scss',
                1,
            ]);
            while ($row = $statement->fetchArray()) {
                // the global values will always be evaluated last
                if ($row['filename'] === self::FILE_GLOBAL_VALUES) {
                    continue;
                }

                $files[] = Application::getDirectory($row['application']) . $row['filename'];
            }

            // global SCSS
            if (\file_exists(WCF_DIR . self::FILE_GLOBAL_VALUES)) {
                $files[] = WCF_DIR . self::FILE_GLOBAL_VALUES;
            }

            $this->files = $files;
        }

        return $this->files;
    }

    /**
     * Compiles SCSS stylesheets.
     */
    public function compile(Style $style): void
    {
        // get style variables
        $variables = $style->getVariables();

        $individualScss = '';
        if (isset($variables['individualScss'])) {
            $individualScss = $variables['individualScss'];
            unset($variables['individualScss']);
        }
        if (isset($variables['individualScssDarkMode'])) {
            $individualScssDarkMode = $variables['individualScssDarkMode'];
            unset($variables['individualScssDarkMode']);

            if ($individualScssDarkMode) {
                $individualScss .= \sprintf(
                    "\nhtml[data-color-scheme=\"dark\"] {\n%s\n}",
                    $individualScssDarkMode,
                );
            }
        }

        unset($variables[Style::DARK_MODE_PREFIX . 'individualScssDarkMode']);

        // add style image path
        $imagePath = '../images/';
        if ($style->imagePath) {
            $imagePath = FileUtil::getRelativePath(WCF_DIR . 'style/', WCF_DIR . $style->imagePath);
            $imagePath = FileUtil::addTrailingSlash(FileUtil::unifyDirSeparator($imagePath));
        }
        $variables['style_image_path'] = "'{$imagePath}'";

        // apply overrides
        if (isset($variables['overrideScss'])) {
            $lines = \explode("\n", StringUtil::unifyNewlines($variables['overrideScss']));
            foreach ($lines as $line) {
                if (\preg_match('~^@([a-zA-Z]+): ?([@a-zA-Z0-9 ,\.\(\)\%\#-]+);$~', $line, $matches)) {
                    $variables[$matches[1]] = $matches[2];
                }
            }
            unset($variables['overrideScss']);
        }

        // api version
        $variables['apiVersion'] = Style::API_VERSION;

        $variables = $this->prepareVariables($variables);

        $parameters = ['scss' => ''];
        EventHandler::getInstance()->fireAction($this, 'compile', $parameters);

        $scss = "/*!\n\nstylesheet for '" . $style->styleName . "', generated on " . \gmdate('r') . " -- DO NOT EDIT\n\n*/\n";
        $scss .= $this->bootstrap($variables);
        foreach ($this->getFiles() as $file) {
            $scss .= $this->prepareFile($file);
        }
        $scss .= $individualScss;
        if (!empty($parameters['scss'])) {
            $scss .= "\n" . $parameters['scss'];
        }

        $css = $this->compileStylesheet(
            $scss,
            $variables
        );

        $preloadManifest = $this->buildPreloadManifest(
            $this->extractPreloadRequests($css)
        );

        $this->writeCss($this->getFilenameForStyle($style), $css, $preloadManifest);
    }

    /**
     * Builds the preload manifest from the given iterable containing
     * preload requests.
     *
     * @see StyleCompiler::extractPreloadRequests()
     * @since 5.4
     */
    private function buildPreloadManifest(iterable $requests): array
    {
        $preloadManifest = ['http' => [], 'html' => []];

        foreach ($requests as $request) {
            if (Url::is($request['filename'])) {
                $filename = $request['filename'];
            } else {
                $filename = WCF::getPath() . FileUtil::getRealPath('style/' . $request['filename']);
            }

            $http = "<{$filename}>; rel=preload; as={$request['as']}";
            $html = \sprintf(
                '<link rel="preload" href="%s" as="%s"',
                StringUtil::encodeHTML($filename),
                StringUtil::encodeHTML($request['as'])
            );
            if ($request['crossorigin']) {
                $http .= "; crossorigin";
                $html .= " crossorigin";
            }
            if ($request['type']) {
                $http .= \sprintf('; type="%s"', \addslashes($request['type']));
                $html .= \sprintf(' type="%s"', StringUtil::encodeHTML($request['type']));
            }
            $html .= '>';
            $preloadManifest['http'][] = $http;
            $preloadManifest['html'][] = $html;
        }

        return $preloadManifest;
    }

    /**
     * Extracts preload requests from the given CSS string.
     *
     * @since 5.4
     */
    private function extractPreloadRequests(string $css): iterable
    {
        $regex = '/--woltlab-suite-preload:\\s*preload_dummy\\(((?:"(?:\\\\.|[^\\\\"])*"|[^")])+)\\)\\s*[;\\}]/';
        if (!\preg_match_all($regex, $css, $requests)) {
            return [];
        }

        foreach ($requests[1] as $request) {
            $regex = '/\s*("(?:\\\\.|[^\\\\"])*"|[^",]+)\s*(?:,|$)\s*/';
            if (!\preg_match_all($regex, $request, $parameters)) {
                continue;
            }
            $parameters = $parameters[1];
            if (\count($parameters) < 4) {
                continue;
            }
            $parameters = \array_map(static function (string $parameter) {
                if ($parameter[0] === '"') {
                    return \stripslashes(\substr($parameter, 1, -1));
                }

                return $parameter;
            }, $parameters);
            [$filename, $as, $crossorigin, $type] = $parameters;

            yield [
                'filename' => $filename,
                'as' => $as,
                'crossorigin' => !!$crossorigin,
                'type' => $type ?: null,
            ];
        }
    }

    /**
     * Compiles SCSS stylesheets for ACP usage.
     */
    public function compileACP(): void
    {
        $files = $this->getCoreFiles();

        // ACP uses a slightly different layout
        $files[] = WCF_DIR . 'acp/style/layout.scss';

        // include stylesheets from other apps in arbitrary order
        if (PACKAGE_ID) {
            foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
                $files = \array_merge($files, $this->getAcpStylesheets($application));
            }
        }

        // read default values
        $sql = "SELECT      variableName, defaultValue, defaultValueDarkMode
                FROM        wcf" . WCF_N . "_style_variable
                ORDER BY    variableID ASC";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        $variables = [];
        while ($row = $statement->fetchArray()) {
            $variables[$row['variableName']] = $row['defaultValue'];
            $variables[Style::DARK_MODE_PREFIX . $row['variableName']] = $row['defaultValueDarkMode'] ?? '';
        }

        $variables['style_image_path'] = "'../images/'";

        $variables = $this->prepareVariables($variables);

        // The theme color implicitly matches the header background color.
        $variables['wcfPageThemeColor'] = 'var(--wcfHeaderBackground)';

        $scss = "/*!\n\nstylesheet for the admin panel, generated on " . \gmdate('r') . " -- DO NOT EDIT\n\n*/\n";
        $scss .= $this->bootstrap($variables);
        foreach ($files as $file) {
            $scss .= $this->prepareFile($file);
        }

        $css = $this->compileStylesheet(
            $scss,
            $variables
        );

        // fix relative paths
        $css = \str_replace('../font/', '../../font/', $css);
        $css = \str_replace('../icon/', '../../icon/', $css);
        $css = \preg_replace('~\.\./images/~', '../../images/', $css);

        $this->writeCss(WCF_DIR . 'acp/style/style', $css);
    }

    /**
     * Returns a list of common stylesheets provided by the core.
     *
     * @return string[] list of common stylesheets
     */
    private function getCoreFiles(): array
    {
        $files = [];
        if ($handle = \opendir(WCF_DIR . 'style/')) {
            while (($file = \readdir($handle)) !== false) {
                if ($file === '.' || $file === '..' || $file === 'bootstrap' || \is_file(WCF_DIR . 'style/' . $file)) {
                    continue;
                }

                $file = WCF_DIR . "style/{$file}/";
                if ($innerHandle = \opendir($file)) {
                    while (($innerFile = \readdir($innerHandle)) !== false) {
                        if (
                            $innerFile === '.'
                            || $innerFile === '..'
                            || !\is_file($file . $innerFile)
                            || !\preg_match('~^[a-zA-Z0-9\-\.]+\.scss$~', $innerFile)
                        ) {
                            continue;
                        }

                        $files[] = $file . $innerFile;
                    }
                    \closedir($innerHandle);
                }
            }

            \closedir($handle);

            // Directory order is not deterministic in some cases,
            // also the `darkMode.scss` must be at the end.
            \usort($files, static function (string $a, string $b) {
                if (\str_ends_with($a, 'ui/darkMode.scss')) {
                    return 1;
                }

                if (\str_ends_with($b, 'ui/darkMode.scss')) {
                    return -1;
                }

                return $a <=> $b;
            });
        }

        return $files;
    }

    /**
     * Returns the list of SCSS stylesheets of an application.
     *
     * @return      string[]
     */
    private function getAcpStylesheets(Application $application): array
    {
        if ($application->packageID == 1) {
            return [];
        }

        $files = [];

        $basePath = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR . $application->getPackage()->packageDir)) . 'acp/style/';
        $result = \glob($basePath . '*.scss');
        if (\is_array($result)) {
            foreach ($result as $file) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * Reads in the SCSS files that form the foundation of the stylesheet. This includes
     * the CSS reset and mixins.
     *
     * @param mixed[] $variables
     */
    private function bootstrap(array $variables): string
    {
        $darkModeVariables = [];

        $prefixLength = \strlen(Style::DARK_MODE_PREFIX);
        $variables = \array_filter(
            $variables,
            static function (string $value, string $key) use (&$darkModeVariables, $prefixLength) {
                if (\str_starts_with($key, Style::DARK_MODE_PREFIX)) {
                    if (\str_starts_with($value, 'rgba(')) {
                        $darkModeVariables[\substr($key, $prefixLength)] = $value;
                    }

                    return false;
                }

                return true;
            },
            \ARRAY_FILTER_USE_BOTH
        );

        $content = \sprintf(
            "html {\n%s\n}",
            $this->exportStyleVariables($variables)
        );

        if ($darkModeVariables !== []) {
            $content .= \sprintf(
                "\nhtml[data-color-scheme=\"dark\"] {\n%s}\n",
                $this->exportStyleVariables($darkModeVariables),
            );
        }

        // add reset like a boss
        $content .= $this->prepareFile(WCF_DIR . 'style/bootstrap/reset.scss');

        // add mixins
        $content .= $this->prepareFile(WCF_DIR . 'style/bootstrap/mixin.scss');

        // add newer mixins added with version 3.0
        foreach (\glob(WCF_DIR . 'style/bootstrap/mixin/*.scss') as $mixin) {
            $content .= $this->prepareFile($mixin);
        }

        $content .= <<<'EOT'
            @function preload($filename, $as, $crossorigin: false, $type: "") {
                @if $crossorigin {
                    @return preload_dummy($filename, $as, 1, $type);
                } @else {
                    @return preload_dummy($filename, $as, 0, $type);
                }
            }
        EOT;

        $content .= <<<'EOT'
            @function getFont($filename, $family: "/", $version: "") {
                @if ($family != "") {
                    $family: "families/" + $family + "/";
                }
                @if ($version != "") {
                    $version: "?v=" + $version;
                }

                @return "../font/" + $family + $filename + $version;
            }
        EOT;

        if (!empty($variables['wcfFontFamilyGoogle'])) {
            $content .= $this->getGoogleFontScss($variables['wcfFontFamilyGoogle']);
        }

        return $content;
    }

    /**
     * Performs transformations on intermediate values and injects global values.
     *
     * @param mixed[] $variables
     * @return mixed[]
     * @since 6.0
     */
    private function prepareVariables(array $variables): array
    {
        foreach ($variables as &$value) {
            if (\str_starts_with($value, '../')) {
                $value = '"' . $value . '"';
            }
        }
        unset($value);

        $variables['wcfFontFamily'] = $variables['wcfFontFamilyFallback'];

        // Define the font family set for the OS default fonts. This needs to be happen statically to
        // allow modifications in the future in case of changes.
        $variables['wcfFontFamilyMonospace'] = self::SYSTEM_FONT_FAMILY_MONOSPACE;

        if ($variables['wcfFontFamily'] === self::SYSTEM_FONT_NAME) {
            $variables['wcfFontFamily'] = self::SYSTEM_FONT_FAMILY;
        }

        if (!empty($variables['wcfFontFamilyGoogle'])) {
            $variables['wcfFontFamily'] = \sprintf(
                '"%s", %s',
                $variables['wcfFontFamilyGoogle'],
                $variables['wcfFontFamily']
            );
        }

        // add options as SCSS variables
        if (PACKAGE_ID) {
            foreach (Option::getOptions() as $constantName => $option) {
                if (\in_array($option->optionType, static::$supportedOptionType)) {
                    $variables['wcf_option_' . \mb_strtolower($constantName)] = \is_int($option->optionValue) ? $option->optionValue : '"' . $option->optionValue . '"';
                }
            }
        } else {
            // workaround during setup
            $variables['wcf_option_attachment_thumbnail_height'] = 210;
            $variables['wcf_option_attachment_thumbnail_width'] = 280;
            $variables['wcf_option_signature_max_image_height'] = 150;
        }

        $variables['apiVersion'] = \str_replace('.', '', Style::API_VERSION);

        // Add some global defaults.
        $variables['wcfBorderRadius'] = '4px';
        $variables['wcfBorderRadiusContainer'] = '8px';
        $variables['wcfBoxShadow'] = 'rgb(0 0 0 / 20%) 0 12px 28px 0, rgb(0 0 0 / 10%) 0 2px 4px 0';
        $variables['wcfBoxShadowCard'] = 'rgb(0 0 0 / 10%) 0 12px 28px 0, rgb(0 0 0 / 5%) 0 2px 4px 0';

        return $variables;
    }

    /**
     * Prepares a SCSS stylesheet for importing.
     *
     * @throws  SystemException
     */
    private function prepareFile(string $filename): string
    {
        if (!\file_exists($filename) || !\is_readable($filename)) {
            throw new SystemException("Unable to access '" . $filename . "', does not exist or is not readable");
        }

        // use a relative path
        $filename = FileUtil::getRelativePath(WCF_DIR, \dirname($filename)) . \basename($filename);

        return '@import "' . $filename . '";' . "\n";
    }

    /**
     * Compiles the given SCSS into one CSS stylesheet and returns it.
     *
     * @param mixed[] $variables
     */
    private function compileStylesheet(string $scss, array $variables): string
    {
        $compiler = $this->makeCompiler();
        $compiler->replaceVariables(\array_map(static function ($value) {
            if ($value === "" || \is_int($value)) {
                return ValueConverter::fromPhp($value);
            }

            return ValueConverter::parseValue($value);
        }, $variables));

        try {
            $result = $compiler->compileString($scss);

            return $result->getCss();
        } catch (\Exception $e) {
            throw new SystemException("Could not compile SCSS: " . $e->getMessage(), 0, '', $e);
        }
    }

    /**
     * Converts the given CSS into the RTL variant.
     *
     * This method differs from StyleUtil::convertCSSToRTL() in that it includes some fixes
     * for elements that need to remain LTR.
     *
     * @see StyleUtil::convertCSSToRTL()
     */
    private function convertToRtl(string $css): string
    {
        $css = StyleUtil::convertCSSToRTL($css);

        // force code boxes to be always LTR
        $css .= "\n/* RTL fix for code boxes */\n";
        $css .= ".codeBoxCode { direction: ltr; } \n";
        $css .= ".codeBox .codeBoxCode { padding-left: 7ch; padding-right: 0; } \n";
        $css .= ".codeBox .codeBoxCode > code .codeBoxLine > a { margin-left: -7ch; margin-right: 0; text-align: right; } \n";

        return $css;
    }

    /**
     * Writes the given css into the file with the given prefix.
     */
    private function writeCss(string $filePrefix, string $css, ?array $preloadManifest = null): void
    {
        \file_put_contents($filePrefix . '.css', $css);
        FileUtil::makeWritable($filePrefix . '.css');

        \file_put_contents($filePrefix . '-rtl.css', $this->convertToRtl($css));
        FileUtil::makeWritable($filePrefix . '-rtl.css');

        if ($preloadManifest) {
            \file_put_contents($filePrefix . '-preload.json', JSON::encode($preloadManifest));
            FileUtil::makeWritable($filePrefix . '-preload.json');
        }
    }

    /**
     * Returns the SCSS required to load a Google font.
     */
    private function getGoogleFontScss(string $font): string
    {
        if (!PACKAGE_ID) {
            return '';
        }

        $cssFile = FontManager::getInstance()->getCssFilename($font);
        if (!\is_readable($cssFile)) {
            return '';
        }

        return \file_get_contents($cssFile);
    }

    /**
     * Exports the style variables as CSS variables on the `html` element.
     *
     * @param mixed[] $variables
     * @since 6.0
     */
    private function exportStyleVariables(array $variables): string
    {
        $skipVariables = [
            'wcfFontFamilyFallback',
            'wcfFontFamilyGoogle',
        ];

        $css = '';
        foreach ($variables as $key => $value) {
            if (!\preg_match('~^wcf[A-Z]~', $key)) {
                continue;
            }

            if (\in_array($key, $skipVariables)) {
                continue;
            }

            $css .= "\t--{$key}: {$value};\n";

            // Export colors as 'r g b' values, omitting the alpha channel.
            // These values can be used for gradients from a color value to
            // its transparent value.
            if (\preg_match('~^rgba\((?<r>\d+), (?<g>\d+), (?<b>\d+),~', $value, $matches)) {
                [
                    'r' => $r,
                    'g' => $g,
                    'b' => $b,
                ] = $matches;

                $css .= \sprintf(
                    "\t--%s-rgb: %d %d %d;\n",
                    $key,
                    $r,
                    $g,
                    $b,
                );
            }
        }

        return $css;
    }

    /**
     * Returns the name of the CSS file for a specific style.
     *
     * @since 5.3
     */
    public static function getFilenameForStyle(Style $style): string
    {
        return WCF_DIR . 'style/style-' . $style->styleID;
    }
}
