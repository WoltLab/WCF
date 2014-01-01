{include file='header' pageTitle='wcf.acp.application.list'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.application.list{/lang}</h1>
</header>

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<div class="tabularBox tabularBoxTitle marginTop">
	<header>
		<h2>{lang}wcf.acp.application.list{/lang} <span class="badge badgeInverse">{#$applicationList|count}</span></h2>
	</header>
	
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
					<td class="columnIcon"><a href="{link controller='ApplicationEdit' id=$application->packageID}{/link}" class="jsTooltip" title="{lang}wcf.global.button.edit{/lang}"><span class="icon icon16 icon-pencil"></span></a></td>
					<td class="columnID columnPackageID">{#$application->packageID}</td>
					<td class="columnText columnPackageName">
						<a href="{link controller='ApplicationEdit' id=$application->packageID}{/link}">{$application->getPackage()}</a>
						{if $application->isPrimary}
							<aside class="statusDisplay">
								<ul class="statusIcons">
									<li><span class="icon icon16 icon-ok-sign jsTooltip" title="{lang}wcf.acp.application.primaryApplication{/lang}"></span></li>
								</ul>
							</aside>
						{/if}
					</td>
					<td class="columnText columnDomainName">{$application->domainName}</td>
					<td class="columnText columnDomainPath">{$application->domainPath}</td>
					<td class="columnText columnCookieDomain">{$application->cookieDomain}</td>
					
					{event name='columns'}
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsBottom'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{include file='footer'}
