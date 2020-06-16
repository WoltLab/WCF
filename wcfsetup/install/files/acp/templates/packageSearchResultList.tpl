<table class="table">
	<thead>
		<tr>
			<th colspan="2" class="columnTitle">{lang}wcf.acp.package.name{/lang}</th>
			<th class="columnText">{lang}wcf.acp.package.author{/lang}</a></th>
			<th class="columnText">{lang}wcf.acp.package.license{/lang}</th>
			<th class="columnDate">{lang}wcf.acp.package.packageDate{/lang}</a></th>
			
			{event name='headColumns'}
		</tr>
	</thead>
	
	<tbody>
		<tr>
			<td colspan="5"><small>{lang count=$trustedSources|count}wcf.acp.package.search.result.trusted{/lang}</small></td>
		</tr>
		{foreach from=$trustedSources item=$package}
			<tr class="jsPackageRow">
				<td class="columnIcon">
					<a href="#" class="jsInstallPackage" data-confirm-message="{lang __encode=true}wcf.acp.package.install.confirmMessage{/lang}" data-package="{$package->package}" data-package-version="{$package->getAccessibleVersion()->packageVersion}"><span class="icon icon16 fa-plus jsTooltip" title="{lang}wcf.acp.package.button.installPackage{/lang}"></span></a>
				</td>
				<td class="columnTitle" title="{$package->packageDescription}">
					<div class="packageSearchName">{$package->packageName} <span class="packageSearchVersion">{$package->getAccessibleVersion()->packageVersion}</span></div>
					<span class="packageSearchPackage">{$package->package}</span>
				</td>
				<td class="columnText">{if $package->authorURL}<a href="{$package->authorURL}" class="externalURL">{$package->author}</a>{else}{$package->author}{/if}</td>
				<td class="columnText">{if $package->getAccessibleVersion()->licenseURL}<a href="{$package->getAccessibleVersion()->licenseURL}" class="externalURL">{$package->getAccessibleVersion()->license}</a>{else}{$package->getAccessibleVersion()->license}{/if}</td>
				<td class="columnDate">{@$package->getAccessibleVersion()->packageDate|time}</td>
			</tr>
		{/foreach}
		{hascontent}
			<tr>
				<td colspan="5"><small>{lang count=$thirdPartySources|count}wcf.acp.package.search.result.thirdParty{/lang}</small></td>
			</tr>
			{content}
				{foreach from=$thirdPartySources item=$package}
					<tr class="jsPackageRow">
						<td class="columnIcon">
							<a href="#" class="jsInstallPackage" data-confirm-message="{lang __encode=true}wcf.acp.package.install.confirmMessage{/lang}" data-package="{$package->package}" data-package-version="{$package->getAccessibleVersion()->packageVersion}"><span class="icon icon16 fa-plus jsTooltip" title="{lang}wcf.acp.package.button.installPackage{/lang}"></span></a>
						</td>
						<td class="columnTitle" title="{$package->packageDescription}">
							<div class="packageSearchName">{$package->packageName} <span class="packageSearchVersion">{$package->getAccessibleVersion()->packageVersion}</span></div>
							<span class="packageSearchPackage">{$package->package}</span>
						</td>
						<td class="columnText">{if $package->authorURL}<a href="{$package->authorURL}" class="externalURL">{$package->author}</a>{else}{$package->author}{/if}</td>
						<td class="columnText">{if $package->getAccessibleVersion()->licenseURL}<a href="{$package->getAccessibleVersion()->licenseURL}" class="externalURL">{$package->getAccessibleVersion()->license}</a>{else}{$package->getAccessibleVersion()->license}{/if}</td>
						<td class="columnDate">{@$package->getAccessibleVersion()->packageDate|time}</td>
					</tr>
				{/foreach}
			{/content}
		{/hascontent}
	</tbody>
</table>
