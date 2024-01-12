{include file='header' pageTitle='wcf.acp.cronjob.list'}

<script data-relocate="true">
	$(function() {
		new WCF.ACP.Cronjob.ExecutionHandler();
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.cronjob.list{/lang} <span class="badge badgeInverse">{#$items}</span></h1>
		<p class="contentHeaderDescription">{lang}wcf.acp.cronjob.subtitle{/lang}</p>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='CronjobAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.cronjob.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="CronjobList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{hascontent}
	<div class="section tabularBox">
		<table class="table jsObjectActionContainer" data-object-action-class-name="wcf\data\cronjob\CronjobAction">
			<thead>
				<tr>
					<th class="columnID columnCronjobID{if $sortField == 'cronjobID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=cronjobID&sortOrder={if $sortField == 'cronjobID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnExpression"><span>{lang}wcf.acp.cronjob.expression{/lang}</span></th>
					<th class="columnText columnDescription{if $sortField == 'descriptionI18n'} active {@$sortOrder}{/if}"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=descriptionI18n&sortOrder={if $sortField == 'descriptionI18n' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.description{/lang}</a></th>
					<th class="columnDate columnNextExec{if $sortField == 'nextExec'} active {@$sortOrder}{/if}"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=nextExec&sortOrder={if $sortField == 'nextExec' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.nextExec{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{content}
					{foreach from=$objects item=cronjob}
						<tr class="jsCronjobRow jsObjectActionObject" data-object-id="{@$cronjob->getObjectID()}">
							<td class="columnIcon">
								<button type="button" class="jsExecuteButton jsTooltip" title="{lang}wcf.acp.cronjob.execute{/lang}" data-object-id="{@$cronjob->cronjobID}">
									{icon name='play'}
								</button>
								
								{if $cronjob->canBeDisabled()}
									{objectAction action="toggle" isDisabled=$cronjob->isDisabled}
								{else}
									{if !$cronjob->isDisabled}
										<span class="disabled" title="{lang}wcf.global.button.disable{/lang}">
											{icon name='square-check'}
										</span>
									{else}
										<span class="disabled" title="{lang}wcf.global.button.enable{/lang}">
											{icon name='square'}
										</span>
									{/if}
								{/if}
								
								{if $cronjob->isEditable()}
									<a href="{link controller='CronjobEdit' id=$cronjob->cronjobID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip">{icon name='pencil'}</a>
								{else}
									<span class="disabled" title="{lang}wcf.global.button.edit{/lang}">
										{icon name='pencil'}
									</span>
								{/if}
								{if $cronjob->isDeletable()}
									{objectAction action="delete" objectTitle=$cronjob->getDescription()}
								{else}
									<span class="disabled" title="{lang}wcf.global.button.delete{/lang}">
										{icon name='xmark'}
									</span>
								{/if}
								
								{event name='rowButtons'}
							</td>
							<td class="columnID">{@$cronjob->cronjobID}</td>
							<td class="columnText columnExpression">
								<kbd>{$cronjob->getExpression()}</kbd>
							</td>
							<td class="columnText columnDescription">
								{if $cronjob->isEditable()}
									<a title="{lang}wcf.acp.cronjob.edit{/lang}" href="{link controller='CronjobEdit' id=$cronjob->cronjobID}{/link}">{$cronjob->getDescription()}</a>
								{else}
									{$cronjob->getDescription()}
								{/if}
							</td>
							<td class="columnDate columnNextExec">
								{if !$cronjob->isDisabled && $cronjob->nextExec != 1}
									{@$cronjob->nextExec|plainTime}
								{/if}
							</td>
							
							{event name='columns'}
						</tr>
					{/foreach}
				{/content}
			</tbody>
		</table>
	</div>
{hascontentelse}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/hascontent}

<footer class="contentFooter">
	{hascontent}
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}
	
	<nav class="contentFooterNavigation">
		<ul>
			<li><a href="{link controller='CronjobAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.cronjob.add{/lang}</span></a></li>
			
			{event name='contentFooterNavigation'}
		</ul>
	</nav>
</footer>

{include file='footer'}
