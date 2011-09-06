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
	<link rel="stylesheet" type="text/css" href="{@RELATIVE_WCF_DIR}acp/style/testing-reset.css" />
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
				