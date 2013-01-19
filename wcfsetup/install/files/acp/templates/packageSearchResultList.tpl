{hascontent}
	<div class="tabularBox marginTop">
		<table class="table">
			<thead>
				<tr>
					<th colspan="2" class="columnTitle">{lang}wcf.acp.package.name{/lang}</th>
					<th class="columnText">{lang}wcf.acp.package.author{/lang}</a></th>
					<th class="columnText">{lang}wcf.acp.package.version{/lang}</th>
					<th class="columnText">{lang}wcf.acp.package.license{/lang}</th>
					<th class="columnDate">{lang}wcf.acp.package.packageDate{/lang}</a></th>
					
					{event name='headColumns'}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$packageUpdates item=$package}
						<tr class="jsPackageRow">
							<td class="columnIcon">
								<img src="{@$__wcf->getPath()}icon/add.svg" alt="" title="{lang}wcf.acp.package.button.install{/lang}" class="icon16 pointer jsTooltip">
								
								{event name='buttons'}
							</td>
							<td class="columnTitle" title="{$package->packageDescription}"><p>{$package->packageName}</p></td>
							<td class="columnText"><p>{if $package->authorURL}<a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$package->authorURL|rawurlencode}" class="externalURL">{$package->author}</a>{else}{$package->author}{/if}</p></td>
							<td class="columnText"><p>
								{$package->getAccessibleVersion()->packageVersion}
								{if $package->getAccessibleVersion()->packageUpdateVersionID != $package->getLatestVersion()->packageUpdateVersionID}
									<img src="{@$__wcf->getPath()}icon/info.svg" alt="" title="{lang packageVersion=$package->getLatestVersion()->packageVersion}wcf.acp.package.newerVersionAvailable{/lang}" class="icon16 jsTooltip" />
								{/if}
							</p></td>
							<td class="columnText"><p>{if $package->getAccessibleVersion()->licenseURL}<a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$package->getAccessibleVersion()->licenseURL|rawurlencode}" class="externalURL">{$package->getAccessibleVersion()->license}</a>{else}{$package->getAccessibleVersion()->license}{/if}</p></td>
							<td class="columnDate"><p>{@$package->getAccessibleVersion()->packageDate|time}</p></td>
							
							{event name='columns'}
						</tr>
					{/foreach}
				{/content}
			</tbody>
		</table>
	</div>
{hascontentelse}
	<p class="info">{lang}wcf.acp.package.search.error.noMatches{/lang}</p>
{/hascontent}