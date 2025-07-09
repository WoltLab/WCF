{include file='header' pageTitle=$package->getName()}

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.acp.package.uninstallation.title': '{jslang}wcf.acp.package.uninstallation.title{/jslang}'
		});
		
		new WCF.ACP.Package.Uninstallation($('.jsUninstallButton'));
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{$package->getName()}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li>
				{unsafe:$interactionContextMenu->render()}
			</li>
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section tabMenuContainer">
	<nav class="tabMenu">
		<ul>
			<li><a href="#information">{lang}wcf.acp.package.information.title{/lang}</a></li>
			{if $package->getRequiredPackages()|count || $package->getDependentPackages()|count}
				<li><a href="#dependencies">{lang}wcf.acp.package.dependencies.title{/lang}</a></li>
			{/if}
			
			{event name='tabMenuTabs'}
		</ul>
	</nav>
	
	<div id="information" class="hidden tabMenuContent">
		<div class="section">
			{if $package->getDescription()}
				<dl>
					<dt>{lang}wcf.acp.package.description{/lang}</dt>
					<dd>{$package->getDescription()}</dd>
				</dl>
			{/if}
			
			<dl>
				<dt>{lang}wcf.acp.package.identifier{/lang}</dt>
				<dd>{$package->package}</dd>
			</dl>
			<dl>
				<dt>{lang}wcf.acp.package.version{/lang}</dt>
				<dd>{$package->packageVersion}</dd>
			</dl>
			<dl>
				<dt>{lang}wcf.acp.package.packageDate{/lang}</dt>
				<dd>{time time=$package->packageDate type='plainDate'}</dd>
			</dl>
			<dl>
				<dt>{lang}wcf.acp.package.installDate{/lang}</dt>
				<dd>{time time=$package->installDate}</dd>
			</dl>
			<dl>
				<dt>{lang}wcf.acp.package.updateDate{/lang}</dt>
				<dd>{time time=$package->updateDate}</dd>
			</dl>
			{if $package->packageURL != ''}
				<dl>
					<dt>{lang}wcf.acp.package.url{/lang}</dt>
					<dd><a href="{$package->packageURL}" class="externalURL"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank" rel="noopener"{/if}>{$package->packageURL}</a></dd>
				</dl>
			{/if}
			<dl>
				<dt>{lang}wcf.acp.package.author{/lang}</dt>
				<dd>{if $package->authorURL}<a href="{$package->authorURL}" class="externalURL"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank" rel="noopener"{/if}>{$package->author}</a>{else}{$package->author}{/if}</dd>
			</dl>
			{if $pluginStoreFileID}
				<dl>
					<dt>{lang}wcf.acp.package.pluginStore.file{/lang}</dt>
					<dd><a href="https://pluginstore.woltlab.com/file/{$pluginStoreFileID}/" class="externalURL"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank" rel="noopener"{/if}>{lang}wcf.acp.package.pluginStore.file.link{/lang}</a></dd>
				</dl>
			{/if}
			
			{event name='propertyFields'}
		</div>
		
		{event name='informationFieldsets'}
	</div>
	
	{if $requiredPackageGridView->countRows() || $dependentPackageGridView->countRows()}
		<div id="dependencies" class="tabMenuContainer tabMenuContent">
			<nav class="menu">
				<ul>
					{if $requiredPackageGridView->countRows()}
						<li><a href="#dependencies-required">{lang}wcf.acp.package.dependencies.required{/lang}</a></li>
					{/if}
					{if $dependentPackageGridView->countRows()}
						<li><a href="#dependencies-dependent">{lang}wcf.acp.package.dependencies.dependent{/lang}</a></li>
					{/if}
					
					{event name='dependenciesSubTabMenuTabs'}
				</ul>
			</nav>
			
			{if $requiredPackageGridView->countRows()}
				<div id="dependencies-required" class="tabMenuContent hidden">
					{unsafe:$requiredPackageGridView->render()}
				</div>
			{/if}
			
			{if $dependentPackageGridView->countRows()}
				<div id="dependencies-dependent" class="tabMenuContent hidden">
					{unsafe:$dependentPackageGridView->render()}
				</div>
			{/if}
			
			{event name='dependenciesSubTabMenuContents'}
		</div>
	{/if}
	
	{event name='tabMenuContents'}
</div>

{hascontent}
	<footer class="contentFooter">
		<nav class="contentFooterNavigation">
			<ul>
				{content}
					{event name='contentFooterNavigation'}
				{/content}
			</ul>
		</nav>
	</footer>
{/hascontent}

{include file='footer'}
