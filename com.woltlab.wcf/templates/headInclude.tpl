<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="format-detection" content="telephone=no">
{include file='headIncludeRobotsMetaTag'}
{implode from=$__wcf->getMetaTagHandler() item=__metaTag glue="\n"}{@$__metaTag}{/implode}
{event name='metaTags'}

<!-- Stylesheets -->
{@$__wcf->getStyleHandler()->getStylesheet()}
{event name='stylesheets'}

{include file='headIncludeJavaScript'}

<!-- Icons -->
<link rel="apple-touch-icon" sizes="180x180" href="{$__wcf->getStyleHandler()->getStyle()->getFaviconAppleTouchIcon()}">
<link rel="manifest" href="{@$__wcf->getStyleHandler()->getStyle()->getFaviconManifest()}">
<link rel="shortcut icon" href="{@$__wcf->getFavicon()}">
<meta name="msapplication-config" content="{@$__wcf->getStyleHandler()->getStyle()->getFaviconBrowserconfig()}">
<meta name="theme-color" content="{$__wcf->getStyleHandler()->getStyle()->getVariable('wcfPageThemeColor', true)}">

{@HEAD_CODE}
