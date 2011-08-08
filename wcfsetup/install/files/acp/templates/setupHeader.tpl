<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="{@$__wcf->getLanguage()->getPageDirection()}" xml:lang="{@$__wcf->getLanguage()->getFixedLanguageCode()}">
<head>
	<title>{@$pageTitle} - {lang}wcf.global.pageTitle{/lang}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<script type="text/javascript">
		//<![CDATA[
		var SID_ARG_2ND	= '{@SID_ARG_2ND_NOT_ENCODED}';
		var RELATIVE_WCF_DIR = '{@RELATIVE_WCF_DIR}';
		//]]>
	</script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}acp/js/x.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/3rdParty/protoaculous.1.8.2.min.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/default.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/AjaxRequest.class.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}acp/js/default.js"></script>
	
	<!-- Testing stylesheets -->
	<link rel="stylesheet" type="text/css" href="{@RELATIVE_WCF_DIR}acp/style/testing-reset.css" />
	<link rel="stylesheet" type="text/css" href="{@RELATIVE_WCF_DIR}acp/style/testing.css" />
	<!-- /Testing stylesheets -->
	
	{*
	<style type="text/css">
		@import url("{@RELATIVE_WCF_DIR}acp/style/extra/setupStyle{if $__wcf->getLanguage()->getPageDirection() == 'rtl'}-rtl{/if}.css");
	</style>
	*}
</head>

<body id="tpl{$templateName|ucfirst}">
	<a id="top"></a>
	<!-- HEADER -->
	<header class="pageHeader">
		<div>
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
				<ul>
					<li id="toBottomLink" class="toBottomLink"><a href="#bottom" title="{lang}wcf.global.scrollDown{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/downS.png" alt="{lang}wcf.global.scrollDown{/lang}" /> <span class="invisible">{lang}wcf.global.scrollDown{/lang}</span></a></li>
				</ul>
			</nav>
			<!-- /header navigation -->
		</div>
	</header>
	<!-- /HEADER -->
	
	<!-- CONTENT -->
	<div id="main" class="main">
		<div>
			<section id="content" class="content">