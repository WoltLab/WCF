<!DOCTYPE html>
<html dir="{@$__wcf->getLanguage()->getPageDirection()}" lang="{@$__wcf->getLanguage()->getFixedLanguageCode()}">
<head>
	<meta charset="utf-8" />
	<title>{lang}wcf.global.progressBar{/lang} - {lang}wcf.global.pageTitle{/lang}</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="{if $lastStep|isset}{@RELATIVE_WCF_DIR}acp/style/{else}install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&amp;showCSS={/if}wcf.css" />
	
	{if !$lastStep|isset}
		<style type="text/css">
			/*<![CDATA[*/
			.wcf-error {
				background-image: url('install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&showIcon=systemError.svg') !important;
			}
		
			.wcf-innerError {
				background-image: url('install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&showIcon=systemError.svg') !important;
			}
			/*]]>*/
		</style>
	{/if}
</head>

<body id="tplWCFInstaller">
	<a id="top"></a>
	<!-- HEADER -->
	<header id="pageHeader" class="wcf-pageHeader">
		<div>
			<!-- no top menu -->
			
			<!-- logo -->
			<div id="logo" class="wcf-logo">
				<div><!-- ToDo: This is just a little trick to compensate the missing link here, find a better solution -->
					<h1>{lang}wcf.global.pageTitle{/lang}</h1>
					<img src="{if $lastStep|isset}{@RELATIVE_WCF_DIR}acp/images/{else}install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&amp;showImage={/if}wcfLogo2.svg" width="300" height="58" alt="" />
				</div>
				
				<!-- no search area -->
			</div>
			<!-- /logo -->
			
			<!-- no main menu -->
			
			<!-- header navigation -->
			<nav class="wcf-headerNavigation">
				<ul>
					<li id="toBottomLink" class="toBottomLink"><a href="#bottom" title="{lang}wcf.global.scrollDown{/lang}" class="jsTooltip"><img src="{if $lastStep|isset}{@RELATIVE_WCF_DIR}icon/{else}install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&amp;showIcon={/if}toBottom.svg" alt="" /> <span class="invisible">{lang}wcf.global.scrollDown{/lang}</span></a></li>
				</ul>
			</nav>
			<!-- /header navigation -->
		</div>
	</header>
	<!-- /HEADER -->
	
	<!-- MAIN -->
	<div id="main" class="wcf-main">
		<div>
			
			<!-- CONTENT -->
			<section id="content" class="wcf-content">
				
				<header class="wcf-container wcf-mainHeading setup">
					<img src="{if $lastStep|isset}{@RELATIVE_WCF_DIR}icon/{else}install.php?tmpFilePrefix={@TMP_FILE_PREFIX}&amp;showIcon={/if}working1.svg" alt="" class="wcf-containerIcon" />
					<hgroup class="wcf-containerContent">
						<h1>{lang}wcf.global.title{/lang}</h1>
						<h2>{lang}wcf.global.title.subtitle{/lang}</h2>
						<p><progress id="packageInstallationProgress" value="{@$progress}" max="100" style="width: 300px;" title="{@$progress}%">{@$progress}%</progress></p>
					</hgroup>
				</header>
				
