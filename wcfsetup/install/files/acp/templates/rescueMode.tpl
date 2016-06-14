<!DOCTYPE html>
<html dir="{@$__wcf->getLanguage()->getPageDirection()}" lang="{@$__wcf->getLanguage()->getFixedLanguageCode()}">
<head>
	<meta charset="utf-8">
	<meta name="robots" content="noindex">
	<title>{lang}wcf.acp.rescueMode{/lang} - {lang}wcf.global.acp{/lang}{if PACKAGE_ID} - {PAGE_TITLE|language}{/if}</title>
	
	<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600">
	<link rel="stylesheet" href="{$pageURL}&amp;proxy=css">
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}" class="wcfAcp">
<a id="top"></a>

<div id="pageContainer" class="pageContainer">
	<div class="pageHeaderContainer">
		<header id="pageHeader" class="pageHeader">
			<div>
				<div class="layoutBoundary">
					<div id="logo" class="logo">
						<a href="{$pageURL}">
							<img src="{$pageURL}&amp;proxy=logo" alt="" class="large">
						</a>
					</div>
				</div>
			</div>
		</header>
	</div>
	
	<section id="main" class="main" role="main">
		<div class="layoutBoundary">
			<div id="content" class="content">

{* content above was taken from 'header.tpl' *}
				
<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.rescueMode{/lang}</h1>
</header>

<p class="info">{lang}wcf.acp.rescueMode.description{/lang}</p>

{include file='formError'}

<form method="post" action="{$pageURL}">
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.rescueMode.credentials{/lang}</h2>
			<small class="sectionDescription">{lang}wcf.acp.rescueMode.credentials.description{/lang}</small>
		</header>
		
		<dl{if $errorField == 'username'} class="formError"{/if}>
			<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
			<dd>
				<input type="text" id="username" name="username" value="{$username}" class="long">
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
				<input type="password" id="password" name="password" value="" class="long">
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
	
	{include file='captcha'}
	
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.rescueMode.application{/lang}</h2>
			<small class="sectionDescription">{lang}wcf.acp.rescueMode.application.description{/lang}</small>
		</header>
		
		{foreach from=$applications item=application}
			{capture assign=applicationSectionDomain}application_{@$application->packageID}_domainName{/capture}
			{capture assign=applicationSectionPath}application_{@$application->packageID}_domainPath{/capture}
			
			<dl{if $errorField == $applicationSectionDomain || $errorField == $applicationSectionPath} class="formError"{/if}>
				<dt><label for="application{@$application->packageID}">{$application->getPackage()}</label></dt>
				<dd>
					<div class="inputAddon">
						<span class="inputPrefix">{lang}wcf.acp.application.domainName{/lang}</span>
						<input type="text" name="applicationValues[{@$application->packageID}][domainName]" id="application{@$application->packageID}" value="{$applicationValues[$application->packageID][domainName]}" class="long">
					</div>
					{if $errorField == $applicationSectionDomain}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.application.domainName.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
				<dd>
					<div class="inputAddon">
						<span class="inputPrefix">{lang}wcf.acp.application.domainPath{/lang}</span>
						<input type="text" name="applicationValues[{@$application->packageID}][domainPath]" value="{$applicationValues[$application->packageID][domainPath]}" class="long">
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

{include file='footer'}
