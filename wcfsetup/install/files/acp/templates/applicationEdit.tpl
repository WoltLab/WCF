{include file='header' pageTitle='wcf.acp.application.edit'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.application.edit.title{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='ApplicationManagement'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.application.management{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

<form method="post" action="{link controller='ApplicationEdit' id=$application->packageID}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.application.domain{/lang}</h2>
		
		<dl{if $errorField == 'domainName'} class="formError"{/if}>
			<dt><label for="domainName">{lang}wcf.acp.application.domainName{/lang}</label></dt>
			<dd>
				<input type="text" name="domainName" id="domainName" value="{$domainName}" class="long">
				{if $errorField == 'domainName'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.application.domainName.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
				<small>{lang}wcf.acp.application.domainName.description{/lang}</small>
			</dd>
		</dl>
		
		<dl{if $errorField == 'domainPath'} class="formError"{/if}>
			<dt><label for="domainPath">{lang}wcf.acp.application.domainPath{/lang}</label></dt>
			<dd>
				<input type="text" name="domainPath" id="domainPath" value="{$domainPath}" class="long">
				<small>{lang}wcf.acp.application.domainPath.description{/lang}</small>
				{if $errorField == 'domainPath'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.application.domainPath.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		{event name='domainFields'}
	</section>
	
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.application.cookie{/lang}</h2>
			<small class="sectionDescription">{lang}wcf.acp.application.cookie.warning{/lang}</small>
		</header>
		
		<dl{if $errorField == 'cookieDomain'} class="formError{/if}">
			<dt><label for="cookieDomain">{lang}wcf.acp.application.cookieDomain{/lang}</label></dt>
			<dd>
				<input type="text" name="cookieDomain" id="cookieDomain" value="{$cookieDomain}" class="long">
				{if $errorField == 'cookieDomain'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.application.cookieDomain.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		{event name='cookieFields'}
	</section>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
