{include file='header'}

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
	{pages print=true assign=pagesLinks controller='PackageListDetailed' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}

	{hascontent}
		<nav>
			<ul>
				{content}
					{if $__wcf->session->getPermission('admin.system.package.canInstallPackage')}
						<li><a href="{link controller='PackageStartInstall'}action=install{/link}" title="{lang}wcf.acp.package.startInstall{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/add1.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.package.startInstall{/lang}</span></a></li>
					{/if}
				
					{event name='largeButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{if $objects|count > 0}
	<div class="tabularBox tabularBoxTitle marginTop shadow">
		<hgroup>
			<h1><a href="{link controller='PackageList'}{/link}">{lang}wcf.acp.package.list{/lang} <span class="badge badgeInverse" title="{lang}wcf.acp.package.list.count{/lang}">{#$items}</span></a></h1>
		</hgroup>
		
		<table class="table">
			<thead>
				<tr>
					<th colspan="2" class="columnID{if $sortField == 'packageID'} active{/if}"><a href="{link controller='PackageListDetailed'}pageNo={@$pageNo}&sortField=packageID&sortOrder={if $sortField == 'packageID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}{if $sortField == 'packageID'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th colspan="2" class="columnTitle{if $sortField == 'packageName'} active{/if}"><a href="{link controller='PackageListDetailed'}pageNo={@$pageNo}&sortField=packageName&sortOrder={if $sortField == 'packageName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.package.name{/lang}{if $sortField == 'packageName'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnText{if $sortField == 'author'} active{/if}"><a href="{link controller='PackageListDetailed'}pageNo={@$pageNo}&sortField=author&sortOrder={if $sortField == 'author' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.package.author{/lang}{if $sortField == 'author'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnText{if $sortField == 'packageVersion'}active{/if}"><a href="{link controller='PackageListDetailed'}pageNo={@$pageNo}&sortField=packageVersion&sortOrder={if $sortField == 'packageVersion' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.package.version{/lang}{if $sortField == 'packageVersion'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDate{if $sortField == 'updateDate'} active{/if}"><a href="{link controller='PackageListDetailed'}pageNo={@$pageNo}&sortField=updateDate&sortOrder={if $sortField == 'updateDate' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.package.updateDate{/lang}{if $sortField == 'updateDate'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					
					{event name='headColumns'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=$package}
					<tr class="jsPackageRow">
						<td class="columnIcon">
							{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage')}
								<a href="{link controller='PackageStartInstall' id=$package->packageID}action=update{/link}"><img src="{@RELATIVE_WCF_DIR}icon/update1.svg" alt="" title="{lang}wcf.acp.package.button.update{/lang}" class="icon16 jsTooltip" /></a>
							{else}
								<img src="{@$__wcf->getPath()}icon/update1D.svg" alt="" title="{lang}wcf.acp.package.button.update{/lang}" class="icon16" />
							{/if}
							{if $__wcf->session->getPermission('admin.system.package.canUninstallPackage') && $package->package != 'com.woltlab.wcf' && $package->packageID != PACKAGE_ID}
								<img src="{@$__wcf->getPath()}icon/delete1.svg" alt="" title="{lang}wcf.acp.package.button.uninstall{/lang}" class="icon16 jsUninstallButton jsTooltip" data-object-id="{@$package->packageID}" data-confirm-message="{lang}wcf.acp.package.uninstallation.confirm{/lang}" />
							{else}
								<img src="{@$__wcf->getPath()}icon/delete1D.svg" alt="" title="{lang}wcf.acp.package.button.uninstall{/lang}" class="icon16" />
							{/if}
							
							{event name='buttons'}
						</td>
						<td class="columnID"><p>{@$package->packageID}</p></td>
						<td class="columnIcon">
							{if $package->isApplication}
								<img src="{@$__wcf->getPath()}icon/packageApplication1.svg" alt="" title="{lang}wcf.acp.package.type.application{/lang}" class="icon16 jsTooltip" />
							{elseif $package->isPlugin()}
								<img src="{@$__wcf->getPath()}icon/packagePlugin1.svg" alt="" title="{lang}wcf.acp.package.type.plugin{/lang}" class="icon16 jsTooltip" />
							{else}
								<img src="{@$__wcf->getPath()}icon/package1.svg" alt="" title="{lang}wcf.acp.package.type.other{/lang}" class="icon16 jsTooltip" />
							{/if}
						</td>
						<td id="packageName{@$package->packageID}" class="columnTitle" title="{$package->packageDescription|language}">
							<a href="{link controller='PackageView' id=$package->packageID}{/link}"><span>{$package->getName()}{if $package->instanceNo > 1 && $package->instanceName == ''} (#{#$package->instanceNo}){/if}</span></a>
						</td>
						<td class="columnText"><p>{if $package->authorURL}<a href="{@$__wcf->getPath()}acp/dereferrer.php?url={$package->authorURL|rawurlencode}" class="wcf-externalURL">{$package->author}</a>{else}{$package->author}{/if}</p></td>
						<td class="columnText"><p>{$package->packageVersion}</p></td>
						<td class="columnDate"><p>{@$package->updateDate|time}</p></td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
		
	</div>
{/if}

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
						<li><a href="{link controller='PackageStartInstall'}action=install{/link}" title="{lang}wcf.acp.package.startInstall{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/add1.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.package.startInstall{/lang}</span></a></li>
					{/if}
				
					{event name='largeButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{include file='footer'}
