{foreach from=$plugins key=packageID item=package}
	<li class="wcf-infoPackagePlugin">
		<div>
			<a href="{link controller='PackageView' id=$packageID}{/link}" title="{$package->getName()}" style="background-image: url('{@RELATIVE_WCF_DIR}icon/packagePlugin1.svg');">
				<h1>{$package->getName()}</h1>
				<small>{$package->packageDescription|truncate:150}</small>
			</a>
		</div>
		
		<footer>
			<nav>
				<ul class="wcf-smallButtonBar">
					<li><a href="{link controller='PackageView' id=$packageID}{/link}" title="{$package->getName()}" class="jsTooltip"><img src="{@RELATIVE_WCF_DIR}icon/info1.svg" alt="" /> <span>Update</span></a></li>
					<li><a href="{link controller='PackageStartInstall' id=$packageID}action=update{/link}" title="{lang}wcf.acp.package.view.button.update{/lang}" class="jsTooltip"><img src="{@RELATIVE_WCF_DIR}icon/update1.svg" alt="" /> <span>Update</span></a></li>
					<li title="{lang}wcf.acp.package.view.button.uninstall{/lang}" class="separator jsTooltip"><img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" class="uninstallButton" data-object-id="{@$package->packageID}" /> <span>Delete</span></li>
				</ul>
			</nav>
		</footer>
	</li>
{/foreach}
