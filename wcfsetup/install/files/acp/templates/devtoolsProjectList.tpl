{include file='header' pageTitle='wcf.acp.devtools.project.list'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Acp/Ui/Devtools/Project/QuickSetup', 'Language'], function(AcpUiDevtoolsProjectQuickSetup, Language) {
		Language.add('wcf.acp.devtools.project.quickSetup', '{jslang}wcf.acp.devtools.project.quickSetup{/jslang}');
		
		AcpUiDevtoolsProjectQuickSetup.init();
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.devtools.project.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="#" class="button jsDevtoolsProjectQuickSetupButton"><span class="icon icon16 fa-search"></span> <span>{lang}wcf.acp.devtools.project.quickSetup{/lang}</span></a></li>
			<li><a href="{link controller='DevtoolsProjectAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.devtools.project.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<p class="info">{lang}wcf.acp.devtools.project.introduction{/lang}</p>

{hascontent}
	<div class="section tabularBox">
		<table class="table jsObjectActionContainer" data-object-action-class-name="wcf\data\devtools\project\DevtoolsProjectAction">
			<thead>
				<tr>
					<th class="columnID{if $sortField === 'projectID'} active {@$sortOrder}{/if}" colspan="3"><a href="{link controller='DevtoolsProjectList'}sortField=projectID&sortOrder={if $sortField === 'projectID' && $sortOrder === 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText{if $sortField === 'name'} active {@$sortOrder}{/if}"><a href="{link controller='DevtoolsProjectList'}sortField=name&sortOrder={if $sortField === 'name' && $sortOrder === 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.devtools.project.name{/lang}</a></th>
					<th class="columnText{if $sortField === 'path'} active {@$sortOrder}{/if}"><a href="{link controller='DevtoolsProjectList'}sortField=path&sortOrder={if $sortField === 'path' && $sortOrder === 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.devtools.project.path{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$objects item=object}
						<tr class="jsObjectRow jsObjectActionObject" data-object-id="{@$object->getObjectID()}">
							<td class="columnIcon">
								<a href="{link controller='DevtoolsProjectSync' id=$object->getObjectID()}{/link}" class="button small">{lang}wcf.acp.devtools.project.sync{/lang}</a>
								<a href="{link controller='DevtoolsProjectPipList' id=$object->getObjectID()}{/link}" class="button small">{lang}wcf.acp.devtools.project.pips{/lang}</a>
							</td>
							<td class="columnIcon">
								<a href="{link controller='DevtoolsProjectEdit' id=$object->getObjectID()}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
								{objectAction action="delete" objectTitle=$object->name}
							</td>
							<td class="columnID">{@$object->getObjectID()}</td>
							<td class="columnText"><a href="{link controller='DevtoolsProjectEdit' id=$object->getObjectID()}{/link}">{$object->name}</a></td>
							<td class="columnText"><small>{$object->path}</small></td>
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
	<nav class="contentFooterNavigation">
		<ul>
			<li><a href="{link controller='DevtoolsProjectAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.devtools.project.add{/lang}</span></a></li>
			
			{event name='contentFooterNavigation'}
		</ul>
	</nav>
</footer>

<div id="projectQuickSetup" style="display: none;">
	<dl>
		<dt>{lang}wcf.acp.devtools.project.quickSetup.path{/lang}</dt>
		<dd>
			<input type="text" name="projectQuickSetupPath" id="projectQuickSetupPath" class="long" />
			<small>{lang}wcf.acp.devtools.project.quickSetup.path.description{/lang}</small>
		</dd>
	</dl>
	
	<div class="formSubmit">
		<button id="projectQuickSetupSubmit" class="buttonPrimary">{lang}wcf.global.button.submit{/lang}</button>
	</div>
</div>

{include file='footer'}
