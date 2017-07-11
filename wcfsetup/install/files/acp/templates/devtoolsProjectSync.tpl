{include file='header' pageTitle='wcf.acp.devtools.project.sync'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.devtools.project.sync{/lang}</h1>
		<p class="contentHeaderDescription">{$object->name}</p>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='DevtoolsProjectList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.devtools.project.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $object->validate() === ''}
	<p class="info">{lang}wcf.acp.devtools.pip.notice{/lang}</p>
	
	<form method="post" action="{link controller='DevtoolsProjectSync' id=$objectID}{/link}">
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
						<th class="columnIcon">{lang}wcf.acp.devtools.pip.target{/lang}</th>
					</tr>
				</thead>
				
				<tbody>
					{foreach from=$object->getPips() item=pip}
						{assign var=_isSupported value=$pip->isSupported()}
						{assign var=_targets value=$pip->getTargets($object)}
						
						<tr data-is-supported="{if $_isSupported}true{else}false{/if}" {if !$_targets|empty} class="jsHasPipTargets"{/if}>
							<td class="columnText">{$pip->pluginName}</td>
							{if $_isSupported}
								<td class="columnText"><small>{$pip->getDefaultFilename()}</small></td>
								<td class="columnIcon">
									{hascontent}
										<ul class="buttonGroup">
											{content}
												{foreach from=$_targets item=target}
													<li><button class="small jsInvokePip" data-plugin-name="{$pip->pluginName}" data-target="{$target}">{$target}</button></li>
												{/foreach}
											{/content}
										</ul>
									{hascontentelse}
										<small>{lang}wcf.acp.devtools.pip.target.noMatches{/lang}</small>
									{/hascontent}
								</td>
							{else}
								<td class="columnText" colspan="2">{$pip->getFirstError()}</td>
							{/if}
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
		
		{event name='sections'}
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	</form>
	
	<script data-relocate="true">
		var container = elById('syncPipMatches');
		elById('syncShowOnlyMatches').addEventListener('change', function() {
			container.classList.toggle('jsShowOnlyMatches');
		});
		
		require(['Ajax'], function(Ajax) {
			var that = {
				_ajaxSetup: function() {
					return {
						data: {
							actionName: 'invoke',
							className: 'wcf\\data\\package\\installation\\plugin\\PackageInstallationPluginAction',
							parameters: {
								projectID: {@$object->projectID}
							}
						}
					}
				}
			};
			
			elBySelAll('.jsInvokePip', container, function(button) {
				button.addEventListener(WCF_CLICK_EVENT, function(event) {
					event.preventDefault();
					
					Ajax.api(that, {
						parameters: {
							pluginName: elData(button, 'plugin-name'),
							target: elData(button, 'target')
						}
					});
				});
			});
		});
	</script>
	
	<style>
		#syncPipMatches.jsShowOnlyMatches tbody > tr:not(.jsHasPipTargets) {
			display: none;
		}
	</style>
{else}
	<p class="error">{$object->validate()}</p>
{/if}

{include file='footer'}
