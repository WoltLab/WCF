<!DOCTYPE html>
<html dir="{@$__wcf->getLanguage()->getPageDirection()}" lang="{@$__wcf->getLanguage()->getFixedLanguageCode()}">
<head>
	<meta charset="utf-8" />
	<title>{lang}wcf.global.progressBar{/lang} - {lang}wcf.global.pageTitle{/lang}</title>
	
	<link rel="stylesheet" type="text/css" media="screen" href="{if $lastStep|isset}{@RELATIVE_WCF_DIR}acp/style/setup/{else}install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&amp;showCSS={/if}WCFSetup.css" />
	
	{if !$lastStep|isset}
		<style type="text/css">
			/*<![CDATA[*/
				@font-face {
					font-family: 'FontAwesome';
					src: url('install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&showFont=fontawesome-webfont.eot');
					src: url('install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&showFont=fontawesome-webfont.eot#iefix') format('embedded-opentype'),
						url('install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&showFont=fontawesome-webfont.ttf') format('truetype');
					font-weight: normal;
					font-style: normal;
				}
			/*]]>*/
		</style>
	{/if}
</head>

<body id="tplWCFInstaller">
	<a id="top"></a>
	
	<header id="pageHeader" class="layoutFluid">
		<div>
			<div id="logo" class="logo">
				<a>
					<h1>{lang}wcf.global.pageTitle{/lang}</h1>
					<img src="{if $lastStep|isset}{@RELATIVE_WCF_DIR}acp/images/{else}install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&amp;showImage={/if}wcfLogo.png" width="558" height="80" alt="" />
				</a>
			</div>
			
			<nav class="navigation navigationHeader">
				<ul class="navigationIcons">
					<li id="toBottomLink" class="toBottomLink"><a href="#bottom" title="{lang}wcf.global.scrollDown{/lang}" class="jsTooltip"><span class="icon icon16 icon-arrow-down"></span> <span class="invisible">{lang}wcf.global.scrollDown{/lang}</span></a></li>
				</ul>
			</nav>
		</div>
	</header>
	
	<div id="main" class="layoutFluid">
		<div>
			<div>
				<section id="content" class="content">
					<header class="boxHeadline">
						<h1>{lang}wcf.global.title{/lang}</h1>
						<p>{lang}wcf.global.title.subtitle{/lang}</p>
						<p><progress id="packageInstallationProgress" value="{@$progress}" max="100" style="width: 300px;" title="{@$progress}%">{@$progress}%</progress></p>
					</header>
