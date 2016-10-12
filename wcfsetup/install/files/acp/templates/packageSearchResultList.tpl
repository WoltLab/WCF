{hascontent}
	<div class="section tabularBox">
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
								<a class="jsInstallPackage" data-confirm-message="{lang __encode=true}wcf.acp.package.install.confirmMessage{/lang}" data-package="{$package->package}" data-package-version="{$package->getAccessibleVersion()->packageVersion}"><span class="icon icon16 fa-plus jsTooltip" title="{lang}wcf.acp.package.button.installPackage{/lang}"></span></a>
								
								{event name='buttons'}
							</td>
							<td class="columnTitle" title="{$package->packageDescription}">{$package->packageName}</td>
							<td class="columnText">{if $package->authorURL}<a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$package->authorURL|rawurlencode}" class="externalURL">{$package->author}</a>{else}{$package->author}{/if}</td>
							<td class="columnText">
								{$package->getAccessibleVersion()->packageVersion}
								{*if $package->getAccessibleVersion()->packageUpdateVersionID != $package->getLatestVersion()->packageUpdateVersionID}
									<span class="icon icon16 icon-info-sign jsTooltip" title="{lang packageVersion=$package->getLatestVersion()->packageVersion}wcf.acp.package.newerVersionAvailable{/lang}"></span>
								{/if*}
							</td>
							<td class="columnText">{if $package->getAccessibleVersion()->licenseURL}<a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$package->getAccessibleVersion()->licenseURL|rawurlencode}" class="externalURL">{$package->getAccessibleVersion()->license}</a>{else}{$package->getAccessibleVersion()->license}{/if}</td>
							<td class="columnDate">{@$package->getAccessibleVersion()->packageDate|time}</td>
							
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