{include file='header' pageTitle=$package->getName()}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init()
		
		new WCF.ACP.Package.Uninstallation($('.jsPackageRow .jsUninstallButton'));
	});
	//]]>
</script>

<header class="boxHeadline">
	<hgroup>
		<h1>{$package->getName()}</h1>
		<h2>{$package->packageDescription|language}</h2>
	</hgroup>
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
			{if $package->parentPackageID}
				<dl>
					<dt>{lang}wcf.acp.package.parentPackage{/lang}</dt>
					<dd><a href="{link controller='Package' id=$package->parentPackageID}{/link}">{$package->getParentPackage()->getName()}</a></dd>
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
					
					{event name='DependenciesSubTabMenuTabs'}
				</ul>
			</nav>
			
			{hascontent}
				<div id="dependencies-required" class="tabularBox tabularBoxTitle hidden">
					<hgroup>
						<h1>{lang}wcf.acp.package.dependencies.required{/lang}</h1>
						<h2>{lang}wcf.acp.package.dependencies.required.description{/lang}</h2>
					</hgroup>
					
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
											{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
												<a href="{link controller='PackageStartInstall' id=$requiredPackage->packageID}action=update{/link}" title="{lang}wcf.acp.package.button.update{/lang}" class="jsTooltip"><span class="icon icon16 icon-repeat"></span></a>
											{/if}
											{if $requiredPackage->canUninstall()}
												<span class="icon icon16 icon-remove pointer jsTooltip jsUninstallButton" title="{lang}wcf.acp.package.button.uninstall{/lang}" data-objectID="{@$requiredPackage->packageID}" data-confirm-message="{lang package=$requiredPackage}wcf.acp.package.uninstallation.confirm{/lang}" data-is-required="{if $package->isRequired()}true{else}false{/if}"></span>
											{else}
												<span class="icon icon16 icon-remove disabled" title="{lang}wcf.acp.package.button.uninstall{/lang}"></span>
											{/if}
										</td>
										<td class="columnID"><p>{@$requiredPackage->packageID}</p></td>
										<td class="columnTitle" title="{$requiredPackage->packageDescription|language}"><p><a href="{link controller='Package' id=$requiredPackage->packageID}{/link}">{$requiredPackage}</a></p></td>
										<td class="columnText">{if $requiredPackage->authorURL}<p><a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$requiredPackage->authorURL|rawurlencode}" class="externalURL">{$requiredPackage->author}</a>{else}{$requiredPackage->author}</p>{/if}</td>
										<td class="columnText"><p>{$requiredPackage->packageVersion}</p></td>
										<td class="columnDate"><p>{@$requiredPackage->packageDate|date}</p></td>
										
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
					<hgroup>
						<h1>{lang}wcf.acp.package.dependencies.dependent{/lang}</h1>
						<h2>{lang}wcf.acp.package.dependencies.dependent.description{/lang}</h2>
					</hgroup>
					
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
											{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
												<a href="{link controller='PackageStartInstall' id=$dependentPackage->packageID}action=update{/link}" title="{lang}wcf.acp.package.button.update{/lang}" class="jsTooltip"><span class="icon icon16 icon-repeat"></span></a>
											{/if}
											{if $dependentPackage->canUninstall()}
												<span class="icon icon16 icon-remove pointer jsTooltip jsUninstallButton" title="{lang}wcf.acp.package.button.uninstall{/lang}" data-objectID="{@$dependentPackage->packageID}" data-confirm-message="{lang package=$dependentPackage}wcf.acp.package.uninstallation.confirm{/lang}" data-is-required="{if $package->isRequired()}true{else}false{/if}"></span>
											{else}
												<span class="icon icon16 icon-remove disabled" title="{lang}wcf.acp.package.button.uninstall{/lang}"></span>
											{/if}
										</td>
										<td class="columnID"><p>{@$dependentPackage->packageID}</p></td>
										<td class="columnTitle" title="{$dependentPackage->packageDescription|language}"><p><a href="{link controller='Package' id=$dependentPackage->packageID}{/link}">{$dependentPackage}</a></p></td>
										<td class="columnText">{if $dependentPackage->authorURL}<p><a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$dependentPackage->authorURL|rawurlencode}" class="externalURL">{$dependentPackage->author}</a>{else}{$dependentPackage->author}</p>{/if}</td>
										<td class="columnText"><p>{$dependentPackage->packageVersion}</p></td>
										<td class="columnDate"><p>{@$dependentPackage->packageDate|date}</p></td>
										
										{event name='dependencyColumns'}
									</tr>
								{/foreach}
							{/content}
						</tbody>
					</table>
				</div>
			{/hascontent}
			
			{event name='DependenciesSubTabMenuContents'}
		</div>
	{/if}
	
	{event name='tabMenuContents'}
</div>

<div class="contentNavigation">
	<nav>
		<ul>
			{if PACKAGE_ID != $package->packageID}
				{if $package->canUninstall()}
					{* TODO: maybe show users that this package can't be uninstalled because a dependent package is the active application *}
					<li><a href="{link controller='Package'}action=startUninstall&packageID={@$package->packageID}{/link}" onclick="return confirm('{lang}wcf.acp.package.button.uninstall.sure{/lang}')" title="{lang}wcf.acp.package.button.uninstall{/lang}" class="button"><span class="icon icon16 icon-remove"></span> <span>{lang}wcf.acp.package.button.uninstall{/lang}</span></a></li>
				{/if}
			{/if}
			{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
				<li><a href="{link controller='PackageStartInstall' id=$package->packageID}action=update{/link}" title="{lang}wcf.acp.package.button.update{/lang}" class="button"><span class="icon icon16 icon-repeat"></span> <span>{lang}wcf.acp.package.button.update{/lang}</span></a></li>
			{/if}
			
			{event name='contentNavigationButtonsBottom'}
			
			<li><a href="{link controller='PackageList'}{/link}" title="{lang}wcf.acp.menu.link.package.list{/lang}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.package.list{/lang}</span></a></li>
		</ul>
	</nav>
</div>

{include file='footer'}
