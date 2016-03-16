{hascontent}
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.package.update.excludedPackages.excluding{/lang}</h2>
			<small class="sectionDescription">{lang}wcf.acp.package.update.excludedPackages.excluding.description{/lang}</small>
		</header>
		
		<ul class="nativeList">
			{content}
				{foreach from=$excludedPackages item=excludedPackage}
					{if $excludedPackage[conflict] == 'newPackageExcludesExistingPackage'}
						<li>{lang}wcf.acp.package.update.excludedPackages.excluding.package{/lang}</li>
					{/if}
				{/foreach}
			{/content}
		</ul>
	</section>
{/hascontent}

{hascontent}
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.package.update.excludedPackages.excluded{/lang}</h2>
			<small class="sectionDescription">{lang}wcf.acp.package.update.excludedPackages.excluded.description{/lang}</small>
		</header>
		
		<ul class="nativeList">
			{content}
				{foreach from=$excludedPackages item=excludedPackage}
					{if $excludedPackage[conflict] == 'existingPackageExcludesNewPackage'}
						<li>{lang}wcf.acp.package.update.excludedPackages.excluded.package{/lang}</li>
					{/if}
				{/foreach}
			{/content}
		</ul>
	</section>
{/hascontent}
