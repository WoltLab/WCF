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

<div class="section tabularBox">
	<table class="table">
		<thead>
			<tr>
				<th class="columnID columnPackageID" colspan="2">{lang}wcf.global.objectID{/lang}</th>
				<th class="columnText columnPackageName">{lang}wcf.acp.package.name{/lang}</th>
				<th class="columnText columnDomainName">{lang}wcf.acp.application.domainName{/lang}</th>
				<th class="columnText columnDomainPath">{lang}wcf.acp.application.domainPath{/lang}</th>
				<th class="columnText columnCookieDomain">{lang}wcf.acp.application.cookieDomain{/lang}</th>
				
				{event name='columnHeads'}
			</tr>
		</thead>
		
		<tbody>
			{foreach from=$applicationList item=application}
				<tr>
					<td class="columnIcon"><a href="{link controller='ApplicationEdit' id=$application->packageID}{/link}" class="jsTooltip" title="{lang}wcf.global.button.edit{/lang}"><span class="icon icon16 fa-pencil"></span></a></td>
					<td class="columnID columnPackageID">{#$application->packageID}</td>
					<td class="columnText columnPackageName"><a href="{link controller='ApplicationEdit' id=$application->packageID}{/link}">{$application->getPackage()}</a></td>
					<td class="columnText columnDomainName">{$application->domainName}</td>
					<td class="columnText columnDomainPath">{$application->domainPath}</td>
					<td class="columnText columnCookieDomain">{$application->cookieDomain}</td>
					
					{event name='columns'}
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>

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
