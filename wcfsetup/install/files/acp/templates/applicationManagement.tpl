{include file='header' pageTitle='wcf.acp.application.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.application.list{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='shared_formNotice' action='edit'}

<form method="post" action="{link controller='ApplicationManagement'}{/link}">
	{if !ENABLE_ENTERPRISE_MODE || $__wcf->user->hasOwnerAccess()}
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.acp.application.management.domain{/lang}</h2>

			<dl{if $errorField == 'domainName'} class="formError"{/if}>
				<dt><label for="domainName">{lang}wcf.acp.application.management.domainName{/lang}</label></dt>
				<dd>
					<div class="inputAddon">
						<span class="inputPrefix">https://</span>
						<input type="text" name="domainName" id="domainName" value="{$domainName}" class="long">
					</div>
					{if $errorField == 'domainName'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.application.management.domainName.error.{$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.application.management.domainName.description{/lang}</small>
				</dd>
			</dl>

			<dl{if $errorField == 'cookieDomain'} class="formError"{/if}>
				<dt><label for="cookieDomain">{lang}wcf.acp.application.management.cookieDomain{/lang}</label></dt>
				<dd>
					<input type="text" name="cookieDomain" id="cookieDomain" value="{$cookieDomain}" class="long">
					{if $errorField == 'cookieDomain'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.application.management.cookieDomain.error.{$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.application.management.cookieDomain.description{/lang}</small>
				</dd>
			</dl>
		</section>

		{* Keep the cookie domain in sync if it was previously identical. *}
		{if $domainName === $cookieDomain}
			<script>
				(() => {
					const domainName = document.getElementById("domainName");
					const cookieDomain = document.getElementById("cookieDomain");

					domainName.addEventListener("input", () => {
						cookieDomain.value = domainName.value;
					});
				})();
			</script>
		{/if}
	{/if}

	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.application.landingPage{/lang}</h2>

		<div class="tabularBox">
			<table class="table">
				<thead>
					<tr>
						<th class="columnID columnPackageID">{lang}wcf.global.objectID{/lang}</th>
						<th class="columnText columnPackageName">{lang}wcf.acp.package.name{/lang}</th>
						<th class="columnText columnLandingPageID">{lang}wcf.acp.application.landingPage{/lang}</th>
						
						{event name='columnHeads'}
					</tr>
				</thead>
				
				<tbody>
					{foreach from=$applicationList item=application}
						<tr>
							<td class="columnID columnPackageID">{#$application->packageID}</td>
							<td class="columnTitle columnPackageName">
								<p><strong>{$application->getPackage()}</strong></p>
								<small>{$application->getPageURL()}</small>
							</td>
							<td class="columnText columnLandingPageID">
								<select name="landingPageID[{$application->packageID}]" required>
									<option value="">{lang}wcf.global.noSelection{/lang}</option>
									<option value="0"{if $application->getAbbreviation() === 'wcf'} disabled{elseif $application->landingPageID === null} selected{/if}>{lang}wcf.acp.application.landingPage.default{/lang}</option>
									
									{foreach from=$pageNodeList item=pageNode}
										{if !$pageNode->isDisabled && !$pageNode->requireObjectID && !$pageNode->excludeFromLandingPage}
											<option value="{$pageNode->pageID}"{if $pageNode->pageID == $application->landingPageID} selected{/if} data-identifier="{@$pageNode->identifier}">{if $pageNode->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($pageNode->getDepth() - 1)}{/if}{$pageNode->name}</option>
										{/if}
									{/foreach}
								</select>
							</td>
							
							{event name='columns'}
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</section>

	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

<footer class="contentFooter">
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}{event name='contentFooterNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}
