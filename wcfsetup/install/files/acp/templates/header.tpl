<!DOCTYPE html>
<html dir="{@$__wcf->getLanguage()->getPageDirection()}" lang="{@$__wcf->getLanguage()->getFixedLanguageCode()}">
<head>
	<base href="{$baseHref}" />
	<meta charset="utf-8" />
	<title>{if $pageTitle|isset}{@$pageTitle}{else}{lang}wcf.global.pageTitle{/lang}{/if} - {lang}wcf.acp{/lang}</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<script type="text/javascript">
		//<![CDATA[
		var SID_ARG_1ST = '{@SID_ARG_1ST}';
		var SID_ARG_2ND	= '{@SID_ARG_2ND_NOT_ENCODED}';
		var RELATIVE_WCF_DIR = '{@RELATIVE_WCF_DIR}';
		var SECURITY_TOKEN = '{@SECURITY_TOKEN}';
		var LANGUAGE_ID = {@$__wcf->getLanguage()->languageID};
		//]]>
	</script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/3rdParty/jquery.min.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/3rdParty/jquery-ui.min.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/3rdParty/jquery.tools.min.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/WCF.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}acp/js/WCF.ACP.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		WCF.User.init({$__wcf->user->userID}, '{@$__wcf->user->username|encodeJS}');
		//]]>
	</script>
	
	<!-- Stylesheets -->
	<style type="text/css">
		@import url("{@RELATIVE_WCF_DIR}acp/style/wcf.css") screen;
		{*
		@import url("{@RELATIVE_WCF_DIR}acp/style/style-{@$__wcf->getLanguage()->getPageDirection()}.css") screen;
	
		@import url("{@RELATIVE_WCF_DIR}acp/style/print.css") print;
		*}
	</style>
	
	{*
	{if $specialStyles|isset}
		<!-- special styles -->
		{@$specialStyles}
	{/if}
	*}
	
	<script type="text/javascript">
		//<![CDATA[
		$(function() {
			{* work-around for unknown core-object during WCFSetup *}
			{if PACKAGE_ID}
				{assign var=activeMenuItems value=$__wcf->getACPMenu()->getActiveMenuItems()|array_reverse}
				var $activeMenuItems = [{implode from=$activeMenuItems item=menuItem}'{$menuItem}'{/implode}];
				new WCF.ACP.Menu($activeMenuItems);
			{/if}
			
			WCF.Language.addObject({
				'wcf.global.button.next': '{lang}wcf.global.button.next{/lang}',
				'wcf.global.loading': '{lang}wcf.global.loading{/lang}',
				'wcf.date.relative.minutes': '{capture assign=relativeMinutes}{lang}wcf.date.relative.minutes{/lang}{/capture}{@$relativeMinutes|encodeJS}',
				'wcf.date.relative.hours': '{capture assign=relativeHours}{lang}wcf.date.relative.hours{/lang}{/capture}{@$relativeHours|encodeJS}',
				'wcf.date.relative.pastDays': '{capture assign=relativePastDays}{lang}wcf.date.relative.pastDays{/lang}{/capture}{@$relativePastDays|encodeJS}',
				'wcf.date.dateTimeFormat': '{lang}wcf.date.dateTimeFormat{/lang}',
				'__days': [ '{lang}wcf.date.day.sunday{/lang}', '{lang}wcf.date.day.monday{/lang}', '{lang}wcf.date.day.tuesday{/lang}', '{lang}wcf.date.day.wednesday{/lang}', '{lang}wcf.date.day.thursday{/lang}', '{lang}wcf.date.day.friday{/lang}', '{lang}wcf.date.day.saturday{/lang}' ],
				'wcf.global.thousandsSeparator': '{capture assign=thousandsSeparator}{lang}wcf.global.thousandsSeparator{/lang}{/capture}{@$thousandsSeparator|encodeJS}',
				'wcf.global.decimalPoint': '{capture assign=decimalPoint}{lang}wcf.global.decimalPoint{/lang}{/capture}{$decimalPoint|encodeJS}',
				'wcf.global.page.next': '{capture assign=pageNext}{lang}wcf.global.page.next{/lang}{/capture}{@$pageNext|encodeJS}',
				'wcf.global.page.previous': '{capture assign=pagePrevious}{lang}wcf.global.page.previous{/lang}{/capture}{@$pagePrevious|encodeJS}',
				'wcf.global.button.collapsible': '{lang}wcf.global.button.collapsible{/lang}',
				'wcf.global.button.disable': '{lang}wcf.global.button.disable{/lang}',
				'wcf.global.button.enable': '{lang}wcf.global.button.enable{/lang}',
				'wcf.global.confirmation.cancel': '{lang}wcf.global.confirmation.cancel{/lang}',
				'wcf.global.confirmation.confirm': '{lang}wcf.global.confirmation.confirm{/lang}',
				'wcf.global.confirmation.title': '{lang}wcf.global.confirmation.title{/lang}'
			});
			new WCF.Date.Time();
			new WCF.Effect.SmoothScroll();
			new WCF.Effect.BalloonTooltip();
			$('<span class="pointer"><span></span></span>').appendTo('.wcf-innerError');
			
			$('#sidebarContent').wcfSidebar();
		});
		//]]>
	</script>
</head>

<body id="tpl{$templateName|ucfirst}">
	<a id="top"></a>
	<!-- HEADER -->
	<header id="pageHeader" class="wcf-pageHeader">
		<div>
			{if $__wcf->user->userID}
				<!-- top menu -->
				<nav id="topMenu" class="wcf-topMenu">
					<div>
						<ul>
							<li id="userMenu" class="wcf-userMenu"><!-- ToDo: We need an ID and/or class for each list here, this ID may also change! -->
								<span class="wcf-dropdownCaption userAvatar">{lang}wcf.user.userNote{/lang}</span>
								<ul class="wcf-dropdown">
									<li><a href="{link controller='Logout'}t={@SECURITY_TOKEN}{/link}" onclick="return confirm('{lang}wcf.user.logout.sure{/lang}')">{lang}wcf.user.logout{/lang}</a></li>
								</ul>
							</li>
						</ul>
					</div>
				</nav>
				<!-- /top menu -->
			{/if}
			
			<!-- logo -->
			<div id="logo" class="wcf-logo">
				<!-- clickable area -->
				<a href="{link controller='Index'}{/link}">
					<h1>WoltLab Community Framework 2.0 Alpha 1</h1>
					<img src="{@RELATIVE_WCF_DIR}acp/images/wcfLogo2.svg" width="321" height="58" alt="Product-logo" title="WoltLab Community Framework 2.0 Alpha 1" />
				</a>
				<!-- /clickable area -->
				
				<!-- search area -->
				
				<!-- /search area -->
			</div>
			<!-- /logo -->
			
			<!-- main menu -->
			<nav id="mainMenu" class="wcf-mainMenu">
				{* work-around for unknown core-object during WCFSetup *}
				{if PACKAGE_ID}
					<ul>
						{foreach from=$__wcf->getACPMenu()->getMenuItems('') item=menuItem}
							<li data-menu-item="{$menuItem->menuItem}"><a>{lang}{@$menuItem->menuItem}{/lang}</a></li>
						{/foreach}
					</ul>
				{/if}
			</nav>
			<!-- /main menu -->
			
			<!-- header navigation -->
			<nav class="wcf-headerNavigation">
				<ul>
					<li id="toBottomLink" class="toBottomLink"><a href="{@$__wcf->getAnchor('bottom')}" title="{lang}wcf.global.scrollDown{/lang}" class="jsTooltip"><img src="{@RELATIVE_WCF_DIR}icon/toBottom.svg" alt="" /> <span class="invisible">{lang}wcf.global.scrollDown{/lang}</span></a></li>
				</ul>
			</nav>
			<!-- /header navigation -->
		</div>
	</header>
	<!-- /HEADER -->
	
	<!-- MAIN -->
	<div id="main" class="wcf-main left">
		<div>
			{hascontent}
				<!-- SIDEBAR -->
				<aside class="wcf-sidebar">
					<!-- sidebar menu -->
					<nav id="sidebarContent" class="wcf-sidebarContent">
						{content}
							{* work-around for unknown core-object during WCFSetup *}
							{if PACKAGE_ID}
								{foreach from=$__wcf->getACPMenu()->getMenuItems('') item=parentMenuItem}
									<div id="{$parentMenuItem->menuItem}-container" style="display: none;" class="wcf-menuContainer collapsibleMenus" data-parent-menu-item="{$parentMenuItem->menuItem}">
										{foreach from=$__wcf->getACPMenu()->getMenuItems($parentMenuItem->menuItem) item=menuItem}
											<h1 class="wcf-menuHeader" data-menu-item="{$menuItem->menuItem}">{lang}{@$menuItem->menuItem}{/lang}</h1>
											<div class="wcf-sidebarContentGroup">
												<ul id="{$menuItem->menuItem}">
													{foreach from=$__wcf->getACPMenu()->getMenuItems($menuItem->menuItem) item=menuItemCategory}
														{if $__wcf->getACPMenu()->getMenuItems($menuItemCategory->menuItem)|count > 0}
															{foreach from=$__wcf->getACPMenu()->getMenuItems($menuItemCategory->menuItem) item=subMenuItem}
																<li id="{$subMenuItem->menuItem}" data-menu-item="{$subMenuItem->menuItem}"><a href="{$subMenuItem->getLink()}">{lang}{$subMenuItem->menuItem}{/lang}</a></li>
															{/foreach}
														{else}
															<li id="{$menuItemCategory->menuItem}" data-menu-item="{$menuItemCategory->menuItem}"><a href="{$menuItemCategory->getLink()}">{lang}{$menuItemCategory->menuItem}{/lang}</a></li>
														{/if}
													{/foreach}
												</ul>
											</div>
										{/foreach}
									</div>
								{/foreach}
							{/if}
						{/content}
					</nav>
					<!-- /sidebar menu -->
				</aside>
				<!-- /SIDEBAR -->
			{/hascontent}
			
			<!-- CONTENT -->
			<section id="content" class="wcf-content">
				