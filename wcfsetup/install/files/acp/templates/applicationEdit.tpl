{include file='header' pageTitle='wcf.acp.application.edit'}

{if $application->packageID != 1 && !$application->isPrimary}
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			WCF.Language.addObject({
				'wcf.acp.application.primaryApplication': '{lang}wcf.acp.application.primaryApplication{/lang}',
				'wcf.acp.application.setAsPrimary.confirmMessage': '{lang}wcf.acp.application.setAsPrimary.confirmMessage{/lang}'
			});
			
			new WCF.ACP.Application.SetAsPrimary({@$application->packageID});
		});
		//]]>
	</script>
{/if}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.application.edit.title{/lang}{if $application->isPrimary} <span class="icon icon16 icon-ok-sign jsTooltip" title="{lang}wcf.acp.application.primaryApplication{/lang}"></span>{/if}</h1>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			{if $application->packageID != 1 && !$application->isPrimary}
				<li><a id="setAsPrimary" class="button"><span class="icon icon16 icon-ok-sign"></span> <span>{lang}wcf.acp.application.setAsPrimary{/lang}</span></a></li>
			{/if}
			<li><a href="{link controller='ApplicationManagement'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.application.management{/lang}</span></a></li>
			
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{link controller='ApplicationEdit' id=$application->packageID}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.acp.application.domain{/lang}</legend>
			
			<dl{if $errorField == 'domainName'} class="formError"{/if}>
				<dt><label for="domainName">{lang}wcf.acp.application.domainName{/lang}</label></dt>
				<dd>
					<input type="text" name="domainName" id="domainName" value="{$domainName}" class="long" />
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
					<input type="text" name="domainPath" id="domainPath" value="{$domainPath}" class="long" />
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
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.acp.application.cookie{/lang}</legend>
			
			<small>{lang}wcf.acp.application.cookie.warning{/lang}</small>
			
			<dl class="marginTop {if $errorField == 'cookieDomain'} formError{/if}">
				<dt><label for="cookieDomain">{lang}wcf.acp.application.cookieDomain{/lang}</label></dt>
				<dd>
					<input type="text" name="cookieDomain" id="cookieDomain" value="{$cookieDomain}" class="long" />
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
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
