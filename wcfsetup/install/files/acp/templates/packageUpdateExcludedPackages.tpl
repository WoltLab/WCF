{hascontent}
	<fieldset>
		<legend>{lang}wcf.acp.package.update.excludedPackages.excluding{/lang}</legend>
		<small>{lang}wcf.acp.package.update.excludedPackages.excluding.description{/lang}</small>
		
		<ul class="nativeList">
			{content}
				{foreach from=$excludedPackages item=excludedPackage}
					{if $excludedPackage[conflict] == 'newPackageExcludesExistingPackage'}
						<li>{lang}wcf.acp.package.update.excludedPackages.excluding.package{/lang}</li>
					{/if}
				{/foreach}
			{/content}
		</ul>
	</fieldset>
{/hascontent}

{hascontent}
	<fieldset>
		<legend>{lang}wcf.acp.package.update.excludedPackages.excluded{/lang}</legend>
		<small>{lang}wcf.acp.package.update.excludedPackages.excluded.description{/lang}</small>
		
		<ul class="nativeList">
			{content}
				{foreach from=$excludedPackages item=excludedPackage}
					{if $excludedPackage[conflict] == 'existingPackageExcludesNewPackage'}
						<li>{lang}wcf.acp.package.update.excludedPackages.excluded.package{/lang}</li>
					{/if}
				{/foreach}
			{/content}
		</ul>
	</fieldset>
{/hascontent}
