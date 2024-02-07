<link rel="apple-touch-icon" sizes="180x180" href="{$__wcf->getStyleHandler()->getStyle()->getFaviconAppleTouchIcon()}">
<link rel="manifest" href="{$__wcf->getStyleHandler()->getStyle()->getFaviconManifest()}">
<link rel="icon" type="image/png" sizes="48x48" href="{$__wcf->getStyleHandler()->getStyle()->getFavicon()}">
<meta name="msapplication-config" content="{$__wcf->getStyleHandler()->getStyle()->getFaviconBrowserconfig()}">
<meta name="theme-color" content="{$__wcf->getStyleHandler()->getStyle()->getVariable('wcfPageThemeColor', true)}">
<script>
	{
		document.querySelector('meta[name="theme-color"]').content = window.getComputedStyle(document.documentElement).getPropertyValue("--wcfPageThemeColor");
	}
</script>
