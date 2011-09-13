<!DOCTYPE html>
<html dir="{@$__wcf->getLanguage()->getPageDirection()}" lang="{@$__wcf->getLanguage()->getFixedLanguageCode()}">
<head>
	<meta charset="utf-8" />
	<title>{lang}wcf.global.progressBar{/lang} - {lang}wcf.global.pageTitle{/lang}</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&amp;showCSS=testing.css" />
</head>

<body>
	<a id="top"></a>
	<!-- HEADER -->
	<header id="pageHeader" class="pageHeader">
		<div>
			<!-- no top menu -->
			
			<!-- logo -->
			<div id="logo" class="logo">
				<div><!-- ToDo: This is just a little trick to compensate the missing link here, find a better solution -->
					<h1>Installation</h1><!-- ToDo: Use a proper text and language variable -->
					<img src="install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&amp;showImage=wcfLogoWhite.svg" width="300" height="58" alt="Product-logo" title="Installation" />
				</div>
				
				<!-- no search area -->
			</div>
			<!-- /logo -->
			
			<!-- no main menu -->
			
			<!-- header navigation -->
			<nav class="headerNavigation">
				<div>
					<ul>
						<li id="toBottomLink" class="toBottomLink"><a href="#bottom" title="{lang}wcf.global.scrollDown{/lang}" class="balloonTooltip"><img src="install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&amp;showIcon=toBottom.svg" alt="" /> <span class="invisible">{lang}wcf.global.scrollDown{/lang}</span></a></li>
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
			
			<!-- CONTENT -->
			<section id="content" class="content">
				
				<header class="mainHeading setup">
					<img src="install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&amp;showIcon=installation1.svg" alt="" />
					<hgroup>
						<h1>{lang}wcf.global.title{/lang}</h1>
						<h2>{lang}wcf.global.title.subtitle{/lang}</h2>
						{* ToDo: Progress bar *}
						<p><progress id="packageInstallationProgress" value="0" max="100" style="width: 300px;">0%</progress></p>
					</hgroup>
				</header>
				