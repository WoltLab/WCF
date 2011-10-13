{foreach from=$plugins key=packageID item=package}
	<li class="package" style="border: 1px solid rgb(192, 192, 192); display: inline-block; text-align: center; width: 150px;">
		<a href="index.php/PackageView/{@$packageID}{@SID_ARG_1ST}">
			<span style="border-bottom: 1px solid rgb(192, 192, 192); display: block;">{$package->getName()}</span>
			<img src="http://s-ak.buzzfed.com/static/enhanced/web05/2011/3/18/1/enhanced-buzz-15854-1300424464-1.jpg" alt="" style="width: 100px;" />
		</a>
		<ul style="text-align: right;">
			<li style="display: inline-block;"><a href="index.php?form=PackageInstallation&amp;action=update&amp;packageID={@$packageID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/update1.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" class="balloonTooltip" /></a></li>
			<li style="display: inline-block;"><img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" title="{lang}wcf.acp.package.view.button.uninstall{/lang}" class="uninstallButton balloonTooltip" data-objectID="{@$package->packageID}" /></li>
		</ul>
	</li>
{/foreach}
