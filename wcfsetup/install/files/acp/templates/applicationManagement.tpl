{include file='header' pageTitle='wcf.acp.application.management'}

<div class="contentNavigation">
	{* todo: event *}
</div>

{hascontent}
	<header class="boxHeadline">
		<hgroup>
			<h1>{lang}wcf.acp.application.list{/lang} <span class="badge">{#$applicationList|count}</span></h1>
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
					{foreach from=$applicationList item=application}
						<tr>
							<td class="columnIcon"><a href="{link controller='ApplicationEdit' id=$application->packageID}{/link}"><img src="{@$__wcf->getPath()}icon/edit.svg" alt="" class="icon16 jsTooltip" title="{lang}wcf.global.button.edit{/lang}" /></a></td>
							<td class="columnID columnPackageID">{#$application->packageID}</td>
							<td class="columnText columnPackageName">
								<a href="{link controller='ApplicationEdit' id=$application->packageID}{/link}">{$application->getPackage()}</a>
								{if $application->isPrimary}
									<aside class="statusDisplay">
										<img src="{@$__wcf->getPath()}icon/home.svg" alt="" class="icon16 jsTooltip" title="{lang}wcf.acp.application.primaryApplication{/lang}" />
									</aside>
								{/if}
							</td>
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

<div class="contentNavigation">
	{* todo: event *}
</div>

{include file='footer'}
