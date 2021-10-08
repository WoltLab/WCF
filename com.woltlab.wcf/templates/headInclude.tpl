<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="format-detection" content="telephone=no">
{if $allowSpidersToIndexThisPage|empty && ($__wcf->getActivePage() == null || !$__wcf->getActivePage()->allowSpidersToIndex)}<meta name="robots" content="noindex,nofollow">{/if}
{implode from=$__wcf->getMetaTagHandler() item=__metaTag glue="\n"}{@$__metaTag}{/implode}
{event name='metaTags'}

<!-- Stylesheets -->
{if $__wcf->getStyleHandler()->getStyle()->getVariable('useGoogleFont')}
	<link href='//fonts.googleapis.com/css?family={$__wcf->getStyleHandler()->getStyle()->getVariable('wcfFontFamilyGoogle')|urlencode}:400,300,600' rel='stylesheet' type='text/css'>
{/if}
{@$__wcf->getStyleHandler()->getStylesheet()}
{event name='stylesheets'}

{include file='headIncludeJavaScript'}

<script data-relocate="true">
	WCF.Language.addObject({
		{* dummy language item to preserve compatibility with WCF 2.0, move this to headIncludeJavaScript *}
		'wcf.global.error.title': '{lang}wcf.global.error.title{/lang}'
		
		{* DEPRECATED -- PLEASE USE javascriptLanguageImport@headIncludeJavaScript *}
		{event name='javascriptLanguageImport'}
	});
</script>

{* DEPRECATED -- PLEASE USE javascriptInclude@headIncludeJavaScript *}
{event name='javascriptInclude'}

<!-- Icons -->
<link rel="apple-touch-icon" sizes="180x180" href="{$__wcf->getStyleHandler()->getStyle()->getFaviconAppleTouchIcon()}">
<link rel="manifest" href="{@$__wcf->getStyleHandler()->getStyle()->getFaviconManifest()}">
<link rel="shortcut icon" href="{@$__wcf->getFavicon()}">
<meta name="msapplication-config" content="{@$__wcf->getStyleHandler()->getStyle()->getFaviconBrowserconfig()}">
<meta name="theme-color" content="{$__wcf->getStyleHandler()->getStyle()->getVariable('wcfHeaderBackground', true)}">

<script data-relocate="true">
	$(function() {
		{* DEPRECATED -- PLEASE USE javascriptInit@headIncludeJavaScript *}
		{event name='javascriptInit'}
	});
</script>

{@HEAD_CODE}
