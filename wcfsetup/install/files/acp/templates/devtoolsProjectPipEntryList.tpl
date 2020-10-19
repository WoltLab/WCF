{include file='header' pageTitle='wcf.acp.devtools.project.pip.entry.list.pageTitle'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.devtools.project.pip.entry.list{/lang}</h1>
		<p class="contentHeaderDescription">{$project->name}</p>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li class="dropdown">
				<a class="button dropdownToggle"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.devtools.project.pip.list{/lang}</span></a>
				<div class="dropdownMenu">
					<ul class="scrollableDropdownMenu">
						{foreach from=$project->getPips() item=otherPip}
							{if $otherPip->supportsGui()}
								{foreach from=$otherPip->getPip()->getEntryTypes() item=otherPipEntryType}
									<li{if $otherPip->pluginName === $pip && $otherPipEntryType === $entryType} class="active"{/if}><a href="{link controller='DevtoolsProjectPipEntryList' id=$project->projectID pip=$otherPip->pluginName entryType=$otherPipEntryType}{/link}">{$otherPip->pluginName} ({$otherPipEntryType})</a></li>
								{foreachelse}
									<li{if $otherPip->pluginName === $pip} class="active"{/if}><a href="{link controller='DevtoolsProjectPipEntryList' id=$project->projectID pip=$otherPip->pluginName}{/link}">{$otherPip->pluginName}</a></li>
								{/foreach}
							{/if}
						{/foreach}
					</ul>
				</div>
			</li>
			<li><a href="{link controller='DevtoolsProjectPipEntryAdd' id=$project->projectID pip=$pip entryType=$entryType}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.devtools.project.pip.entry.button.add{/lang}</span></a></li>
			<li><a href="{link controller='DevtoolsProjectList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.devtools.project.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<p class="info">{content}{lang}wcf.acp.pip.{@$pip}.info{/lang}{/content}</p>
{/hascontent}

<form method="post" action="{link controller='DevtoolsProjectPipEntryList' id=$project->projectID}{@$linkParameters}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
		
		<dl>
			<dt></dt>
			<dd>
				<input type="text" id="search" name="entryFilter" value="{$entryFilter}" placeholder="{lang}wcf.global.filter{/lang}" class="long">
			</dd>
		</dl>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{csrfToken}
		</div>
	</section>
</form>

{hascontent}
	<div class="paginationTop">
		{content}
			{pages print=true assign=pagesLinks controller="DevtoolsProjectPipEntryList" id=$project->projectID link="$linkParameters&pageNo=%d"}
		{/content}
	</div>
{/hascontent}

{if !$entryList->getEntries()|empty}
	<div class="section tabularBox jsShowOnlyMatches">
		<table class="table" id="devtoolsProjectPipEntryList">
			<thead>
				<tr>
					{foreach from=$entryList->getKeys() item=languageItem name=entryListKeys}
						<th{if $tpl[foreach][entryListKeys][first]} colspan="2"{/if}>{@$languageItem|language}</th>
					{/foreach}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$entryList->getEntries($startIndex-1, $itemsPerPage) key=identifier item=entry}
					<tr class="jsPipEntryRow" data-identifier="{@$identifier}">
						<td class="columnIcon">
							<a href="{link controller='DevtoolsProjectPipEntryEdit' id=$project->projectID pip=$pip identifier=$identifier entryType=$entryType}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}"></span>
						</td>
						{foreach from=$entryList->getKeys() key=key item=languageItem}
							<td>{$entry[$key]}</td>
						{/foreach}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
		<nav class="contentFooterNavigation">
			<ul>
				<li class="dropdown">
					<a class="button dropdownToggle"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.devtools.project.pip.list{/lang}</span></a>
					<div class="dropdownMenu">
						<ul class="scrollableDropdownMenu">
							{foreach from=$project->getPips() item=otherPip}
								{if $otherPip->supportsGui()}
									{foreach from=$otherPip->getPip()->getEntryTypes() item=otherPipEntryType}
										<li{if $otherPip->pluginName === $pip && $otherPipEntryType === $entryType} class="active"{/if}><a href="{link controller='DevtoolsProjectPipEntryList' id=$project->projectID pip=$otherPip->pluginName entryType=$otherPipEntryType}{/link}">{$otherPip->pluginName} ({$otherPipEntryType})</a></li>
									{foreachelse}
										<li{if $otherPip->pluginName === $pip} class="active"{/if}><a href="{link controller='DevtoolsProjectPipEntryList' id=$project->projectID pip=$otherPip->pluginName}{/link}">{$otherPip->pluginName}</a></li>
									{/foreach}
								{/if}
							{/foreach}
						</ul>
					</div>
				</li>
				<li><a href="{link controller='DevtoolsProjectPipEntryAdd' id=$project->projectID pip=$pip entryType=$entryType}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.devtools.project.pip.entry.button.add{/lang}</span></a></li>
				<li><a href="{link controller='DevtoolsProjectList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.devtools.project.list{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Acp/Ui/Devtools/Project/Pip/Entry/List'], function(Language, DevtoolsProjectPipEntryList) {
		Language.addObject({
			'wcf.acp.devtools.project.pip.entry.delete.addDeleteInstruction': '{jslang}wcf.acp.devtools.project.pip.entry.delete.addDeleteInstruction{/jslang}',
			'wcf.acp.devtools.project.pip.entry.delete.addDeleteInstruction.description': '{jslang}wcf.acp.devtools.project.pip.entry.delete.addDeleteInstruction.description{/jslang}',
			'wcf.acp.devtools.project.pip.entry.delete.confirmMessage': '{jslang}wcf.acp.devtools.project.pip.entry.delete.confirmMessage{/jslang}'
		});
		
		new DevtoolsProjectPipEntryList('devtoolsProjectPipEntryList', '{@$project->projectID}', '{@$pip}', '{@$entryType}', {if $pipObject->getPip()->supportsDeleteInstruction()}true{else}false{/if});
	});
</script>

{include file='footer'}
