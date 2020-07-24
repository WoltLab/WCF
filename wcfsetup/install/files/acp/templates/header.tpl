<!DOCTYPE html>
<html dir="{@$__wcf->getLanguage()->getPageDirection()}" lang="{@$__wcf->getLanguage()->getFixedLanguageCode()}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex">
	<title>{if $pageTitle|isset}{@$pageTitle|language} - {/if}{lang}wcf.global.acp{/lang}{if PACKAGE_ID} - {PAGE_TITLE|language}{/if}</title>
	
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
		var SECURITY_TOKEN = '{@SECURITY_TOKEN}';
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
	
	{js application='wcf' lib='polyfill' file='promise' bundle='WoltLabSuite.Core' core='true'}
	{js application='wcf' file='require' bundle='WoltLabSuite.Core' core='true'}
	{js application='wcf' file='require.config' bundle='WoltLabSuite.Core' core='true'}
	{js application='wcf' file='require.linearExecution' bundle='WoltLabSuite.Core' core='true'}
	{js application='wcf' file='wcf.globalHelper' bundle='WoltLabSuite.Core' core='true'}
	{js application='wcf' file='closest' bundle='WoltLabSuite.Core' core='true'}
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
				'__days': [ '{lang}wcf.date.day.sunday{/lang}', '{lang}wcf.date.day.monday{/lang}', '{lang}wcf.date.day.tuesday{/lang}', '{lang}wcf.date.day.wednesday{/lang}', '{lang}wcf.date.day.thursday{/lang}', '{lang}wcf.date.day.friday{/lang}', '{lang}wcf.date.day.saturday{/lang}' ],
				'__daysShort': [ '{lang}wcf.date.day.sun{/lang}', '{lang}wcf.date.day.mon{/lang}', '{lang}wcf.date.day.tue{/lang}', '{lang}wcf.date.day.wed{/lang}', '{lang}wcf.date.day.thu{/lang}', '{lang}wcf.date.day.fri{/lang}', '{lang}wcf.date.day.sat{/lang}' ],
				'__months': [ '{lang}wcf.date.month.january{/lang}', '{lang}wcf.date.month.february{/lang}', '{lang}wcf.date.month.march{/lang}', '{lang}wcf.date.month.april{/lang}', '{lang}wcf.date.month.may{/lang}', '{lang}wcf.date.month.june{/lang}', '{lang}wcf.date.month.july{/lang}', '{lang}wcf.date.month.august{/lang}', '{lang}wcf.date.month.september{/lang}', '{lang}wcf.date.month.october{/lang}', '{lang}wcf.date.month.november{/lang}', '{lang}wcf.date.month.december{/lang}' ], 
				'__monthsShort': [ '{lang}wcf.date.month.short.jan{/lang}', '{lang}wcf.date.month.short.feb{/lang}', '{lang}wcf.date.month.short.mar{/lang}', '{lang}wcf.date.month.short.apr{/lang}', '{lang}wcf.date.month.short.may{/lang}', '{lang}wcf.date.month.short.jun{/lang}', '{lang}wcf.date.month.short.jul{/lang}', '{lang}wcf.date.month.short.aug{/lang}', '{lang}wcf.date.month.short.sep{/lang}', '{lang}wcf.date.month.short.oct{/lang}', '{lang}wcf.date.month.short.nov{/lang}', '{lang}wcf.date.month.short.dec{/lang}' ],
				'wcf.acp.search.noResults': '{lang}wcf.acp.search.noResults{/lang}',
				'wcf.clipboard.item.unmarkAll': '{lang}wcf.clipboard.item.unmarkAll{/lang}',
				'wcf.clipboard.item.markAll': '{lang}wcf.clipboard.item.markAll{/lang}',
				'wcf.clipboard.item.mark': '{lang}wcf.clipboard.item.mark{/lang}',
				'wcf.date.relative.now': '{lang __literal=true}wcf.date.relative.now{/lang}',
				'wcf.date.relative.minutes': '{capture assign=relativeMinutes}{lang __literal=true}wcf.date.relative.minutes{/lang}{/capture}{@$relativeMinutes|encodeJS}',
				'wcf.date.relative.hours': '{capture assign=relativeHours}{lang __literal=true}wcf.date.relative.hours{/lang}{/capture}{@$relativeHours|encodeJS}',
				'wcf.date.relative.pastDays': '{capture assign=relativePastDays}{lang __literal=true}wcf.date.relative.pastDays{/lang}{/capture}{@$relativePastDays|encodeJS}',
				'wcf.date.dateFormat': '{"wcf.date.dateFormat"|language|encodeJS}',
				'wcf.date.dateTimeFormat': '{lang}wcf.date.dateTimeFormat{/lang}',
				'wcf.date.shortDateTimeFormat': '{lang}wcf.date.shortDateTimeFormat{/lang}',
				'wcf.date.hour': '{lang}wcf.date.hour{/lang}',
				'wcf.date.minute': '{lang}wcf.date.minute{/lang}',
				'wcf.date.timeFormat': '{lang}wcf.date.timeFormat{/lang}',
				'wcf.date.firstDayOfTheWeek': '{lang}wcf.date.firstDayOfTheWeek{/lang}',
				'wcf.global.button.add': '{lang}wcf.global.button.add{/lang}',
				'wcf.global.button.cancel': '{lang}wcf.global.button.cancel{/lang}',
				'wcf.global.button.close': '{lang}wcf.global.button.close{/lang}',
				'wcf.global.button.collapsible': '{lang}wcf.global.button.collapsible{/lang}',
				'wcf.global.button.delete': '{lang}wcf.global.button.delete{/lang}',
				'wcf.global.button.disable': '{lang}wcf.global.button.disable{/lang}',
				'wcf.global.button.disabledI18n': '{lang}wcf.global.button.disabledI18n{/lang}',
				'wcf.global.button.edit': '{lang}wcf.global.button.edit{/lang}',
				'wcf.global.button.enable': '{lang}wcf.global.button.enable{/lang}',
				'wcf.global.button.hide': '{lang}wcf.global.button.hide{/lang}',
				'wcf.global.button.insert': '{lang}wcf.global.button.insert{/lang}',
				'wcf.global.button.next': '{lang}wcf.global.button.next{/lang}',
				'wcf.global.button.preview': '{lang}wcf.global.button.preview{/lang}',
				'wcf.global.button.reset': '{lang}wcf.global.button.reset{/lang}',
				'wcf.global.button.save': '{lang}wcf.global.button.save{/lang}',
				'wcf.global.button.search': '{lang}wcf.global.button.search{/lang}',
				'wcf.global.button.submit': '{lang}wcf.global.button.submit{/lang}',
				'wcf.global.button.upload': '{lang}wcf.global.button.upload{/lang}',
				'wcf.global.confirmation.cancel': '{lang}wcf.global.confirmation.cancel{/lang}',
				'wcf.global.confirmation.confirm': '{lang}wcf.global.confirmation.confirm{/lang}',
				'wcf.global.confirmation.title': '{lang}wcf.global.confirmation.title{/lang}',
				'wcf.global.decimalPoint': '{capture assign=decimalPoint}{lang}wcf.global.decimalPoint{/lang}{/capture}{$decimalPoint|encodeJS}',
				'wcf.global.error.timeout': '{lang}wcf.global.error.timeout{/lang}',
				'wcf.global.error.title': '{lang}wcf.global.error.title{/lang}',
				'wcf.global.form.error.empty': '{lang}wcf.global.form.error.empty{/lang}',
				'wcf.global.form.error.greaterThan': '{lang __literal=true}wcf.global.form.error.greaterThan{/lang}',
				'wcf.global.form.error.lessThan': '{lang __literal=true}wcf.global.form.error.lessThan{/lang}',
				'wcf.global.form.error.multilingual': '{lang}wcf.global.form.error.multilingual{/lang}',
				'wcf.global.form.input.maxItems': '{lang}wcf.global.form.input.maxItems{/lang}',
				'wcf.global.language.noSelection': '{lang}wcf.global.language.noSelection{/lang}',
				'wcf.global.loading': '{lang}wcf.global.loading{/lang}',
				'wcf.global.noSelection': '{lang}wcf.global.noSelection{/lang}',
				'wcf.global.select': '{lang}wcf.global.select{/lang}',
				'wcf.page.jumpTo': '{lang}wcf.page.jumpTo{/lang}',
				'wcf.page.jumpTo.description': '{lang}wcf.page.jumpTo.description{/lang}',
				'wcf.global.page.pagination': '{lang}wcf.global.page.pagination{/lang}',
				'wcf.global.page.next': '{capture assign=pageNext}{lang}wcf.global.page.next{/lang}{/capture}{@$pageNext|encodeJS}',
				'wcf.global.page.previous': '{capture assign=pagePrevious}{lang}wcf.global.page.previous{/lang}{/capture}{@$pagePrevious|encodeJS}',
				'wcf.global.pageDirection': '{lang}wcf.global.pageDirection{/lang}',
				'wcf.global.reason': '{lang}wcf.global.reason{/lang}',
				'wcf.global.scrollUp': '{lang}wcf.global.scrollUp{/lang}',
				'wcf.global.success': '{lang}wcf.global.success{/lang}',
				'wcf.global.success.add': '{lang}wcf.global.success.add{/lang}',
				'wcf.global.success.edit': '{lang}wcf.global.success.edit{/lang}',
				'wcf.global.thousandsSeparator': '{capture assign=thousandsSeparator}{lang}wcf.global.thousandsSeparator{/lang}{/capture}{@$thousandsSeparator|encodeJS}',
				'wcf.page.pagePosition': '{lang __literal=true}wcf.page.pagePosition{/lang}',
				'wcf.menu.page': '{lang}wcf.menu.page{/lang}',
				'wcf.menu.user': '{lang}wcf.menu.user{/lang}',
				'wcf.date.datePicker': '{lang}wcf.date.datePicker{/lang}',
				'wcf.date.datePicker.previousMonth': '{lang}wcf.date.datePicker.previousMonth{/lang}',
				'wcf.date.datePicker.nextMonth': '{lang}wcf.date.datePicker.nextMonth{/lang}',
				'wcf.date.datePicker.month': '{lang}wcf.date.datePicker.month{/lang}',
				'wcf.date.datePicker.year': '{lang}wcf.date.datePicker.year{/lang}',
				'wcf.date.datePicker.hour': '{lang}wcf.date.datePicker.hour{/lang}',
				'wcf.date.datePicker.minute': '{lang}wcf.date.datePicker.minute{/lang}'
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
			
			WCF.System.PageNavigation.init('.pagination');
			
			{if $__wcf->user->userID}
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
	<div id="pageContainer" class="pageContainer{if !PACKAGE_ID || !$__wcf->user->userID} acpPageHiddenMenu{elseif $_acpPageSubMenuActive} acpPageSubMenuActive{/if}">
		{event name='beforePageHeader'}
		
		{include file='pageHeader'}
		
		{event name='afterPageHeader'}
		
		<div id="acpPageContentContainer" class="acpPageContentContainer">
			{include file='pageMenu'}
			
			<section id="main" class="main" role="main">
				<div class="layoutBoundary">
					<div id="content" class="content">
					
