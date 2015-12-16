{include file='header' pageTitle='wcf.acp.page.landing'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.page.landing{/lang}</h1>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='PageList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.cms.page.list{/lang}</span></a></li>
			
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{link controller='PageLanding'}{/link}">
	<div class="tabularBox marginTop">
		<table class="table">
			<thead>
				<tr>
					<th class="columnText">{lang}wcf.acp.page.landing.application{/lang}</th>
					<th class="columnURL">{lang}wcf.acp.page.landing.pageURL{/lang}</th>
					<th class="columnText">{lang}wcf.acp.page.landing.landingPage{/lang}</th>
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$applications item=application}
					<tr>
						<td class="columnText">{$application->getPackage()}</td>
						<td class="columnURL">{$application->getPageURL()}</td>
						<td class="columnText">
							<select name="landingPages[{@$application->packageID}]">
								<option value="-1">{lang}wcf.acp.page.landing.applicationDefault{/lang}</option>
								{foreach from=$pageNodeList item=pageNode}
									<option value="{@$pageNode->getPage()->pageID}"{if $pageNode->getPage()->pageID == $application->landingPageID} selected{/if}>{if $pageNode->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($pageNode->getDepth() - 1)}{/if}{$pageNode->getPage()}</option>
								{/foreach}
							</select>
						</td>
					</tr>
				{/foreach}	
			</tbody>
		</table>
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
