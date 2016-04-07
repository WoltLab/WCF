{include file='header' pageTitle='wcf.acp.dashboard.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.dashboard.list{/lang}</h1>
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
				<th colspan="2" class="columnID">{lang}wcf.global.objectID{/lang}</th>
				<th class="columnText">{lang}wcf.dashboard.objectType{/lang}</th>
				
				{event name='columnHeads'}
			</tr>
		</thead>
		
		<tbody>
			{foreach from=$objectTypes item=$objectType}
				<tr>
					<td class="columnIcon">
						<a href="{link controller='DashboardOption' id=$objectType->objectTypeID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
						
						{event name='rowButtons'}
					</td>
					<td class="columnID">{#$objectType->objectTypeID}</td>
					<td class="columnText"><a href="{link controller='DashboardOption' id=$objectType->objectTypeID}{/link}">{lang}wcf.dashboard.objectType.{$objectType->objectType}{/lang}</a></td>
					
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