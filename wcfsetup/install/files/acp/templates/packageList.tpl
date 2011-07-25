{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.Language.add('wcf.acp.package.view.button.uninstall.sure', '{lang}wcf.acp.package.view.button.uninstall.sure{/lang}');
		
		new WCF.ACP.PackageUninstallation($('.packageRow .uninstallButton'));
	});
	//]]>
</script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/packageL.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.package.list{/lang}</h1>
	</hgroup>
</header>

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=PackageList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:SID_ARG_2ND_NOT_ENCODED}

	{if $__wcf->session->getPermission('admin.system.package.canInstallPackage') || $additionalLargeButtons|isset}
		<nav class="largeButtons">
			<ul>
				{if $__wcf->session->getPermission('admin.system.package.canInstallPackage')}<li><a href="index.php?form=PackageStartInstall&amp;action=install{@SID_ARG_2ND}" title="{lang}wcf.acp.package.startInstall{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/packageInstallM.png" alt="" /> <span>{lang}wcf.acp.package.startInstall{/lang}</span></a></li>{/if}
				{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
			</ul>
		</nav>
	{/if}
</div>

{if $packages|count > 0}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.package.list.count{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th colspan="2"{if $sortField == 'packageID'} class="active"{/if}><div><a href="index.php?page=PackageList&amp;pageNo={@$pageNo}&amp;sortField=packageID&amp;sortOrder={if $sortField == 'packageID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.package.list.id{/lang}{if $sortField == 'packageID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th colspan="2"{if $sortField == 'packageName'} class="active"{/if}><div><a href="index.php?page=PackageList&amp;pageNo={@$pageNo}&amp;sortField=packageName&amp;sortOrder={if $sortField == 'packageName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.package.list.name{/lang}{if $sortField == 'packageName'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th{if $sortField == 'author'} class="active"{/if}><div><a href="index.php?page=PackageList&amp;pageNo={@$pageNo}&amp;sortField=author&amp;sortOrder={if $sortField == 'author' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.package.list.author{/lang}{if $sortField == 'author'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th{if $sortField == 'packageVersion'} class="active"{/if}><div><a href="index.php?page=PackageList&amp;pageNo={@$pageNo}&amp;sortField=packageVersion&amp;sortOrder={if $sortField == 'packageVersion' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.package.list.version{/lang}{if $sortField == 'packageVersion'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th{if $sortField == 'updateDate'} class="active"{/if}><div><a href="index.php?page=PackageList&amp;pageNo={@$pageNo}&amp;sortField=updateDate&amp;sortOrder={if $sortField == 'updateDate' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.package.updateDate{/lang}{if $sortField == 'updateDate'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalHeadColumns|isset}{@$additionalHeadColumns}{/if}
				</tr>
			</thead>
			<tbody>
				{foreach from=$packages item=$package}
					<tr class="packageRow {cycle values="container-1,container-2"}">
						<td class="columnIcon">
							{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
								<a href="index.php?form=PackageStartInstall&amp;action=update&amp;activePackageID={@$package->packageID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/packageUpdateS.png" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" /></a>
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/packageUpdateDisabledS.png" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" />
							{/if}
							{if $__wcf->session->getPermission('admin.system.package.canUninstallPackage') && $package->package != 'com.woltlab.wcf' && $package->packageID != PACKAGE_ID}
								<img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" class="uninstallButton" data-objectID="{@$package->packageID}" />
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" />
							{/if}
							
							{if $additionalButtons[$package->packageID]|isset}{@$additionalButtons[$package->packageID]}{/if}
						</td>
						<td class="columnID">{@$package->packageID}</td>
						<td class="columnIcon">
							{if $package->standalone}
								<img src="{@RELATIVE_WCF_DIR}icon/packageTypeStandaloneS.png" alt="" title="{lang}wcf.acp.package.list.standalone{/lang}" />
							{elseif $package->isPlugin()}
								<img src="{@RELATIVE_WCF_DIR}icon/packageTypePluginS.png" alt="" title="{lang}wcf.acp.package.list.plugin{/lang}" />
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/packageS.png" alt="" title="{lang}wcf.acp.package.list.other{/lang}" />
							{/if}
						</td>
						<td class="columnText" title="{$package->packageDescription}" id="packageName{@$package->packageID}">
							<a href="index.php?page=PackageView&amp;activePackageID={@$package->packageID}{@SID_ARG_2ND}"><span>{$package->getName()}{if $package->instanceNo > 1 && $package->instanceName == ''} (#{#$package->instanceNo}){/if}</span></a>
						</td>
						<td class="columnText">{if $package->authorURL}<a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$package->authorURL|rawurlencode}" class="externalURL">{$package->author}</a>{else}{$package->author}{/if}</td>
						<td class="columnText">{$package->packageVersion}</td>
						<td class="columnDate">{@$package->updateDate|time}</td>
						
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
				{if $__wcf->session->getPermission('admin.system.package.canInstallPackage')}<li><a href="index.php?form=PackageStartInstall&amp;action=install{@SID_ARG_2ND}" title="{lang}wcf.acp.package.startInstall{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/packageInstallM.png" alt="" /> <span>{lang}wcf.acp.package.startInstall{/lang}</span></a></li>{/if}
				{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
			</ul>
		</nav>
	{/if}
</div>

{include file='footer'}
