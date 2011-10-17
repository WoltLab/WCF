{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.Language.add('wcf.acp.package.view.button.uninstall.sure', '{lang}wcf.acp.package.view.button.uninstall.sure{/lang}');
		
		new WCF.ACP.PackageUninstallation($('.packageRow .uninstallButton'));
	});
	//]]>
</script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/packageStandalone1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.package.list{/lang}</h1>
	</hgroup>
</header>

<div class="contentHeader">
	{pages print=true assign=pagesLinks controller='PackageListDetailed' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}

	{if $__wcf->session->getPermission('admin.system.package.canInstallPackage') || $additionalLargeButtons|isset}
		<nav class="largeButtons">
			<ul>
				{if $__wcf->session->getPermission('admin.system.package.canInstallPackage')}<li><a href="{link controller='PackageStartInstall'}action=install{/link}" title="{lang}wcf.acp.package.startInstall{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.package.startInstall{/lang}</span></a></li>{/if}
				{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
			</ul>
		</nav>
	{/if}
</div>

{if $objects|count > 0}
	<div class="border boxTitle">
		<hgroup>
			<h1><a href="#">{lang}wcf.acp.package.list{/lang} <span class="badge" title="{lang}wcf.acp.package.list.count{/lang}">{#$items}</span></a></h1>
		</hgroup>
		
		<table>
			<thead>
				<tr>
					<th colspan="2" class="columnID{if $sortField == 'packageID'} active{/if}"><a href="{link controller='PackageListDetailed'}pageNo={@$pageNo}&sortField=packageID&sortOrder={if $sortField == 'packageID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.package.list.id{/lang}{if $sortField == 'packageID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th colspan="2" class="columnTitle{if $sortField == 'packageName'} active{/if}"><a href="{link controller='PackageListDetailed'}pageNo={@$pageNo}&sortField=packageName&sortOrder={if $sortField == 'packageName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.package.list.name{/lang}{if $sortField == 'packageName'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnText{if $sortField == 'author'} active{/if}"><a href="{link controller='PackageListDetailed'}pageNo={@$pageNo}&sortField=author&sortOrder={if $sortField == 'author' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.package.list.author{/lang}{if $sortField == 'author'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnText{if $sortField == 'packageVersion'}active{/if}"><a href="{link controller='PackageListDetailed'}pageNo={@$pageNo}&sortField=packageVersion&sortOrder={if $sortField == 'packageVersion' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.package.list.version{/lang}{if $sortField == 'packageVersion'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDate{if $sortField == 'updateDate'} active{/if}"><a href="{link controller='PackageListDetailed'}pageNo={@$pageNo}&sortField=updateDate&sortOrder={if $sortField == 'updateDate' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.package.updateDate{/lang}{if $sortField == 'updateDate'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					
					{if $additionalHeadColumns|isset}{@$additionalHeadColumns}{/if}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=$package}
					<tr class="packageRow">
						<td class="columnIcon">
							{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
								<a href="{link controller='PackageStartInstall' id=$package->packageID}action=update{/link}"><img src="{@RELATIVE_WCF_DIR}icon/update1.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" class="balloonTooltip" /></a>
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/update1D.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" />
							{/if}
							{if $__wcf->session->getPermission('admin.system.package.canUninstallPackage') && $package->package != 'com.woltlab.wcf' && $package->packageID != PACKAGE_ID}
								<img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" data-objectID="{@$package->packageID}" class="uninstallButton balloonTooltip" />
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/delete1D.svg" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" />
							{/if}
							
							{if $additionalButtons[$package->packageID]|isset}{@$additionalButtons[$package->packageID]}{/if}
						</td>
						<td class="columnID"><p>{@$package->packageID}</p></td>
						<td class="columnIcon">
							{if $package->standalone}
								<img src="{@RELATIVE_WCF_DIR}icon/packageStandalone1.svg" alt="" title="{lang}wcf.acp.package.list.standalone{/lang}" class="balloonTooltip" />
							{elseif $package->isPlugin()}
								<img src="{@RELATIVE_WCF_DIR}icon/packagePlugin1.svg" alt="" title="{lang}wcf.acp.package.list.plugin{/lang}" class="balloonTooltip" />
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/package1.svg" alt="" title="{lang}wcf.acp.package.list.other{/lang}" class="balloonTooltip" />
							{/if}
						</td>
						<td id="packageName{@$package->packageID}" class="columnTitle" title="{$package->packageDescription}">
							<a href="{link controller='PackageView' id=$package->packageID}{/link}"><span>{$package->getName()}{if $package->instanceNo > 1 && $package->instanceName == ''} (#{#$package->instanceNo}){/if}</span></a>
						</td>
						<td class="columnText><p>{if $package->authorURL}<a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$package->authorURL|rawurlencode}" class="externalURL">{$package->author}</a>{else}{$package->author}{/if}</p></td>
						<td class="columnText"><p>{$package->packageVersion}</p></td>
						<td class="columnDate"><p>{@$package->updateDate|time}</p></td>
						
						{if $additionalColumns[$package->packageID]|isset}{@$additionalColumns[$package->packageID]}{/if}
					</tr>
				{/foreach}
			</tbody>
		</table>
		
	</div>
{/if}

<div class="contentFooter">
	{@$pagesLinks}
	
	{if $__wcf->session->getPermission('admin.system.package.canInstallPackage') || $additionalLargeButtons|isset}
		<nav class="largeButtons">
			<ul>
				{if $__wcf->session->getPermission('admin.system.package.canInstallPackage')}<li><a href="{link controller='PackageStartInstall'}action=install{/link}" title="{lang}wcf.acp.package.startInstall{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.package.startInstall{/lang}</span></a></li>{/if}
				{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
			</ul>
		</nav>
	{/if}
</div>

{include file='footer'}
