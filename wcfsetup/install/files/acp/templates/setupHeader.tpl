<!DOCTYPE html>
<html dir="{@$__wcf->getLanguage()->getPageDirection()}" lang="{@$__wcf->getLanguage()->getFixedLanguageCode()}">
<head>
	<meta charset="utf-8" />
	<title>{@$pageTitle} - {lang}wcf.global.pageTitle{/lang}</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<script type="text/javascript">
		//<![CDATA[
		var SID_ARG_2ND	= '{@SID_ARG_2ND_NOT_ENCODED}';
		var RELATIVE_WCF_DIR = '{@RELATIVE_WCF_DIR}';
		var SECURITY_TOKEN = '{@SECURITY_TOKEN}';
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
	
	{*
	{if $specialStyles|isset}
		<!-- special styles -->
		{@$specialStyles}
	{/if}
	*}
	
	<!-- Testing stylesheets -->
	<link rel="stylesheet" type="text/css" href="{@RELATIVE_WCF_DIR}acp/style/testing.css" />
	<!-- /Testing stylesheets -->
	
	{*
	<style type="text/css">
		@import url("{@RELATIVE_WCF_DIR}acp/style/style-{@$__wcf->getLanguage()->getPageDirection()}.css");
	</style>
	
	<!--[if IE 8]>
		<style type="text/css">
			@import url("{@RELATIVE_WCF_DIR}style/extra/ie8-fix{if $__wcf->getLanguage()->getPageDirection() == 'rtl'}-rtl{/if}.css");
		</style>
	<![endif]-->
	*}
	
	<script type="text/javascript">
		//<![CDATA[
		$(function() {
			WCF.Language.addObject({
				'wcf.global.loading': '{lang}wcf.global.loading{/lang}',
				'wcf.global.date.relative.minutes': '{capture assign=relativeMinutes}{lang}wcf.global.date.relative.minutes{/lang}{/capture}{@$relativeMinutes|encodeJS}',
				'wcf.global.date.relative.hours': '{capture assign=relativeHours}{lang}wcf.global.date.relative.hours{/lang}{/capture}{@$relativeHours|encodeJS}',
				'wcf.global.date.relative.pastDays': '{capture assign=relativePastDays}{lang}wcf.global.date.relative.pastDays{/lang}{/capture}{@$relativePastDays|encodeJS}',
				'wcf.global.date.dateTimeFormat': '{lang}wcf.global.date.dateTimeFormat{/lang}',
				'__days': [ '{lang}wcf.global.date.day.sunday{/lang}', '{lang}wcf.global.date.day.monday{/lang}', '{lang}wcf.global.date.day.tuesday{/lang}', '{lang}wcf.global.date.day.wednesday{/lang}', '{lang}wcf.global.date.day.thursday{/lang}', '{lang}wcf.global.date.day.friday{/lang}', '{lang}wcf.global.date.day.saturday{/lang}' ],
				'wcf.global.thousandsSeparator': '{capture assign=thousandsSeparator}{lang}wcf.global.thousandsSeparator{/lang}{/capture}{@$thousandsSeparator|encodeJS}',
				'wcf.global.decimalPoint': '{capture assign=decimalPoint}{lang}wcf.global.decimalPoint{/lang}{/capture}{$decimalPoint|encodeJS}',
				'wcf.global.page.next': '{capture assign=pageNext}{lang}wcf.global.page.next{/lang}{/capture}{@$pageNext|encodeJS}',
				'wcf.global.page.previous': '{capture assign=pagePrevious}{lang}wcf.global.page.previous{/lang}{/capture}{@$pagePrevious|encodeJS}'
			});
			new WCF.Date.Time();
			new WCF.Effect.SmoothScroll();
			new WCF.Effect.BalloonTooltip();
		});
		//]]>
	</script>
</head>

<body id="tpl{$templateName|ucfirst}">
	<a id="top"></a>
	<!-- HEADER -->
	<header id="pageHeader" class="pageHeader">
		<div>
			<!-- no top menu -->
			
			<!-- logo -->
			<div id="logo" class="logo">
				<!-- clickable area -->
				<a href="index.php{@SID_ARG_1ST}">
					<h1>WoltLab Community Framework 2.0 Pre-Alpha 1</h1>
					<img src="{@RELATIVE_WCF_DIR}acp/images/wcfLogoWhite.svg" width="300" height="58" alt="Product-logo" title="WoltLab Community Framework 2.0 Pre-Alpha 1" />
				</a>
				<!-- /clickable area -->
				
				<!-- no search area -->
			</div>
			<!-- /logo -->
			
			<!-- main menu -->
			<nav id="mainMenu" class="mainMenu">
				<ul>
					<li class="activeMenuItem">Log-In</li>
				</ul>
			</nav>
			<!-- /main menu -->
			
			<!-- header navigation -->
			<nav class="headerNavigation">
				<div>
					<ul>
						<li id="toBottomLink" class="toBottomLink"><a href="#bottom" title="{lang}wcf.global.scrollDown{/lang}" class="balloonTooltip"><img src="{@RELATIVE_WCF_DIR}icon/toBottom.svg" alt="" /> <span class="invisible">{lang}wcf.global.scrollDown{/lang}</span></a></li>
					</ul>
				</div>
			</nav>
			<!-- /header navigation -->
		</div>
	</header>
	<!-- /HEADER -->
	
	<!-- MAIN -->
	<div id="main" class="main">
		<div>
			<!-- NO SIDEBAR -->
			
			<!-- CONTENT -->
			<section id="content" class="content">
				