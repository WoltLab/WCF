{include file='header' pageTitle='wcf.acp.application.group.add'}

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.application.group.add{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.form.{$action}.success{/lang}</p>	
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='ApplicationManagement'}{/link}" class="button"><img src="{@RELATIVE_WCF_DIR}icon/list.svg" alt="" /> <span>{lang}wcf.acp.application.management{/lang}</span></a></li>
		</ul>
	</nav>
</div>

<div class="container containerPadding marginTop">
	<form method="post" action="{link controller='ApplicationGroupAdd'}{/link}">
		<fieldset>
			<legend>{lang}wcf.acp.application.group.data{/lang}</legend>
			<dl{if $errorField == 'groupName'} class="formError"{/if}>
				<dt><label for="groupName">{lang}wcf.acp.application.group.groupName{/lang}</label></dt>
				<dd>
					<input type="text" name="groupName" id="groupName" value="{$groupName}" class="long" />
					{if $errorField == 'groupName'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.application.group.groupName.error.{$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
		</fieldset>
		
		<fieldset{if $errorField == 'applications'} class="formError"{/if}>
			<legend>{lang}wcf.acp.application.group.availableApplications{/lang}</legend>
			
			<div class="tabularBox">
				<table class="table">
					<thead>
						<tr>
							<th class="columnID columnPackageID" colspan="2">{lang}wcf.global.objectID{/lang}</th>
							<th class="columnText columnPackageName">{lang}wcf.acp.package.name{/lang}</th>
							<th class="columnText columnDomainName">{lang}wcf.acp.application.domainName{/lang}</th>
							<th class="columnText columnDomainPath">{lang}wcf.acp.application.domainPath{/lang}</th>
							<th class="columnText columnCookieDomain">{lang}wcf.acp.application.cookieDomain{/lang}</th>
							<th class="columnText columnCookiePath">{lang}wcf.acp.application.cookiePath{/lang}</th>
							
							{event name='headColumns'}
						</tr>
					</thead>
					
					<tbody>
						{foreach from=$availableApplications item=application}
							<tr data-package="{$application->package}">
								<td class="columnIcon"><a href="{link controller='ApplicationEdit' id=$application->packageID}{/link}"><img src="{@RELATIVE_WCF_DIR}icon/edit.svg" alt="" class="icon16 jsTooltip" title="{lang}wcf.global.button.edit{/lang}" /></a></td>
								<td class="columnID columnPackageID">{#$application->packageID}</td>
								<td class="columnText columnPackageName"><a href="{link controller='PackageView' id=$application->packageID}{/link}">{lang}{$application->packageName}{/lang}</a></td>
								<td class="columnText columnDomainName">{$application->domainName}</td>
								<td class="columnText columnDomainPath">{$application->domainPath}</td>
								<td class="columnText columnCookieDomain">{$application->cookieDomain}</td>
								<td class="columnText columnCookiePath">{$application->cookiePath}</td>
								
								{event name='columns'}
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			
			{if $errorField == 'applications'}
				<small class="innerError">
					{if $errorType == 'empty'}
						{lang}wcf.global.form.error.empty{/lang}
					{else}
						{lang}wcf.acp.application.group.applications.error.{$errorType}{/lang}
					{/if}
				</small>
			{/if}
		</fieldset>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" />
		</div>
	</form>
</div>

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='ApplicationManagement'}{/link}" class="button"><img src="{@RELATIVE_WCF_DIR}icon/list.svg" alt="" /> <span>{lang}wcf.acp.application.management{/lang}</span></a></li>
		</ul>
	</nav>
</div>

{include file='footer'}
