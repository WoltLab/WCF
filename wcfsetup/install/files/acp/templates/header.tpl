<!DOCTYPE html>
<html
	dir="{@$__wcf->getLanguage()->getPageDirection()}"
	lang="{$__wcf->getLanguage()->getBcp47()}"
	data-color-scheme="{$__wcf->getStyleHandler()->getColorScheme()}"
>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex">
	<title>{if $pageTitle|isset}{@$pageTitle|language} - {/if}{jslang}wcf.global.acp{/jslang}{if PACKAGE_ID} - {PAGE_TITLE|phrase}{/if}</title>
	
	{* work-around for Microsoft Edge that sometimes does not apply this style, if it was set via an external stylesheet *}
	<style>ol, ul { list-style: none; }</style>
	
	<!-- Stylesheets -->
	{@$__wcf->getStyleHandler()->getStylesheet(true)}
	{event name='stylesheets'}
	
	<!-- Icons -->
	{if PACKAGE_ID && $__wcf->getStyleHandler()->getDefaultStyle()}
		<link rel="apple-touch-icon" sizes="180x180" href="{$__wcf->getStyleHandler()->getDefaultStyle()->getFaviconAppleTouchIcon()}">
		<link rel="manifest" href="{$__wcf->getStyleHandler()->getDefaultStyle()->getFaviconManifest()}">
		<link rel="icon" type="image/png" sizes="48x48" href="{$__wcf->getStyleHandler()->getDefaultStyle()->getFavicon()}">
		<meta name="msapplication-config" content="{$__wcf->getStyleHandler()->getDefaultStyle()->getFaviconBrowserconfig()}">
	{else}	
		<link rel="apple-touch-icon" sizes="180x180" href="{$__wcf->getPath()}images/favicon/default.apple-touch-icon.png">
		<link rel="manifest" href="{$__wcf->getPath()}images/favicon/default.manifest.json">
		<link rel="icon" href="{$__wcf->getPath()}images/favicon/default.favicon.ico">
		<meta name="msapplication-config" content="{$__wcf->getPath()}images/favicon/default.browserconfig.xml">
	{/if}
	<script data-eager="true">
	{
		{if $__wcf->getStyleHandler()->getColorScheme() === 'system'}
		{
			const colorScheme = matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
			document.documentElement.dataset.colorScheme = colorScheme;
		}
		{/if}
		
		const themeColor = document.createElement("meta");
		themeColor.name = "theme-color";
		themeColor.content = window.getComputedStyle(document.documentElement).getPropertyValue("--wcfHeaderBackground");
		document.currentScript.replaceWith(themeColor);
	}
	</script>

	<meta name="timezone" content="{$__wcf->user->getTimeZone()->getName()}">
	
	<script data-eager="true">
		var WCF_PATH = '{@$__wcf->getPath()}';
		var WSC_API_URL = '{@$__wcf->getPath()}acp/';
		{* The SECURITY_TOKEN is defined in wcf.globalHelper.js *}
		var LANGUAGE_ID = {@$__wcf->getLanguage()->languageID};
		var LANGUAGE_USE_INFORMAL_VARIANT = {if LANGUAGE_USE_INFORMAL_VARIANT}true{else}false{/if};
		var TIME_NOW = {@TIME_NOW};
		var LAST_UPDATE_TIME = {@LAST_UPDATE_TIME};
		var ENABLE_DEBUG_MODE = {if ENABLE_DEBUG_MODE}true{else}false{/if};
		var ENABLE_PRODUCTION_DEBUG_MODE = {if ENABLE_PRODUCTION_DEBUG_MODE}true{else}false{/if};
		var ENABLE_DEVELOPER_TOOLS = {if ENABLE_DEVELOPER_TOOLS}true{else}false{/if};
		
		{* This constant is a compiler option, it does not exist in production. *}
		{* Unlike the frontend, this option must be defined in the ACP at all times. *}
		var COMPILER_TARGET_DEFAULT = true;
	</script>

	<script data-eager="true" src="{$__wcf->getPath()}js/WoltLabSuite/WebComponent.min.js?v={@LAST_UPDATE_TIME}"></script>
	<script data-eager="true" src="{$phrasePreloader->getUrl($__wcf->language)}"></script>
	
	{js application='wcf' file='require' bundle='WoltLabSuite.Core' core='true'}
	{js application='wcf' file='require.config' bundle='WoltLabSuite.Core' core='true'}
	{js application='wcf' file='require.linearExecution' bundle='WoltLabSuite.Core' core='true'}
	{js application='wcf' file='wcf.globalHelper' bundle='WoltLabSuite.Core' core='true'}
	{js application='wcf' file='3rdParty/tslib' bundle='WoltLabSuite.Core' core='true'}
	<script>
		requirejs.config({
			baseUrl: '{@$__wcf->getPath()}js',
			urlArgs: 't={@LAST_UPDATE_TIME}'
			{hascontent}
			, paths: {
				{content}{event name='requirePaths'}{/content}
			}
			{/hascontent}
		});
		{event name='requireConfig'}
	</script>
	<script>
		require(['Language', 'WoltLabSuite/Core/Acp/Bootstrap', 'User'], function(Language, AcpBootstrap, User) {
			Language.addObject({
				'wcf.acp.search.noResults': '{jslang}wcf.acp.search.noResults{/jslang}',

				{event name='javascriptLanguageImport'}
			});
			
			User.init(
				{@$__wcf->user->userID},
				{if $__wcf->user->userID}'{@$__wcf->user->username|encodeJS}'{else}''{/if},
				{if $__wcf->user->userID}'{@$__wcf->user->getLink()|encodeJS}'{else}''{/if}
			);
			
			AcpBootstrap.setup({
				bootstrap: {
					dynamicColorScheme: {if $__wcf->getStyleHandler()->getColorScheme() === 'system'}true{else}false{/if},
					enableMobileMenu: {if PACKAGE_ID && $__isLogin|empty}true{else}false{/if},
				}
			});
		});
	</script>
	
	{include file='__devtoolsLanguageChooser'}
	
	<script>
		// prevent jQuery and other libraries from utilizing define()
		__require_define_amd = define.amd;
		define.amd = undefined;
	</script>
	{js application='wcf' lib='jquery'}
	{js application='wcf' lib='jquery-ui'}
	{js application='wcf' lib='jquery-ui' file='touchPunch' bundle='WCF.Combined'}
	{js application='wcf' lib='jquery-ui' file='nestedSortable' bundle='WCF.Combined'}
	{js application='wcf' file='WCF.Assets' bundle='WCF.Combined'}
	{js application='wcf' file='WCF' bundle='WCF.Combined'}
	{js application='wcf' acp='true' file='WCF.ACP'}
	<script>
		define.amd = __require_define_amd;
		$.holdReady(true);
		WCF.User.init(
			{@$__wcf->user->userID},
			{if $__wcf->user->userID}'{@$__wcf->user->username|encodeJS}'{else}''{/if}
		);
	</script>
	{js application='wcf' file='WCF.Message' bundle='WCF.Combined'}
	{js application='wcf' file='WCF.Label' bundle='WCF.Combined'}
	<script>
		$(function() {
			if (jQuery.browser.touch) $('html').addClass('touch');
			
			{if $__wcf->user->userID && $__isLogin|empty}
				new WCF.ACP.Search();
			{/if}
			
			{event name='javascriptInit'}
			
			$('form[method=get]').attr('method', 'post');
			
			// rewrites legacy links using the `dereferrer.php` service
			// see https://github.com/WoltLab/WCF/issues/2557
			elBySelAll('a', undefined, function(link) {
				if (/\/dereferrer.php$/.test(link.pathname) && link.search.match(/^\?url=([^&=]+)$/)) {
					link.href = unescape(RegExp.$1);
				}
				
				if (link.classList.contains('externalURL')) {
					var rel = (link.rel === '') ? [] : link.rel.split(' ');
					if (rel.indexOf('noopener') === -1) rel.push('noopener');
					
					link.rel = rel.join(' ');
				}
			});
			
			WCF.DOMNodeInsertedHandler.execute();
		});
	</script>
	{event name='javascriptInclude'}
	
	{if !$headContent|empty}
		{@$headContent}
	{/if}
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}" class="wcfAcp{if !$__isLogin|empty} acpAuthFlow{/if}">
	<span id="top"></span>
	
	{assign var=_acpPageSubMenuActive value=false}
	{if PACKAGE_ID}
		{assign var=_activeMenuItems value=$__wcf->getACPMenu()->getActiveMenuItems()}
		{foreach from=$__wcf->getACPMenu()->getMenuItems('') item=_sectionMenuItem}
			{if $_sectionMenuItem->menuItem|in_array:$_activeMenuItems}{assign var=_acpPageSubMenuActive value=true}{/if}
		{/foreach}
	{/if}
	<div id="pageContainer" class="pageContainer{if !PACKAGE_ID || !$__wcf->user->userID || !$__isLogin|empty} acpPageHiddenMenu{elseif $_acpPageSubMenuActive} acpPageSubMenuActive{/if}">
		{event name='beforePageHeader'}
		
		{include file='pageHeader'}
		
		{event name='afterPageHeader'}
		
		<div id="acpPageContentContainer" class="acpPageContentContainer">
			{include file='pageMenu'}
			
			<section id="main" class="main" role="main">
				<div class="layoutBoundary">
					<div id="content" class="content">
					
