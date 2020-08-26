{include file='header' pageTitle='wcf.acp.devtools.project.sync.pageTitle'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.devtools.project.sync{/lang}</h1>
		<p class="contentHeaderDescription">{$object->name}</p>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $object->validate() === ''}
				<li><a href="{link controller='DevtoolsProjectPipList' id=$object->getObjectID()}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.devtools.project.pips{/lang}</span></a></li>
			{/if}
			<li><a href="{link controller='DevtoolsProjectEdit' id=$object->getObjectID()}{/link}" class="button"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.acp.devtools.project.edit{/lang}</span></a></li>
			<li><a href="{link controller='DevtoolsProjectList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.devtools.project.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $object->validate() === ''}
	<p class="info">{lang}wcf.acp.devtools.pip.notice{/lang}</p>
	
	<div class="section">
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" id="syncShowOnlyMatches" checked> {lang}wcf.acp.devtools.pip.showOnlyMatches{/lang}</label>
				<small>{lang}wcf.acp.devtools.pip.showOnlyMatches.description{/lang}</small>
			</dd>
		</dl>
	</div>
	<div class="section tabularBox jsShowOnlyMatches" id="syncPipMatches">
		<table class="table">
			<thead>
				<tr>
					<th class="columnText">{lang}wcf.acp.devtools.pip.pluginName{/lang}</th>
					<th class="columnText">{lang}wcf.acp.devtools.pip.defaultFilename{/lang}</th>
					<th class="columnIcon" colspan="2">{lang}wcf.acp.devtools.pip.target{/lang}</th>
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$object->getPips() item=pip}
					{assign var=_isSupported value=$pip->isSupported()}
					{assign var=_targets value=$pip->getTargets($object)}
					{assign var=_targetCount value=$_targets|count}
					
					<tr data-plugin-name="{$pip->pluginName}" data-is-supported="{if $_isSupported}true{else}false{/if}" {if $_targetCount} class="jsHasPipTargets" data-sync-dependencies="{$pip->getSyncDependencies(true)}"{/if}>
						<td class="columnText"{if $_targetCount > 0} rowspan="{$_targetCount}"{/if}>{$pip->pluginName}</td>
						{if $_isSupported}
							<td class="columnText pipDefaultFilename"{if $_targetCount > 0} rowspan="{$_targetCount}"{/if}><small>{$pip->getEffectiveDefaultFilename()}</small></td>
							{if $_targetCount}
								<td class="columnIcon"><button class="small jsInvokePip" data-target="{$_targets[0]}">{$_targets[0]}</button></td>
								<td class="columnText"><small class="jsInvokePipResult" data-target="{$_targets[0]}">{lang}wcf.acp.devtools.sync.status.idle{/lang}</small></td>
							{else}
								<td class="columnText" colspan="2">
									<small>{lang}wcf.acp.devtools.pip.target.noMatches{/lang}</small>
								</td>
							{/if}
						{else}
							<td class="columnText" colspan="3">{$pip->getFirstError()}</td>
						{/if}
					</tr>
					{if $_targetCount}
						{section name=i loop=$_targets start=1}
							<tr data-plugin-name="{$pip->pluginName}" {if $_targetCount} class="jsHasPipTargets jsSkipTargetDetection"{/if}>
								<td class="columnIcon"><button class="small jsInvokePip" data-target="{$_targets[$i]}">{$_targets[$i]}</button></td>
								<td class="columnText"><small class="jsInvokePipResult" data-target="{$_targets[$i]}">{lang}wcf.acp.devtools.sync.status.idle{/lang}</small></td>
							</tr>
						{/section}
					{/if}
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<script data-relocate="true">
		require(['Language', 'WoltLabSuite/Core/Acp/Ui/Devtools/Project/Sync'], function(Language, AcpUiDevtoolsProjectSync) {
			Language.addObject({
				'wcf.acp.devtools.sync.status.failure': '{jslang}wcf.acp.devtools.sync.status.failure{/jslang}',
				'wcf.acp.devtools.sync.syncAll': '{jslang}wcf.acp.devtools.sync.syncAll{/jslang}'
			});
			
			AcpUiDevtoolsProjectSync.init({$object->projectID});
		});
	</script>
	
	<style>
		#syncPipMatches.jsShowOnlyMatches tbody > tr:not(.jsHasPipTargets) {
			display: none;
		}
		
		#syncPipMatches > table {
			/*table-layout: fixed;*/
		}
		
		#syncPipMatches td:first-child {
			width: 300px;
		}
		
		#syncPipMatches td.pipDefaultFilename {
			width: 300px;
		}
		
		#syncPipMatches td:last-child {
			width: auto;
		}
		
		.syncStatusContainer {
			overflow: hidden;
		}
	</style>
{else}
	<p class="error">{@$object->validate()}</p>
{/if}

{include file='__devtoolsProjectInstallationJavaScript'}
{include file='footer'}
