{foreach from=$plugins key=packageID item=package}
	<li class="infoPackagePlugin">
		<div>
			<a href="index.php/PackageView/{@$packageID}{@SID_ARG_1ST}" title="{$package->getName()}" style="background-image: url('{@RELATIVE_WCF_DIR}icon/packagePlugin1.svg');">
				<h1>{$package->getName()}</h1>
				<small>{$package->packageDescription|truncate:300}</small>
			</a>
		</div>
		
		<footer>
			<nav>
				<ul class="smallButtonBar">
					<li><a href="index.php/PackageView/{@$packageID}{@SID_ARG_1ST}" title="{$package->getName()}" class="balloonTooltip"><img src="{@RELATIVE_WCF_DIR}icon/info1.svg" alt="" /> <span>Update</span></a></li>
					<li><a href="index.php?form=PackageStartInstall&amp;action=update&amp;packageID={@$packageID}{@SID_ARG_2ND}" title="{lang}wcf.acp.package.view.button.update{/lang}" class="balloonTooltip"><img src="{@RELATIVE_WCF_DIR}icon/update1.svg" alt="" /> <span>Update</span></a></li>
					<li title="{lang}wcf.acp.package.view.button.uninstall{/lang}" class="balloonTooltip separator"><img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" class="uninstallButton" data-object-id="{@$package->packageID}" /> <span>Delete</span></li>
				</ul>
			</nav>
		</footer>
	</li>
{/foreach}
