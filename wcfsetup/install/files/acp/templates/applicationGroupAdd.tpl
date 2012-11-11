{include file='header' pageTitle='wcf.acp.application.group.'|concat:$action}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.ACP.Application.Group.Delete('{link controller='ApplicationManagement'}{/link}');
		
		WCF.Language.addObject({
			'wcf.acp.application.group.delete.success': '{lang}wcf.acp.application.group.delete.success{/lang}'
		});
	});
	//]]>
</script>

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.application.group.{$action}{/lang}</h1>
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
			{if $action == 'edit'}<li><a class="button jsDeleteApplicationGroup" data-confirm-message="{lang}wcf.acp.application.group.delete.confirmMessage{/lang}" data-group-id="{@$applicationGroup->groupID}"><img src="{@RELATIVE_WCF_DIR}icon/delete.svg" class="icon24" /> <span>{lang}wcf.acp.application.group.delete{/lang}</span></a></li>{/if}
			<li><a href="{link controller='ApplicationManagement'}{/link}" class="button"><img src="{@RELATIVE_WCF_DIR}icon/list.svg" alt="" /> <span>{lang}wcf.acp.application.management{/lang}</span></a></li>
		</ul>
	</nav>
</div>

{if $availableApplications|count > 1}
	<div class="container containerPadding marginTop">
		<form method="post" action="{if $action == 'add'}{link controller='ApplicationGroupAdd'}{/link}{else}{link controller='ApplicationGroupEdit' id=$applicationGroup->groupID}{/link}{/if}">
			<fieldset>
				<legend>{lang}wcf.acp.application.group.data{/lang}</legend>
				
				<dl{if $errorField == 'groupName'} class="formError"{/if}>
					<dt><label for="groupName">{lang}wcf.acp.application.group.groupName{/lang}</label></dt>
					<dd>
						<input type="text" name="groupName" id="groupName" value="{$groupName}" class="long" required="required" />
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
								<th class="columnID columnPackageID" colspan="3">{lang}wcf.global.objectID{/lang}</th>
								<th class="columnMark columnPrimaryApplication">{lang}wcf.acp.application.primaryApplication{/lang}</th>
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
									<td class="columnMark"><input type="checkbox" name="applications[]" value="{@$application->packageID}"{if $application->packageID|in_array:$applications} checked="checked"{/if} /></td>
									<td class="columnIcon"><a href="{link controller='ApplicationEdit' id=$application->packageID}{/link}"><img src="{@RELATIVE_WCF_DIR}icon/edit.svg" alt="" class="icon16 jsTooltip" title="{lang}wcf.global.button.edit{/lang}" /></a></td>
									<td class="columnID columnPackageID">{#$application->packageID}</td>
									<td class="columnMark columnPrimaryApplication"><input type="radio" name="primaryApplication" value="{@$application->packageID}" required="required"{if $primaryApplication == $application->packageID} checked="checked"{/if} /></td>
									<td class="columnText columnPackageName"><a href="{link controller='PackageView' id=$application->packageID}{/link}">{lang}{$application->getPackage()->getName()}{/lang}</a></td>
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
{else}
	<p class="error">{lang}wcf.acp.application.group.noAvailableApplications{/lang}</p>
{/if}

{include file='footer'}
