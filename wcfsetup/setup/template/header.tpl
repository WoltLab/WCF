<!DOCTYPE html>
<html dir="{@$__wcf->getLanguage()->getPageDirection()}" lang="{@$__wcf->getLanguage()->getFixedLanguageCode()}">
<head>
	<meta charset="utf-8" />
	<title>{lang}wcf.global.progressBar{/lang} - {lang}wcf.global.pageTitle{/lang}</title>
	
	<link rel="stylesheet" type="text/css" media="screen" href="{if $lastStep|isset}{@RELATIVE_WCF_DIR}acp/style/setup/{else}install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&amp;showCSS={/if}WCFSetup.css" />
	
	{if !$lastStep|isset}
		<style type="text/css">
			/*<![CDATA[*/
				.info:after {
					background-image: url('install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&showIcon=infoInverse.svg') !important;					
				}
				
				.error:after {
					background-image: url('install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&showIcon=errorInverse.svg') !important;	
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
					<img src="{if $lastStep|isset}{@RELATIVE_WCF_DIR}acp/images/{else}install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&amp;showImage={/if}wcfLogo2.svg" width="300" height="58" alt="" />
				</a>
			</div>
			
			<nav class="navigation navigationHeader clearfix">
				<ul class="navigationIcons">
					<li id="toBottomLink" class="toBottomLink"><a href="{@$__wcf->getAnchor('bottom')}" title="{lang}wcf.global.scrollDown{/lang}" class="jsTooltip"><img src="{if $lastStep|isset}{@RELATIVE_WCF_DIR}icon/{else}install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&amp;showIcon={/if}circleArrowDownColored.svg" alt="" class="icon16" /> <span class="invisible">{lang}wcf.global.scrollDown{/lang}</span></a></li>
				</ul>
			</nav>
		</div>
	</header>
	
	<div id="main" class="layoutFluid">
		<div>
			<section id="content" class="content clearfix">
				<header class="boxHeadline">
					<hgroup>
						<h1>{lang}wcf.global.title{/lang}</h1>
						<h2>{lang}wcf.global.title.subtitle{/lang}</h2>
						<p><progress id="packageInstallationProgress" value="{@$progress}" max="100" style="width: 300px;" title="{@$progress}%">{@$progress}%</progress></p>
					</hgroup>
				</header>
				
