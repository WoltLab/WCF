{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();

		WCF.Language.add('wcf.acp.package.view.button.uninstall.sure', 'wcf.acp.package.view.button.uninstall.sure');
		new WCF.ACP.PackageUninstallation($('.package .uninstallButton'));

		{if $pluginsCount > 1}
			new WCF.ACP.Package.List({@($pluginsCount / 1)|ceil});
		{/if}
	});
	//]]>
</script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/packageStandalone1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.package.list{/lang}</h1>
	</hgroup>
</header>

<div class="tabMenuContainer">
	<nav class="tabMenu">
		<ul>
			<li><a href="#applications" title="applications">applications</a></li>
			{if $plugins|count}<li><a href="#plugins" title="plugins">plugins</a></li>{/if}
		</ul>
	</nav>
	
	<div id="applications" class="border tabMenuContent hidden">
		<hgroup class="subHeading">
			<h1>Installed Applications</h1>
		</hgroup>
		
		{foreach from=$applications key=packageID item=package}
			<fieldset class="infoPackageStandalone">
				<legend>{$package->getName()}</legend>
				
				<img src="{@RELATIVE_WCF_DIR}icon/wcfIcon1.svg" alt="" title="{$package->getName()}" class="packageStandaloneIcon" />
				
				<section>
					<dl>
						<dt>package</dt>
						<dd><span class="badge badgeNote">{$package->package}</span></dd>
					</dl>
					<dl>
						<dt>installed version</dt>
						<dd>{$package->packageVersion}</dd>
					</dl>
					<dl>
						<dt>create date</dt>
						<dd>{$package->packageDate|date}</dd>
					</dl>
					<dl>
						<dt>install date</dt>
						<dd>{@$package->installDate|time}</dd>
					</dl>
					<dl>
						<dt>update date</dt>
						<dd>{@$package->updateDate|time}</dd>
					</dl>
					<dl>
						<dt>creator</dt>
						<dd>{if $package->authorURL}<a href="dereferrer.php?url={$package->authorURL|rawurlencode}">{/if}{$package->author}{if $package->authorURL}</a>{/if}</dd>
					</dl>
				</section>
				
				<footer>
					<nav>
						<ul class="smallButtons">
							<li><a href="{link controller='PackageView' id=$packageID}{/link}"><img src="{@RELATIVE_WCF_DIR}icon/info1.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" class="bal loonTooltip" /> Details</a></li>
							<li><a href="{link controller='PackageStartInstall' id=$packageID}action=update{/link}"><img src="{@RELATIVE_WCF_DIR}icon/update1.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" class="bal loonTooltip" /> Update</a></li>
						</ul>
					</nav>
				</footer>
			</fieldset>
		{/foreach}
	</div>
	
	{hascontent}
		<div id="plugins" class="border tabMenuContent hidden">
			<hgroup class="subHeading">
				<h1>installed plugins</h1>
			</hgroup>
			
			<div class="pluginList"></div>
			
			<ol>
				{content}
					{include file='packageListPlugins'}
				{/content}
			</ol>
			
			<div class="pluginList"></div>
		</div>
	{/hascontent}
</div>

<div class="contentFooter">
	<nav class="largeButtons">
		<ul>
			<li><a href="{link controller='DetailedPackageList'}{/link}">detailed package list</a></li>
		</ul>
	</nav>
</div>

{include file='footer'}
