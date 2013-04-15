{include file='header' pageTitle='wcf.acp.package.list'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.Language.add('wcf.acp.package.view.button.uninstall.sure', '{lang}wcf.acp.package.view.button.uninstall.sure{/lang}');
		
		new WCF.ACP.Package.Uninstallation($('.jsPackageRow .jsUninstallButton'));
	});
	//]]>
</script>

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.package.list{/lang}</h1>
	</hgroup>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller='PackageList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	{hascontent}
		<nav>
			<ul>
				{content}
					{if $__wcf->session->getPermission('admin.system.package.canInstallPackage')}
						<li><a href="{link controller='PackageStartInstall'}action=install{/link}" title="{lang}wcf.acp.package.startInstall{/lang}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.package.startInstall{/lang}</span></a></li>
					{/if}
					
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{if $objects|count}
	<div class="tabularBox tabularBoxTitle marginTop">
		<hgroup>
			<h1>{lang}wcf.acp.package.list{/lang} <span class="badge badgeInverse">{#$items}</span></h1>
		</hgroup>
		
		<table class="table">
			<thead>
				<tr>
					<th colspan="2" class="columnID{if $sortField == 'packageID'} active {@$sortOrder}{/if}"><a href="{link controller='PackageList'}pageNo={@$pageNo}&sortField=packageID&sortOrder={if $sortField == 'packageID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle{if $sortField == 'packageName'} active {@$sortOrder}{/if}"><a href="{link controller='PackageList'}pageNo={@$pageNo}&sortField=packageName&sortOrder={if $sortField == 'packageName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.package.name{/lang}</a></th>
					<th class="columnText{if $sortField == 'author'} active {@$sortOrder}{/if}"><a href="{link controller='PackageList'}pageNo={@$pageNo}&sortField=author&sortOrder={if $sortField == 'author' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.package.author{/lang}</a></th>
					<th class="columnText">{lang}wcf.acp.package.version{/lang}</th>
					<th class="columnDate{if $sortField == 'updateDate'} active {@$sortOrder}{/if}"><a href="{link controller='PackageList'}pageNo={@$pageNo}&sortField=updateDate&sortOrder={if $sortField == 'updateDate' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.package.updateDate{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=$package}
					<tr class="jsPackageRow">
						<td class="columnIcon">
							{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
								<a href="{link controller='PackageStartInstall' id=$package->packageID}action=update{/link}" title="{lang}wcf.acp.package.button.update{/lang}" class="jsTooltip"><span class="icon icon16 icon-repeat"></span></a>
							{/if}
							{if $package->canUninstall()}
								<span class="icon icon16 icon-remove pointer jsUninstallButton jsTooltip" title="{lang}wcf.acp.package.button.uninstall{/lang}" data-object-id="{@$package->packageID}" data-confirm-message="{lang}wcf.acp.package.uninstallation.confirm{/lang}" data-is-required="{if $package->isRequired()}true{else}false{/if}"></span>
							{else}
								<span class="icon icon16 icon-remove disabled" title="{lang}wcf.acp.package.button.uninstall{/lang}"></span>
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID"><p>{@$package->packageID}</p></td>
						<td id="packageName{@$package->packageID}" class="columnTitle" title="{$package->packageDescription|language}">
							<a href="{link controller='Package' id=$package->packageID}{/link}"><span>{$package}</span></a>
						</td>
						<td class="columnText"><p>{if $package->authorURL}<a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$package->authorURL|rawurlencode}" class="externalURL">{$package->author}</a>{else}{$package->author}{/if}</p></td>
						<td class="columnText"><p>{$package->packageVersion}</p></td>
						<td class="columnDate"><p>{@$package->updateDate|time}</p></td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
		
	</div>
			
	<div class="contentNavigation">
		{@$pagesLinks}
		
		{hascontent}
			<script type="text/javascript">
				//<![CDATA[
				$(function() {
					new WCF.ACP.Package.Uninstallation($('.jsPluginContainer .jsUninstallButton'));
				});
				//]]>
			</script>
			
			<nav>
				<ul>
					{content}
						{if $__wcf->session->getPermission('admin.system.package.canInstallPackage')}
							<li><a href="{link controller='PackageStartInstall'}action=install{/link}" title="{lang}wcf.acp.package.startInstall{/lang}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.package.startInstall{/lang}</span></a></li>
						{/if}
						
						{event name='contentNavigationButtonsBottom'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</div>
{/if}

{include file='footer'}
