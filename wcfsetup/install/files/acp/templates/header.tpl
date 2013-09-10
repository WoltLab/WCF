<!DOCTYPE html>
<html dir="{@$__wcf->getLanguage()->getPageDirection()}" lang="{@$__wcf->getLanguage()->getFixedLanguageCode()}">
<head>
	<base href="{$baseHref}" />
	<meta charset="utf-8" />
	<title>{if $pageTitle|isset}{@$pageTitle|language} - {/if}{lang}wcf.global.acp{/lang}{if PACKAGE_ID} - {PAGE_TITLE|language}{/if}</title>
	<script data-relocate="true">
		//<![CDATA[
		var SID_ARG_2ND = '{@SID_ARG_2ND_NOT_ENCODED}';
		var WCF_PATH = '{@$__wcf->getPath()}';
		var SECURITY_TOKEN = '{@SECURITY_TOKEN}';
		var LANGUAGE_ID = {@$__wcf->getLanguage()->languageID};
		var TIME_NOW = {@TIME_NOW};
		//]]>
	</script>
	<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/jquery.min.js?v={@$__wcfVersion}"></script>
	<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/jquery-ui.min.js?v={@$__wcfVersion}"></script>
	<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/jquery-ui.nestedSortable.min.js?v={@$__wcfVersion}"></script>
	<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/jquery-ui.timepicker.min.js?v={@$__wcfVersion}"></script>
	<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.js?v={@$__wcfVersion}"></script>
	<script data-relocate="true" src="{@$__wcf->getPath()}acp/js/WCF.ACP.js?v={@$__wcfVersion}"></script>
	<script data-relocate="true">
		//<![CDATA[
		WCF.User.init({$__wcf->user->userID}, '{@$__wcf->user->username|encodeJS}');
		//]]>
	</script>
	{event name='javascriptInclude'}
	
	<!-- Stylesheets -->
	{@$__wcf->getStyleHandler()->getStylesheet()}
	{event name='stylesheets'}
	
	<!-- Icons -->
	<link rel="shortcut icon" href="{@$__wcf->getPath()}images/favicon.ico" />
	<link rel="apple-touch-icon" href="{@$__wcf->getPath()}images/apple-touch-icon.png" />
	
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			{* work-around for unknown core-object during WCFSetup *}
			{if PACKAGE_ID}
				{assign var=activeMenuItems value=$__wcf->getACPMenu()->getActiveMenuItems()|array_reverse}
				var $activeMenuItems = [{implode from=$activeMenuItems item=_menuItem}'{$_menuItem}'{/implode}];
				new WCF.ACP.Menu($activeMenuItems);
			{/if}
			
			WCF.Language.addObject({
				'__days': [ '{lang}wcf.date.day.sunday{/lang}', '{lang}wcf.date.day.monday{/lang}', '{lang}wcf.date.day.tuesday{/lang}', '{lang}wcf.date.day.wednesday{/lang}', '{lang}wcf.date.day.thursday{/lang}', '{lang}wcf.date.day.friday{/lang}', '{lang}wcf.date.day.saturday{/lang}' ],
				'__daysShort': [ '{lang}wcf.date.day.sun{/lang}', '{lang}wcf.date.day.mon{/lang}', '{lang}wcf.date.day.tue{/lang}', '{lang}wcf.date.day.wed{/lang}', '{lang}wcf.date.day.thu{/lang}', '{lang}wcf.date.day.fri{/lang}', '{lang}wcf.date.day.sat{/lang}' ],
				'__months': [ '{lang}wcf.date.month.january{/lang}', '{lang}wcf.date.month.february{/lang}', '{lang}wcf.date.month.march{/lang}', '{lang}wcf.date.month.april{/lang}', '{lang}wcf.date.month.may{/lang}', '{lang}wcf.date.month.june{/lang}', '{lang}wcf.date.month.july{/lang}', '{lang}wcf.date.month.august{/lang}', '{lang}wcf.date.month.september{/lang}', '{lang}wcf.date.month.october{/lang}', '{lang}wcf.date.month.november{/lang}', '{lang}wcf.date.month.december{/lang}' ], 
				'__monthsShort': [ '{lang}wcf.date.month.jan{/lang}', '{lang}wcf.date.month.feb{/lang}', '{lang}wcf.date.month.mar{/lang}', '{lang}wcf.date.month.apr{/lang}', '{lang}wcf.date.month.may{/lang}', '{lang}wcf.date.month.jun{/lang}', '{lang}wcf.date.month.jul{/lang}', '{lang}wcf.date.month.aug{/lang}', '{lang}wcf.date.month.sep{/lang}', '{lang}wcf.date.month.oct{/lang}', '{lang}wcf.date.month.nov{/lang}', '{lang}wcf.date.month.dec{/lang}' ],
				'wcf.acp.search.noResults': '{lang}wcf.acp.search.noResults{/lang}',
				'wcf.clipboard.item.unmarkAll': '{lang}wcf.clipboard.item.unmarkAll{/lang}',
				'wcf.date.relative.now': '{lang}wcf.date.relative.now{/lang}',
				'wcf.date.relative.minutes': '{capture assign=relativeMinutes}{lang}wcf.date.relative.minutes{/lang}{/capture}{@$relativeMinutes|encodeJS}',
				'wcf.date.relative.hours': '{capture assign=relativeHours}{lang}wcf.date.relative.hours{/lang}{/capture}{@$relativeHours|encodeJS}',
				'wcf.date.relative.pastDays': '{capture assign=relativePastDays}{lang}wcf.date.relative.pastDays{/lang}{/capture}{@$relativePastDays|encodeJS}',
				'wcf.date.dateFormat': '{lang}wcf.date.dateFormat{/lang}',
				'wcf.date.dateTimeFormat': '{lang}wcf.date.dateTimeFormat{/lang}',
				'wcf.date.shortDateTimeFormat': '{lang}wcf.date.shortDateTimeFormat{/lang}',
				'wcf.date.hour': '{lang}wcf.date.hour{/lang}',
				'wcf.date.minute': '{lang}wcf.date.minute{/lang}',
				'wcf.date.timeFormat': '{lang}wcf.date.timeFormat{/lang}',
				'wcf.global.button.add': '{lang}wcf.global.button.add{/lang}',
				'wcf.global.button.cancel': '{lang}wcf.global.button.cancel{/lang}',
				'wcf.global.button.close': '{lang}wcf.global.button.close{/lang}',
				'wcf.global.button.collapsible': '{lang}wcf.global.button.collapsible{/lang}',
				'wcf.global.button.delete': '{lang}wcf.global.button.delete{/lang}',
				'wcf.global.button.disable': '{lang}wcf.global.button.disable{/lang}',
				'wcf.global.button.disabledI18n': '{lang}wcf.global.button.disabledI18n{/lang}',
				'wcf.global.button.edit': '{lang}wcf.global.button.edit{/lang}',
				'wcf.global.button.enable': '{lang}wcf.global.button.enable{/lang}',
				'wcf.global.button.next': '{lang}wcf.global.button.next{/lang}',
				'wcf.global.button.preview': '{lang}wcf.global.button.preview{/lang}',
				'wcf.global.button.reset': '{lang}wcf.global.button.reset{/lang}',
				'wcf.global.button.save': '{lang}wcf.global.button.save{/lang}',
				'wcf.global.button.search': '{lang}wcf.global.button.search{/lang}',
				'wcf.global.button.submit': '{lang}wcf.global.button.submit{/lang}',
				'wcf.global.confirmation.cancel': '{lang}wcf.global.confirmation.cancel{/lang}',
				'wcf.global.confirmation.confirm': '{lang}wcf.global.confirmation.confirm{/lang}',
				'wcf.global.confirmation.title': '{lang}wcf.global.confirmation.title{/lang}',
				'wcf.global.decimalPoint': '{capture assign=decimalPoint}{lang}wcf.global.decimalPoint{/lang}{/capture}{$decimalPoint|encodeJS}',
				'wcf.global.error.timeout': '{lang}wcf.global.error.timeout{/lang}',
				'wcf.global.error.title': '{lang}wcf.global.error.title{/lang}',
				'wcf.global.form.error.empty': '{lang}wcf.global.form.error.empty{/lang}',
				'wcf.global.form.error.greaterThan': '{lang}wcf.global.form.error.greaterThan.javaScript{/lang}',
				'wcf.global.form.error.lessThan': '{lang}wcf.global.form.error.lessThan.javaScript{/lang}',
				'wcf.global.loading': '{lang}wcf.global.loading{/lang}',
				'wcf.global.page.jumpTo': '{lang}wcf.global.page.jumpTo{/lang}',
				'wcf.global.page.jumpTo.description': '{lang}wcf.global.page.jumpTo.description{/lang}',
				'wcf.global.page.pageNavigation': '{lang}wcf.global.page.pageNavigation{/lang}',
				'wcf.global.page.next': '{capture assign=pageNext}{lang}wcf.global.page.next{/lang}{/capture}{@$pageNext|encodeJS}',
				'wcf.global.page.previous': '{capture assign=pagePrevious}{lang}wcf.global.page.previous{/lang}{/capture}{@$pagePrevious|encodeJS}',
				'wcf.global.success': '{lang}wcf.global.success{/lang}',
				'wcf.global.success.add': '{lang}wcf.global.success.add{/lang}',
				'wcf.global.success.edit': '{lang}wcf.global.success.edit{/lang}',
				'wcf.global.thousandsSeparator': '{capture assign=thousandsSeparator}{lang}wcf.global.thousandsSeparator{/lang}{/capture}{@$thousandsSeparator|encodeJS}'
				{event name='javascriptLanguageImport'}
			});
			
			if (jQuery.browser.touch) $('html').addClass('touch');
			new WCF.Date.Time();
			new WCF.Effect.SmoothScroll();
			new WCF.Effect.BalloonTooltip();
			
			WCF.Dropdown.init();
			WCF.System.PageNavigation.init('.pageNavigation');
			WCF.Date.Picker.init();
			
			{if $__wcf->user->userID}
				new WCF.ACP.Search();
			{/if}
			
			{event name='javascriptInit'}
		});
		//]]>
	</script>
</head>

<body id="tpl{$templateName|ucfirst}">
	<a id="top"></a>
	
	<header id="pageHeader" class="layoutFluid">
		<div>
			{if $__wcf->user->userID}
				<nav id="topMenu" class="userPanel">
					<div class="layoutFluid">
						<ul class="userPanelItems">
							<li id="userMenu" class="dropdown">
								<a class="dropdownToggle framed" data-toggle="userMenu">{if PACKAGE_ID}{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(24)} {/if}{lang}wcf.user.userNote{/lang}</a>
								<ul class="dropdownMenu">
									{if PACKAGE_ID > 1}
										<li><a href="{@$__wcf->getPageMenu()->getLandingPage()->getLink()}">{lang}wcf.global.toLandingPage{/lang}</a></li>
										<li class="dropdownDivider"></li>
									{/if}
									<li><a href="{link controller='Logout'}t={@SECURITY_TOKEN}{/link}" onclick="WCF.System.Confirmation.show('{lang}wcf.user.logout.sure{/lang}', $.proxy(function (action) { if (action == 'confirm') window.location.href = $(this).attr('href'); }, this)); return false;">{lang}wcf.user.logout{/lang}</a></li>
								</ul>
							</li>
							{event name='topMenu'}
						</ul>
						
						{if $__wcf->getSession()->getPermission('admin.general.canUseAcp')}
							<aside id="search" class="searchBar">
								<form>
									<input type="search" name="q" placeholder="{lang}wcf.global.search.enterSearchTerm{/lang}" value="" />
								</form>
							</aside>
						{/if}
					</div>
				</nav>
			{/if}
			
			<div id="logo" class="logo">
				<a href="{link}{/link}">
					<h1>{lang}wcf.global.acp{/lang}</h1>
					{if PACKAGE_ID > 1}
						{event name='headerLogo'}
					{else}
						<img src="{@$__wcf->getPath()}acp/images/wcfLogo.png" alt="" style="height: 80px; width: 502px;" />
					{/if}
				</a>
			</div>
			
			{* work-around for unknown core-object during WCFSetup *}
			{if PACKAGE_ID && $__wcf->user->userID}
				{hascontent}
					<nav id="mainMenu" class="mainMenu">
						<ul>{content}{foreach from=$__wcf->getACPMenu()->getMenuItems('') item=_menuItem}<li data-menu-item="{$_menuItem->menuItem}"><a>{lang}{@$_menuItem->menuItem}{/lang}</a></li>{/foreach}{/content}</ul>
					</nav>
				{/hascontent}
			{/if}
			
			<nav class="navigation navigationHeader">
				<ul class="navigationIcons">
					<li id="toBottomLink" class="toBottomLink"><a href="{@$__wcf->getAnchor('bottom')}" title="{lang}wcf.global.scrollDown{/lang}" class="jsTooltip"><span class="icon icon16 icon-arrow-down"></span> <span class="invisible">{lang}wcf.global.scrollDown{/lang}</span></a></li>
					{event name='navigationIcons'}
				</ul>
			</nav>
		</div>
	</header>
	
	<div id="main" class="layoutFluid{if PACKAGE_ID && $__wcf->user->userID && $__wcf->getACPMenu()->getMenuItems('')|count} sidebarOrientationLeft{/if}">
		<div>
			<div>
				{hascontent}
					<aside class="sidebar collapsibleMenu">
						<div>
							{content}
								{* work-around for unknown core-object during WCFSetup *}
								{if PACKAGE_ID && $__wcf->user->userID}
									{foreach from=$__wcf->getACPMenu()->getMenuItems('') item=_parentMenuItem}
										<div id="{$_parentMenuItem->menuItem}-container" style="display: none;" class="menuGroup collapsibleMenus" data-parent-menu-item="{$_parentMenuItem->menuItem}">
											{foreach from=$__wcf->getACPMenu()->getMenuItems($_parentMenuItem->menuItem) item=_menuItem}
												<fieldset>
													<legend class="menuHeader" data-menu-item="{$_menuItem->menuItem}">{@$_menuItem}</legend>
													
													<nav class="menuGroupItems">
														<ul id="{$_menuItem->menuItem}">
															{foreach from=$__wcf->getACPMenu()->getMenuItems($_menuItem->menuItem) item=menuItemCategory}
																{if $__wcf->getACPMenu()->getMenuItems($menuItemCategory->menuItem)|count > 0}
																	{foreach from=$__wcf->getACPMenu()->getMenuItems($menuItemCategory->menuItem) item=subMenuItem}
																		<li id="{$subMenuItem->menuItem}" data-menu-item="{$subMenuItem->menuItem}"><a href="{$subMenuItem->getLink()}">{@$subMenuItem}</a></li>
																	{/foreach}
																{else}
																	<li id="{$menuItemCategory->menuItem}" data-menu-item="{$menuItemCategory->menuItem}"><a href="{$menuItemCategory->getLink()}">{@$menuItemCategory}</a></li>
																{/if}
															{/foreach}
														</ul>
													</nav>
												</fieldset>
											{/foreach}
										</div>
									{/foreach}
								{/if}
							{/content}
						</div>
					</aside>
				{/hascontent}
				
				<section id="content" class="content">
				
