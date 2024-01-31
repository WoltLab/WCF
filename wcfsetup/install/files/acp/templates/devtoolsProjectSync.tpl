{include file='header' pageTitle='wcf.acp.devtools.project.sync.pageTitle'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.devtools.project.sync{/lang}</h1>
		<p class="contentHeaderDescription">{$object->name}</p>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $object->validate() === ''}
				<li>
					<button class="button" id="devtoolsSyncAll">
						{icon name='arrows-rotate' type='solid'}
						{lang}wcf.acp.devtools.sync.syncAll{/lang}
					</button>
				</li>
				<li><a href="{link controller='DevtoolsProjectPipList' id=$object->getObjectID()}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.devtools.project.pips{/lang}</span></a></li>
			{/if}
			<li><a href="{link controller='DevtoolsProjectEdit' id=$object->getObjectID()}{/link}" class="button">{icon name='pencil'} <span>{lang}wcf.acp.devtools.project.edit{/lang}</span></a></li>
			<li><a href="{link controller='DevtoolsProjectList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.devtools.project.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formError'}

{if $object->validate() === ''}
	<woltlab-core-notice type="info">{lang}wcf.acp.devtools.pip.notice{/lang}</woltlab-core-notice>
	
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
					<th class="columnIcon" colspan="2">{lang}wcf.acp.devtools.pip.target{/lang}</th>
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$object->getPips() item=pip}
					{assign var=_isSupported value=$pip->isSupported()}
					{assign var=_targets value=$pip->getTargets($object)}
					{assign var=_targetCount value=$_targets|count}
					
					<tr
						data-plugin-name="{$pip->pluginName}"
						data-is-supported="{if $_isSupported}true{else}false{/if}"
						data-is-important="{if $pip->isImportant()}true{else}false{/if}"
						{if $_targetCount}
							class="jsHasPipTargets"
							data-sync-dependencies="{$pip->getSyncDependencies(true)}"
						{/if}
					>
						<td class="columnText"{if $_targetCount > 0} rowspan="{$_targetCount}"{/if}>
							<p><strong>{$pip->pluginName}</strong></p>
							{if $_isSupported}
								<small class="pipDefaultFilename" title="{lang}wcf.acp.devtools.pip.defaultFilename{/lang}">{$pip->getEffectiveDefaultFilename()}</small>
							{/if}
						</td>
						{if $_isSupported}
							{if $_targetCount}
								<td class="columnIcon"><button type="button" class="button small jsInvokePip" data-target="{$_targets[0]}">{$_targets[0]}</button></td>
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
							<tr
								data-plugin-name="{$pip->pluginName}"
								data-is-important="{if $pip->isImportant()}true{else}false{/if}"
								{if $_targetCount}
									class="jsHasPipTargets jsSkipTargetDetection"
								{/if}
							>
								<td class="columnIcon"><button type="button" class="button small jsInvokePip" data-target="{$_targets[$i]}">{$_targets[$i]}</button></td>
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
		
		#syncPipMatches td:last-child {
			width: auto;
		}

		#syncPipMatches .pipDefaultFilename {
			color: #7D8287;
		}

		#syncPipMatches tr[data-is-important="true"] + tr[data-is-important="false"] td {
			border-top: 4px solid #e0e0e0;
		}
		
		#syncPipMatches.jsShowOnlyMatches tr[data-is-important="true"] ~ tr[data-is-important="false"].jsHasPipTargets:not(:is(tr[data-is-important="false"].jsHasPipTargets ~ tr)) td {
			border-top: 4px solid #e0e0e0;
		}
		
		.syncStatusContainer {
			overflow: hidden;
		}
	</style>
{else}
	<woltlab-core-notice type="error">{@$object->validate()}</woltlab-core-notice>
{/if}

{if $object->validate(true) === ''}
	{include file='__devtoolsProjectInstallationJavaScript'}
{/if}

{include file='footer'}
