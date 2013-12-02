{include file='header' pageTitle=$package->getName()}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init()
		
		WCF.Language.addObject({
			'wcf.acp.package.uninstallation.title': '{lang}wcf.acp.package.uninstallation.title{/lang}'
		});
		
		new WCF.ACP.Package.Uninstallation($('.jsUninstallButton'), {if PACKAGE_ID > 1}'{link controller='PackageList' forceWCF=true}packageID={literal}{packageID}{/literal}{/link}'{else}null{/if});
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{$package->getName()}</h1>
	<p>{$package->packageDescription|language}</p>
</header>

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<div class="tabMenuContainer">
	<nav class="tabMenu">
		<ul>
			<li><a href="{@$__wcf->getAnchor('information')}">{lang}wcf.acp.package.information.title{/lang}</a></li>
			{if $package->getRequiredPackages()|count || $package->getDependentPackages()|count}
				<li><a href="{@$__wcf->getAnchor('dependencies')}">{lang}wcf.acp.package.dependencies.title{/lang}</a></li>
			{/if}
			
			{event name='tabMenuTabs'}
		</ul>
	</nav>
	
	<div id="information" class="container containerPadding hidden tabMenuContent">
		<fieldset>
			<legend>{lang}wcf.acp.package.information.properties{/lang}</legend>
			
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
				<dd>{@$package->packageDate|date}</dd>
			</dl>
			<dl>
				<dt>{lang}wcf.acp.package.installDate{/lang}</dt>
				<dd>{@$package->installDate|time}</dd>
			</dl>
			<dl>
				<dt>{lang}wcf.acp.package.updateDate{/lang}</dt>
				<dd>{@$package->updateDate|time}</dd>
			</dl>
			{if $package->packageURL != ''}
				<dl>
					<dt>{lang}wcf.acp.package.url{/lang}</dt>
					<dd><a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$package->packageURL|rawurlencode}" class="externalURL">{$package->packageURL}</a></dd>
				</dl>
			{/if}
			<dl>
				<dt>{lang}wcf.acp.package.author{/lang}</dt>
				<dd>{if $package->authorURL}<a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$package->authorURL|rawurlencode}" class="externalURL">{$package->author}</a>{else}{$package->author}{/if}</dd>
			</dl>
			
			{event name='propertyFields'}
		</fieldset>
		
		{if $package->packageDescription|language}
			<fieldset>
				<legend>{lang}wcf.acp.package.description{/lang}</legend>
				
				<p>{$package->packageDescription|language}</p>
			</fieldset>
		{/if}
		
		{event name='informationFieldsets'}
	</div>
	
	{if $package->getRequiredPackages()|count || $package->getDependentPackages()|count}
		<div id="dependencies" class="container containerPadding tabMenuContainer tabMenuContent">
			<nav class="menu">
				<ul>
					{if $package->getRequiredPackages()|count}
						<li><a href="{@$__wcf->getAnchor('dependencies-required')}">{lang}wcf.acp.package.dependencies.required{/lang}</a></li>
					{/if}
					{if $package->getDependentPackages()|count}
						<li><a href="{@$__wcf->getAnchor('dependencies-dependent')}">{lang}wcf.acp.package.dependencies.dependent{/lang}</a></li>
					{/if}
					
					{event name='dependenciesSubTabMenuTabs'}
				</ul>
			</nav>
			
			{hascontent}
				<div id="dependencies-required" class="tabularBox tabularBoxTitle hidden">
					<header>
						<h2>{lang}wcf.acp.package.dependencies.required{/lang}</h2>
						<small>{lang}wcf.acp.package.dependencies.required.description{/lang}</small>
					</header>
					
					<table class="table">
						<thead>
							<tr>
								<th colspan="2" class="columnID">{lang}wcf.global.objectID{/lang}</th>
								<th class="columnTitle">{lang}wcf.acp.package.name{/lang}</th>
								<th class="columnText">{lang}wcf.acp.package.author{/lang}</th>
								<th class="columnText">{lang}wcf.acp.package.version{/lang}</th>
								<th class="columnDigits">{lang}wcf.acp.package.packageDate{/lang}</th>
								
								{event name='requirementColumnHeads'}
							</tr>
						</thead>
						
						<tbody>
							{content}
								{foreach from=$package->getRequiredPackages() item=requiredPackage}
									<tr class="jsPackageRow">
										<td class="columnIcon">
											{if $requiredPackage->canUninstall()}
												<span class="icon icon16 icon-remove pointer jsTooltip jsUninstallButton" title="{lang}wcf.acp.package.button.uninstall{/lang}" data-object-id="{@$requiredPackage->packageID}" data-confirm-message="{lang package=$requiredPackage}wcf.acp.package.uninstallation.confirm{/lang}" data-is-required="{if $requiredPackage->isRequired()}true{else}false{/if}" data-is-application="{if $requiredPackage->isApplication}true{else}false{/if}"></span>
											{else}
												<span class="icon icon16 icon-remove disabled" title="{lang}wcf.acp.package.button.uninstall{/lang}"></span>
											{/if}
										</td>
										<td class="columnID">{@$requiredPackage->packageID}</td>
										<td class="columnTitle" title="{$requiredPackage->packageDescription|language}"><a href="{link controller='Package' id=$requiredPackage->packageID}{/link}">{$requiredPackage}</a></td>
										<td class="columnText">{if $requiredPackage->authorURL}<a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$requiredPackage->authorURL|rawurlencode}" class="externalURL">{$requiredPackage->author}</a>{else}{$requiredPackage->author}{/if}</td>
										<td class="columnText">{$requiredPackage->packageVersion}</td>
										<td class="columnDate">{@$requiredPackage->packageDate|date}</td>
										
										{event name='requirementColumns'}
									</tr>
								{/foreach}
							{/content}
						</tbody>
					</table>
				</div>
			{/hascontent}
			
			{hascontent}
				<div id="dependencies-dependent" class="tabularBox tabularBoxTitle hidden">
					<header>
						<h2>{lang}wcf.acp.package.dependencies.dependent{/lang}</h2>
						<small>{lang}wcf.acp.package.dependencies.dependent.description{/lang}</small>
					</header>
					
					<table class="table">
						<thead>
							<tr>
								<th colspan="2" class="columnID">{lang}wcf.global.objectID{/lang}</th>
								<th class="columnTitle">{lang}wcf.acp.package.name{/lang}</th>
								<th class="columnText">{lang}wcf.acp.package.author{/lang}</th>
								<th class="columnText">{lang}wcf.acp.package.version{/lang}</th>
								<th class="columnDigits">{lang}wcf.acp.package.packageDate{/lang}</th>
								
								{event name='dependencyColumnHeads'}
							</tr>
						</thead>
						
						<tbody>
							{content}
								{foreach from=$package->getDependentPackages() item=dependentPackage}
									<tr class="jsPackageRow">
										<td class="columnIcon">
											{if $dependentPackage->canUninstall()}
												<span class="icon icon16 icon-remove pointer jsTooltip jsUninstallButton" title="{lang}wcf.acp.package.button.uninstall{/lang}" data-object-id="{@$dependentPackage->packageID}" data-confirm-message="{lang package=$dependentPackage}wcf.acp.package.uninstallation.confirm{/lang}" data-is-required="{if $dependentPackage->isRequired()}true{else}false{/if}" data-is-application="{if $dependentPackage->isApplication}true{else}false{/if}"></span>
											{else}
												<span class="icon icon16 icon-remove disabled" title="{lang}wcf.acp.package.button.uninstall{/lang}"></span>
											{/if}
										</td>
										<td class="columnID">{@$dependentPackage->packageID}</td>
										<td class="columnTitle" title="{$dependentPackage->packageDescription|language}"><a href="{link controller='Package' id=$dependentPackage->packageID}{/link}">{$dependentPackage}</a></td>
										<td class="columnText">{if $dependentPackage->authorURL}<a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$dependentPackage->authorURL|rawurlencode}" class="externalURL">{$dependentPackage->author}</a>{else}{$dependentPackage->author}{/if}</td>
										<td class="columnText">{$dependentPackage->packageVersion}</td>
										<td class="columnDate">{@$dependentPackage->packageDate|date}</td>
										
										{event name='dependencyColumns'}
									</tr>
								{/foreach}
							{/content}
						</tbody>
					</table>
				</div>
			{/hascontent}
			
			{event name='dependenciesSubTabMenuContents'}
		</div>
	{/if}
	
	{event name='tabMenuContents'}
</div>

<div class="contentNavigation">
	<nav>
		<ul>
			{event name='contentNavigationButtonsBottom'}
			
			<li><a href="{link controller='PackageList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.package.list{/lang}</span></a></li>
		</ul>
	</nav>
</div>

{include file='footer'}
