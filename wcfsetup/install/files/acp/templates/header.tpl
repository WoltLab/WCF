<!DOCTYPE html>
<html dir="{@$__wcf->getLanguage()->getPageDirection()}" lang="{@$__wcf->getLanguage()->getFixedLanguageCode()}">
<head>
	<base href="{$baseHref}">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex">
	<title>{if $pageTitle|isset}{@$pageTitle|language} - {/if}{lang}wcf.global.acp{/lang}{if PACKAGE_ID} - {PAGE_TITLE|language}{/if}</title>
	
	<!-- Stylesheets -->
	<link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600" rel="stylesheet">
	{@$__wcf->getStyleHandler()->getStylesheet(true)}
	{event name='stylesheets'}
	
	<!-- Icons -->
	<link rel="shortcut icon" href="{@$__wcf->getPath()}images/favicon.ico">
	<link rel="apple-touch-icon" href="{@$__wcf->getPath()}images/apple-touch-icon.png">
	
	<script>
		var SID_ARG_2ND = '{@SID_ARG_2ND_NOT_ENCODED}';
		var WCF_PATH = '{@$__wcf->getPath()}';
		var SECURITY_TOKEN = '{@SECURITY_TOKEN}';
		var LANGUAGE_ID = {@$__wcf->getLanguage()->languageID};
		var TIME_NOW = {@TIME_NOW};
		var URL_LEGACY_MODE = {if URL_LEGACY_MODE}true{else}false{/if};
	</script>
	
	{js application='wcf' file='require' bundle='WCF.Core' core='true'}
	{js application='wcf' file='require.config' bundle='WCF.Core' core='true'}
	{js application='wcf' file='require.linearExecution' bundle='WCF.Core' core='true'}
	{js application='wcf' file='wcf.globalHelper' bundle='WCF.Core' core='true'}
	<script>
		requirejs.config({
			baseUrl: '{@$__wcf->getPath()}js'
		});
	</script>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
		require(['Language', 'WoltLab/WCF/Acp/Bootstrap'], function(Language, AcpBootstrap) {
			Language.addObject({
				'__days': [ '{lang}wcf.date.day.sunday{/lang}', '{lang}wcf.date.day.monday{/lang}', '{lang}wcf.date.day.tuesday{/lang}', '{lang}wcf.date.day.wednesday{/lang}', '{lang}wcf.date.day.thursday{/lang}', '{lang}wcf.date.day.friday{/lang}', '{lang}wcf.date.day.saturday{/lang}' ],
				'__daysShort': [ '{lang}wcf.date.day.sun{/lang}', '{lang}wcf.date.day.mon{/lang}', '{lang}wcf.date.day.tue{/lang}', '{lang}wcf.date.day.wed{/lang}', '{lang}wcf.date.day.thu{/lang}', '{lang}wcf.date.day.fri{/lang}', '{lang}wcf.date.day.sat{/lang}' ],
				'__months': [ '{lang}wcf.date.month.january{/lang}', '{lang}wcf.date.month.february{/lang}', '{lang}wcf.date.month.march{/lang}', '{lang}wcf.date.month.april{/lang}', '{lang}wcf.date.month.may{/lang}', '{lang}wcf.date.month.june{/lang}', '{lang}wcf.date.month.july{/lang}', '{lang}wcf.date.month.august{/lang}', '{lang}wcf.date.month.september{/lang}', '{lang}wcf.date.month.october{/lang}', '{lang}wcf.date.month.november{/lang}', '{lang}wcf.date.month.december{/lang}' ], 
				'__monthsShort': [ '{lang}wcf.date.month.short.jan{/lang}', '{lang}wcf.date.month.short.feb{/lang}', '{lang}wcf.date.month.short.mar{/lang}', '{lang}wcf.date.month.short.apr{/lang}', '{lang}wcf.date.month.short.may{/lang}', '{lang}wcf.date.month.short.jun{/lang}', '{lang}wcf.date.month.short.jul{/lang}', '{lang}wcf.date.month.short.aug{/lang}', '{lang}wcf.date.month.short.sep{/lang}', '{lang}wcf.date.month.short.oct{/lang}', '{lang}wcf.date.month.short.nov{/lang}', '{lang}wcf.date.month.short.dec{/lang}' ],
				'wcf.acp.search.noResults': '{lang}wcf.acp.search.noResults{/lang}',
				'wcf.clipboard.item.unmarkAll': '{lang}wcf.clipboard.item.unmarkAll{/lang}',
				'wcf.date.relative.now': '{lang __literal=true}wcf.date.relative.now{/lang}',
				'wcf.date.relative.minutes': '{capture assign=relativeMinutes}{lang __literal=true}wcf.date.relative.minutes{/lang}{/capture}{@$relativeMinutes|encodeJS}',
				'wcf.date.relative.hours': '{capture assign=relativeHours}{lang __literal=true}wcf.date.relative.hours{/lang}{/capture}{@$relativeHours|encodeJS}',
				'wcf.date.relative.pastDays': '{capture assign=relativePastDays}{lang __literal=true}wcf.date.relative.pastDays{/lang}{/capture}{@$relativePastDays|encodeJS}',
				'wcf.date.dateFormat': '{lang}wcf.date.dateFormat{/lang}',
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
				'wcf.global.loading': '{lang}wcf.global.loading{/lang}',
				'wcf.global.page.jumpTo': '{lang}wcf.global.page.jumpTo{/lang}',
				'wcf.global.page.jumpTo.description': '{lang}wcf.global.page.jumpTo.description{/lang}',
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
				'wcf.page.pagePosition': '{lang __literal=true}wcf.page.pagePosition{/lang}'
				{event name='javascriptLanguageImport'}
			});
			
			AcpBootstrap.setup({
				bootstrap: {
					enableMobileMenu: {if $__isLogin|empty}true{else}false{/if}
				}
			});
		});
		});
	</script>
	{js application='wcf' lib='jquery'}
	
	<script>
		// prevent jQuery and other libraries from utilizing define()
		__require_define_amd = define.amd;
		define.amd = undefined;
	</script>
	{js application='wcf' lib='jquery-ui'}
	{js application='wcf' lib='jquery-ui' file='touchPunch' bundle='WCF.Combined'}
	{js application='wcf' lib='jquery-ui' file='nestedSortable' bundle='WCF.Combined'}
	{js application='wcf' file='WCF.Assets' bundle='WCF.Combined'}
	{js application='wcf' file='WCF' bundle='WCF.Combined'}
	{js application='wcf' acp='true' file='WCF.ACP'}
	<script>
		define.amd = __require_define_amd;
		$.holdReady(true);
		WCF.User.init({$__wcf->user->userID}, '{@$__wcf->user->username|encodeJS}');
	</script>
	<script>
		$(function() {
			if (jQuery.browser.touch) $('html').addClass('touch');
			
			new WCF.Effect.SmoothScroll();
			WCF.System.PageNavigation.init('.pagination');
			
			{if $__wcf->user->userID}
				new WCF.ACP.Search();
			{/if}
			
			{event name='javascriptInit'}
			
			$('form[method=get]').attr('method', 'post');
		});
	</script>
	{event name='javascriptInclude'}
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}" class="wcfAcp">
	<a id="top"></a>
	
	<div id="pageContainer" class="pageContainer">
		{event name='beforePageHeader'}
		
		{include file='pageHeader'}
		
		{event name='afterPageHeader'}
		
		<div id="acpPageContentContainer" class="acpPageContentContainer">
			{include file='pageMenu'}
			
			<section id="main" class="main" role="main">
				<div class="layoutBoundary">
					<div id="content" class="content">
					