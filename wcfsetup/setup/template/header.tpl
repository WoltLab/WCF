<!DOCTYPE html>
<html dir="{@$__wcf->getLanguage()->getPageDirection()}" lang="{@$__wcf->getLanguage()->getFixedLanguageCode()}">
<head>
	<meta charset="utf-8" />
	<title>{lang}wcf.global.progressBar{/lang} - {lang}wcf.global.title{/lang}</title>
	
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

<body>
	<a id="top"></a>
	
	<div id="pageContainer" class="pageContainer">
		<div id="pageHeaderContainer" class="pageHeaderContainer">
			<header id="pageHeader" class="pageHeader">
				<div>
					<div class="layoutBoundary">
						<div id="pageHeaderLogo" class="pageHeaderLogo">
							<img src="{if $lastStep|isset}{@RELATIVE_WCF_DIR}acp/images/{else}install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&amp;showImage={/if}wcfLogo.png" alt="" style="height: 40px; width: 288px;" />
						</div>
					</div>
				</div>
			</header>
		</div>
		
		<section id="main" class="main" role="main">
			<div class="layoutBoundary">
				<div id="content" class="content">
					<header class="contentHeader">
						<div class="contentHeaderTitle">
							<h1 class="contentTitle">{lang}wcf.global.title{/lang}</h1>
							<p class="contentHeaderDescription"><progress id="packageInstallationProgress" value="{@$progress}" max="100" style="width: 300px;" title="{@$progress}%">{@$progress}%</progress></p>
						</div>
					</header>
