<!DOCTYPE html>
<html dir="{@$__wcf->getLanguage()->getPageDirection()}" lang="{@$__wcf->getLanguage()->getFixedLanguageCode()}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex">
	<title>{if $pageTitle|isset}{@$pageTitle|language} - {/if}{jslang}wcf.global.acp{/jslang}{if PACKAGE_ID} - {PAGE_TITLE|language}{/if}</title>
	
	{* work-around for Microsoft Edge that sometimes does not apply this style, if it was set via an external stylesheet *}
	<style>ol, ul { list-style: none; }</style>
	
	<!-- Stylesheets -->
	{@$__wcf->getStyleHandler()->getStylesheet(true)}
	{event name='stylesheets'}
	
	<!-- Icons -->
	{if PACKAGE_ID && $__wcf->getStyleHandler()->getDefaultStyle()}
		<link rel="apple-touch-icon" sizes="180x180" href="{$__wcf->getStyleHandler()->getDefaultStyle()->getFaviconAppleTouchIcon()}">
		<link rel="manifest" href="{@$__wcf->getStyleHandler()->getDefaultStyle()->getFaviconManifest()}">
		<link rel="shortcut icon" href="{@$__wcf->getPath()}{@$__wcf->getStyleHandler()->getDefaultStyle()->getRelativeFavicon()}">
		<meta name="msapplication-config" content="{@$__wcf->getStyleHandler()->getDefaultStyle()->getFaviconBrowserconfig()}">
		<meta name="theme-color" content="{$__wcf->getStyleHandler()->getDefaultStyle()->getVariable('wcfPageThemeColor', true)}">
	{else}	
		<link rel="apple-touch-icon" sizes="180x180" href="{@$__wcf->getPath()}images/favicon/default.apple-touch-icon.png">
		<link rel="manifest" href="{@$__wcf->getPath()}images/favicon/default.manifest.json">
		<link rel="shortcut icon" href="{@$__wcf->getPath()}images/favicon/default.favicon.ico">
		<meta name="msapplication-config" content="{@$__wcf->getPath()}images/favicon/default.browserconfig.xml">
		<meta name="theme-color" content="#3a6d9c">
	{/if}
	
	<script>
		var SID_ARG_2ND = '';
		var WCF_PATH = '{@$__wcf->getPath()}';
		var WSC_API_URL = '{@$__wcf->getActivePath()}acp/';
		var SECURITY_TOKEN = '{csrfToken type=raw}';
		var LANGUAGE_ID = {@$__wcf->getLanguage()->languageID};
		var LANGUAGE_USE_INFORMAL_VARIANT = {if LANGUAGE_USE_INFORMAL_VARIANT}true{else}false{/if};
		var TIME_NOW = {@TIME_NOW};
		var LAST_UPDATE_TIME = {@LAST_UPDATE_TIME};
		var URL_LEGACY_MODE = false;
		var ENABLE_DEBUG_MODE = {if ENABLE_DEBUG_MODE}true{else}false{/if};
		var ENABLE_PRODUCTION_DEBUG_MODE = {if ENABLE_PRODUCTION_DEBUG_MODE}true{else}false{/if};
		var ENABLE_DEVELOPER_TOOLS = {if ENABLE_DEVELOPER_TOOLS}true{else}false{/if};
		var WSC_API_VERSION = {@WSC_API_VERSION};
		
		{* This constant is a compiler option, it does not exist in production. *}
		{* Unlike the frontend, this option must be defined in the ACP at all times. *}
		var COMPILER_TARGET_DEFAULT = true;
	</script>
	
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
				'__days': [ '{jslang}wcf.date.day.sunday{/jslang}', '{jslang}wcf.date.day.monday{/jslang}', '{jslang}wcf.date.day.tuesday{/jslang}', '{jslang}wcf.date.day.wednesday{/jslang}', '{jslang}wcf.date.day.thursday{/jslang}', '{jslang}wcf.date.day.friday{/jslang}', '{jslang}wcf.date.day.saturday{/jslang}' ],
				'__daysShort': [ '{jslang}wcf.date.day.sun{/jslang}', '{jslang}wcf.date.day.mon{/jslang}', '{jslang}wcf.date.day.tue{/jslang}', '{jslang}wcf.date.day.wed{/jslang}', '{jslang}wcf.date.day.thu{/jslang}', '{jslang}wcf.date.day.fri{/jslang}', '{jslang}wcf.date.day.sat{/jslang}' ],
				'__months': [ '{jslang}wcf.date.month.january{/jslang}', '{jslang}wcf.date.month.february{/jslang}', '{jslang}wcf.date.month.march{/jslang}', '{jslang}wcf.date.month.april{/jslang}', '{jslang}wcf.date.month.may{/jslang}', '{jslang}wcf.date.month.june{/jslang}', '{jslang}wcf.date.month.july{/jslang}', '{jslang}wcf.date.month.august{/jslang}', '{jslang}wcf.date.month.september{/jslang}', '{jslang}wcf.date.month.october{/jslang}', '{jslang}wcf.date.month.november{/jslang}', '{jslang}wcf.date.month.december{/jslang}' ], 
				'__monthsShort': [ '{jslang}wcf.date.month.short.jan{/jslang}', '{jslang}wcf.date.month.short.feb{/jslang}', '{jslang}wcf.date.month.short.mar{/jslang}', '{jslang}wcf.date.month.short.apr{/jslang}', '{jslang}wcf.date.month.short.may{/jslang}', '{jslang}wcf.date.month.short.jun{/jslang}', '{jslang}wcf.date.month.short.jul{/jslang}', '{jslang}wcf.date.month.short.aug{/jslang}', '{jslang}wcf.date.month.short.sep{/jslang}', '{jslang}wcf.date.month.short.oct{/jslang}', '{jslang}wcf.date.month.short.nov{/jslang}', '{jslang}wcf.date.month.short.dec{/jslang}' ],
				'wcf.acp.search.noResults': '{jslang}wcf.acp.search.noResults{/jslang}',
				'wcf.clipboard.item.unmarkAll': '{jslang}wcf.clipboard.item.unmarkAll{/jslang}',
				'wcf.clipboard.item.markAll': '{jslang}wcf.clipboard.item.markAll{/jslang}',
				'wcf.clipboard.item.mark': '{jslang}wcf.clipboard.item.mark{/jslang}',
				'wcf.date.relative.now': '{jslang __literal=true}wcf.date.relative.now{/jslang}',
				'wcf.date.relative.minutes': '{jslang __literal=true}wcf.date.relative.minutes{/jslang}',
				'wcf.date.relative.hours': '{jslang __literal=true}wcf.date.relative.hours{/jslang}',
				'wcf.date.relative.pastDays': '{jslang __literal=true}wcf.date.relative.pastDays{/jslang}',
				'wcf.date.dateFormat': '{jslang}wcf.date.dateFormat{/jslang}',
				'wcf.date.dateTimeFormat': '{jslang}wcf.date.dateTimeFormat{/jslang}',
				'wcf.date.shortDateTimeFormat': '{jslang}wcf.date.shortDateTimeFormat{/jslang}',
				'wcf.date.hour': '{jslang}wcf.date.hour{/jslang}',
				'wcf.date.minute': '{jslang}wcf.date.minute{/jslang}',
				'wcf.date.timeFormat': '{jslang}wcf.date.timeFormat{/jslang}',
				'wcf.date.firstDayOfTheWeek': '{jslang}wcf.date.firstDayOfTheWeek{/jslang}',
				'wcf.global.button.add': '{jslang}wcf.global.button.add{/jslang}',
				'wcf.global.button.cancel': '{jslang}wcf.global.button.cancel{/jslang}',
				'wcf.global.button.close': '{jslang}wcf.global.button.close{/jslang}',
				'wcf.global.button.collapsible': '{jslang}wcf.global.button.collapsible{/jslang}',
				'wcf.global.button.delete': '{jslang}wcf.global.button.delete{/jslang}',
				'wcf.button.delete.confirmMessage': '{jslang __literal=true}wcf.button.delete.confirmMessage{/jslang}',
				'wcf.global.button.disable': '{jslang}wcf.global.button.disable{/jslang}',
				'wcf.global.button.disabledI18n': '{jslang}wcf.global.button.disabledI18n{/jslang}',
				'wcf.global.button.edit': '{jslang}wcf.global.button.edit{/jslang}',
				'wcf.global.button.enable': '{jslang}wcf.global.button.enable{/jslang}',
				'wcf.global.button.hide': '{jslang}wcf.global.button.hide{/jslang}',
				'wcf.global.button.insert': '{jslang}wcf.global.button.insert{/jslang}',
				'wcf.global.button.next': '{jslang}wcf.global.button.next{/jslang}',
				'wcf.global.button.preview': '{jslang}wcf.global.button.preview{/jslang}',
				'wcf.global.button.reset': '{jslang}wcf.global.button.reset{/jslang}',
				'wcf.global.button.save': '{jslang}wcf.global.button.save{/jslang}',
				'wcf.global.button.search': '{jslang}wcf.global.button.search{/jslang}',
				'wcf.global.button.submit': '{jslang}wcf.global.button.submit{/jslang}',
				'wcf.global.button.upload': '{jslang}wcf.global.button.upload{/jslang}',
				'wcf.global.confirmation.cancel': '{jslang}wcf.global.confirmation.cancel{/jslang}',
				'wcf.global.confirmation.confirm': '{jslang}wcf.global.confirmation.confirm{/jslang}',
				'wcf.global.confirmation.title': '{jslang}wcf.global.confirmation.title{/jslang}',
				'wcf.global.decimalPoint': '{jslang}wcf.global.decimalPoint{/jslang}',
				'wcf.global.error.timeout': '{jslang}wcf.global.error.timeout{/jslang}',
				'wcf.global.error.title': '{jslang}wcf.global.error.title{/jslang}',
				'wcf.global.form.error.empty': '{jslang}wcf.global.form.error.empty{/jslang}',
				'wcf.global.form.error.greaterThan': '{jslang __literal=true}wcf.global.form.error.greaterThan{/jslang}',
				'wcf.global.form.error.lessThan': '{jslang __literal=true}wcf.global.form.error.lessThan{/jslang}',
				'wcf.global.form.error.multilingual': '{jslang}wcf.global.form.error.multilingual{/jslang}',
				'wcf.global.form.input.maxItems': '{jslang}wcf.global.form.input.maxItems{/jslang}',
				'wcf.global.language.noSelection': '{jslang}wcf.global.language.noSelection{/jslang}',
				'wcf.global.loading': '{jslang}wcf.global.loading{/jslang}',
				'wcf.global.noSelection': '{jslang}wcf.global.noSelection{/jslang}',
				'wcf.global.select': '{jslang}wcf.global.select{/jslang}',
				'wcf.page.jumpTo': '{jslang}wcf.page.jumpTo{/jslang}',
				'wcf.page.jumpTo.description': '{jslang}wcf.page.jumpTo.description{/jslang}',
				'wcf.global.page.pagination': '{jslang}wcf.global.page.pagination{/jslang}',
				'wcf.global.page.next': '{jslang}wcf.global.page.next{/jslang}',
				'wcf.global.page.previous': '{jslang}wcf.global.page.previous{/jslang}',
				'wcf.global.pageDirection': '{jslang}wcf.global.pageDirection{/jslang}',
				'wcf.global.reason': '{jslang}wcf.global.reason{/jslang}',
				'wcf.global.scrollUp': '{jslang}wcf.global.scrollUp{/jslang}',
				'wcf.global.success': '{jslang}wcf.global.success{/jslang}',
				'wcf.global.success.add': '{jslang}wcf.global.success.add{/jslang}',
				'wcf.global.success.edit': '{jslang}wcf.global.success.edit{/jslang}',
				'wcf.global.thousandsSeparator': '{jslang}wcf.global.thousandsSeparator{/jslang}',
				'wcf.page.pagePosition': '{jslang __literal=true}wcf.page.pagePosition{/jslang}',
				'wcf.menu.page': '{jslang}wcf.menu.page{/jslang}',
				'wcf.menu.user': '{jslang}wcf.menu.user{/jslang}',
				'wcf.date.datePicker': '{jslang}wcf.date.datePicker{/jslang}',
				'wcf.date.datePicker.previousMonth': '{jslang}wcf.date.datePicker.previousMonth{/jslang}',
				'wcf.date.datePicker.nextMonth': '{jslang}wcf.date.datePicker.nextMonth{/jslang}',
				'wcf.date.datePicker.month': '{jslang}wcf.date.datePicker.month{/jslang}',
				'wcf.date.datePicker.year': '{jslang}wcf.date.datePicker.year{/jslang}',
				'wcf.date.datePicker.hour': '{jslang}wcf.date.datePicker.hour{/jslang}',
				'wcf.date.datePicker.minute': '{jslang}wcf.date.datePicker.minute{/jslang}',
				'wcf.global.form.password.button.hide': '{jslang}wcf.global.form.password.button.hide{/jslang}',
				'wcf.global.form.password.button.show': '{jslang}wcf.global.form.password.button.show{/jslang}'
				{event name='javascriptLanguageImport'}
			});
			
			AcpBootstrap.setup({
				bootstrap: {
					enableMobileMenu: {if PACKAGE_ID && $__isLogin|empty}true{else}false{/if}
				}
			});
			
			User.init({@$__wcf->user->userID}, '{@$__wcf->user->username|encodeJS}', {if $__wcf->user->userID}'{@$__wcf->user->getLink()|encodeJS}'{else}''{/if});
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
	{js application='wcf' lib='polyfill' file='focus-visible' bundle='WCF.Combined' hasTiny=true}
	{js application='wcf' file='WCF.Assets' bundle='WCF.Combined'}
	{js application='wcf' file='WCF' bundle='WCF.Combined'}
	{js application='wcf' acp='true' file='WCF.ACP'}
	<script>
		define.amd = __require_define_amd;
		$.holdReady(true);
		WCF.User.init({$__wcf->user->userID}, '{@$__wcf->user->username|encodeJS}');
	</script>
	{js application='wcf' file='WCF.Attachment' bundle='WCF.Combined'}
	{js application='wcf' file='WCF.Message' bundle='WCF.Combined'}
	{js application='wcf' file='WCF.Label' bundle='WCF.Combined'}
	{js application='wcf' file='WCF.Poll' bundle='WCF.Combined'}
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
					if (rel.indexOf('noreferrer') === -1) rel.push('noreferrer');
					
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

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}" class="wcfAcp">
	<a id="top"></a>
	
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
					
