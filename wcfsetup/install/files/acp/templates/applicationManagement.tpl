{include file='header' pageTitle='wcf.acp.application.management'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.ACP.Application.Group.Delete();
		
		WCF.Language.addObject({
			'wcf.acp.application.group.delete.success': '{lang}wcf.acp.application.group.delete.success{/lang}'
		});
	});
	//]]>
</script>

{foreach from=$applicationGroups item=applicationGroup}
	<header class="boxHeadline">
		<hgroup>
			<h1>{lang}wcf.acp.application.group.title{/lang} <span class="badge">{#$applicationGroup|count}</span></h1>
		</hgroup>
	</header>
	
	<div class="tabularBox marginTop">
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
				{foreach from=$applicationGroup item=application}
					<tr>
						<td class="columnIcon"><a href="{link controller='ApplicationEdit' id=$application->packageID}{/link}"><img src="{@RELATIVE_WCF_DIR}icon/edit.svg" alt="" class="icon16 jsTooltip" title="{lang}wcf.global.button.edit{/lang}" /></a></td>
						<td class="columnID columnPackageID">{#$application->packageID}</td>
						<td class="columnText columnPackageName">
							<a href="{link controller='PackageView' id=$application->packageID}{/link}">{lang}{$application->getPackage()->getName()}{/lang}</a>
							
							{if $application->isPrimary}
								<aside class="statusDisplay">
									<ul class="statusIcons">
										<li><img src="{@RELATIVE_WCF_DIR}icon/home.svg" alt="" class="icon16 jsTooltip" title="{lang}{lang}wcf.acp.application.primaryApplication{/lang}{/lang}" /></li>
									</ul>
								</aside>
							{/if}
						</td>
						<td class="columnText columnDomainName">{$application->domainName}</td>
						<td class="columnText columnDomainPath">{$application->domainPath}</td>
						<td class="columnText columnCookieDomain">{$application->cookieDomain}</td>
						<td class="columnText columnCookiePath">{$application->cookiePath}</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<div class="contentNavigation">
		<nav>
			<ul>
				<li><a class="button jsDeleteApplicationGroup" data-confirm-message="{lang}wcf.acp.application.group.delete.confirmMessage{/lang}" data-group-id="{@$applicationGroup->groupID}"><img src="{@RELATIVE_WCF_DIR}icon/delete.svg" class="icon24" /> <span>{lang}wcf.acp.application.group.delete{/lang}</span></a></li>
				<li><a href="{link controller='ApplicationGroupEdit' id=$applicationGroup->groupID}{/link}" class="button"><img src="{@RELATIVE_WCF_DIR}icon/edit.svg" class="icon24" /> <span>{lang}wcf.acp.application.group.edit{/lang}</span></a></li>
			</ul>
		</nav>
	</div>
{/foreach}

{hascontent}
	<header class="boxHeadline">
		<hgroup>
			<h1>{lang}wcf.acp.application.independentApplications{/lang} <span class="badge">{#$applications|count}</span></h1>
		</hgroup>
	</header>
	
	<div class="tabularBox marginTop">
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
				{content}
					{foreach from=$applications item=application}
						<tr>
							<td class="columnIcon"><a href="{link controller='ApplicationEdit' id=$application->packageID}{/link}"><img src="{@RELATIVE_WCF_DIR}icon/edit.svg" alt="" class="icon16 jsTooltip" title="{lang}wcf.global.button.edit{/lang}" /></a></td>
							<td class="columnID columnPackageID">{#$application->packageID}</td>
							<td class="columnText columnPackageName"><a href="{link controller='PackageView' id=$application->packageID}{/link}">{lang}{$application->getPackage()->getName()}{/lang}</a></td>
							<td class="columnText columnDomainName">{$application->domainName}</td>
							<td class="columnText columnDomainPath">{$application->domainPath}</td>
							<td class="columnText columnCookieDomain">{$application->cookieDomain}</td>
							<td class="columnText columnCookiePath">{$application->cookiePath}</td>
						</tr>
					{/foreach}
				{/content}
			</tbody>
		</table>
	</div>
{/hascontent}

{hascontent}
	<div class="contentNavigation">
		<nav>
			<ul>
				{content}
					{if $applications|count > 1}
						<li><a href="{link controller='ApplicationGroupAdd'}{/link}" class="button"><img src="{@RELATIVE_WCF_DIR}icon/add.svg" class="icon24" /> <span>{lang}wcf.acp.application.group.add{/lang}</span></a></li>
					{/if}
					
					{* todo: event *}
				{/content}
			</ul>
		</nav>
	</div>
{/hascontent}

{include file='footer'}
