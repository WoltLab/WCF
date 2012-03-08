{foreach from=$plugins key=packageID item=package}
	<li class="wcf-infoPackagePlugin wcf-box wcf-button jsPackageContainer">
		<div>
			<a href="{link controller='PackageView' id=$packageID}{/link}" title="{$package->getName()}" style="background-image: url('{@$__wcf->getPath()}icon/packagePlugin1.svg');">
				<h1>{$package->getName()}</h1>
				<small>{$package->packageDescription|language|truncate:150}</small>
			</a>
		</div>
		
		<footer>
			<nav>
				<ul class="wcf-smallButtonBar">
					<li><a href="{link controller='PackageView' id=$packageID}{/link}" title="{$package->getName()}" class="jsTooltip"><img src="{@$__wcf->getPath()}icon/info1.svg" alt="" /> <span>{lang}wcf.acp.package.button.info{/lang}</span></a></li>
					<li><a href="{link controller='PackageStartInstall' id=$packageID}action=update{/link}" title="{lang}wcf.acp.package.button.update{/lang}" class="jsTooltip"><img src="{@$__wcf->getPath()}icon/update1.svg" alt="" /> <span>{lang}wcf.acp.package.button.update{/lang}</span></a></li>
					<li title="{lang}wcf.acp.package.button.uninstall{/lang}" class="separator jsTooltip"><img src="{@$__wcf->getPath()}icon/delete1.svg" alt="" class="jsUninstallButton" data-object-id="{@$package->packageID}" data-confirm-message="{lang}wcf.acp.package.uninstallation.confirm{/lang}" /> <span>{lang}wcf.acp.package.button.uninstall{/lang}</span></li>
				</ul>
			</nav>
		</footer>
	</li>
{/foreach}
