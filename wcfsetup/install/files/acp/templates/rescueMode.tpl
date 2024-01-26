<!DOCTYPE html>
<html dir="{@$__wcf->getLanguage()->getPageDirection()}" lang="{@$__wcf->getLanguage()->getFixedLanguageCode()}">
<head>
	<meta charset="utf-8">
	<meta name="robots" content="noindex">
	<title>{lang}wcf.acp.rescueMode{/lang} - {lang}wcf.global.acp{/lang}{if PACKAGE_ID} - {PAGE_TITLE|phrase}{/if}</title>
	
	<link rel="stylesheet" href="{$assets['WCFSetup.css']}">
	<style>
		.content {
			margin: 0 auto;
			max-width: 800px;
		}
	</style>
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}" class="wcfAcp">
<a id="top"></a>

<div id="pageContainer" class="pageContainer acpPageHiddenMenu">
	<div class="pageHeaderContainer">
		<header id="pageHeaderFacade" class="pageHeaderFacade">
			<div class="layoutBoundary">
				<div id="pageHeaderLogo" class="pageHeaderLogo">
					<a href="{$pageURL}">
						<img src="{$assets['woltlabSuite.png']}" alt="" class="pageHeaderLogoLarge" style="width: 281px;height: 40px;display: inline !important;">
					</a>
				</div>
			</div>
		</header>
	</div>
	
	<div id="acpPageContentContainer" class="acpPageContentContainer">
		<section id="main" class="main" role="main">
			<div class="layoutBoundary">
				<div id="content" class="content">

{* content above was taken from 'header.tpl' *}
				
<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.rescueMode{/lang}</h1>
</header>

<p class="info">{lang}wcf.acp.rescueMode.description{/lang}</p>

					{include file='shared_formError'}

<form method="post" action="{$pageURL}">
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.rescueMode.credentials{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.acp.rescueMode.credentials.description{/lang}</p>
		</header>
		
		<dl{if $errorField == 'username'} class="formError"{/if}>
			<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
			<dd>
				<input type="text" id="username" name="username" value="{$username}" class="long" required>
				{if $errorField == 'username'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'notAuthorized'}
							{lang}wcf.acp.rescueMode.username.notAuthorized{/lang}
						{else}
							{lang}wcf.user.username.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'password'} class="formError"{/if}>
			<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
			<dd>
				<input type="password" id="password" name="password" value="" class="long" required>
				{if $errorField == 'password'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.user.password.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
	</section>
	
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.rescueMode.domain{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.acp.rescueMode.domain.description{/lang}</p>
		</header>

		<dl{if $errorField === 'domainName'} class="formError"{/if}>
			<dt><label for="domainName">{lang}wcf.acp.application.domainName{/lang}</label></dt>
			<dd>
				<div class="inputAddon">
					<span class="inputPrefix">{$protocol}</span>
					<input type="text" name="domainName" id="domainName" value="{$domainName}" class="long" required>
				</div>
				{if $errorField === 'domainName'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.application.domainName.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
	</section>
	
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.rescueMode.application{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.acp.rescueMode.application.description{/lang}</p>
		</header>

		{foreach from=$applications item=application}
			{capture assign=applicationSectionPath}application_{@$application->packageID}{/capture}
			
			<dl{if $errorField == $applicationSectionPath} class="formError"{/if}>
				<dt><label for="application{@$application->packageID}">{$application->getPackage()}</label></dt>
				<dd>
					<div class="inputAddon">
						<span class="inputPrefix">{lang}wcf.acp.application.domainPath{/lang}</span>
						<input type="text" name="applicationValues[{@$application->packageID}]" value="{$applicationValues[$application->packageID]}" class="long" required>
					</div>
					{if $errorField == $applicationSectionPath}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.application.domainPath.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
		{/foreach}
	</section>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		
		{* do not use the security token here because we cannot rely on working cookies *}
	</div>
</form>

<script>
(() => {
	// Remove anything but the hostname and port when pasting URLs into the domain input.
	const domainName = document.getElementById("domainName");
	domainName.addEventListener("paste", (event) => {
		const value = event.clipboardData.getData("text/plain");
		try {
			const url = new URL(value.trim());
			let newValue = url.hostname;
			if (url.port) {
				newValue += `:${ url.port }`;
			}

			domainName.value = newValue;
			event.preventDefault();
		} catch (e) {
			// Not an URL.
		}
	});
})();
</script>

{include file='footer'}
