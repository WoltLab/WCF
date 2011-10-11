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
			<h1>installed applications</h1>
		</hgroup>

		{foreach from=$applications key=packageID item=package}
			<fieldset>
				<legend>{$package->getName()}</legend>

				&lt;gimme sum icon&gt;

				<dl>
					<dt>package</dt>
					<dd>{$package->package}</dd>
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

				<div style="text-align: right;">
					<ul>
						<li style="display: inline-block;"><a href="index.php/PackageView/{@$packageID}/{@SID_ARG_1ST}">details</a></li>
						<li style="display: inline-block;"><a href="index.php/PackageStartInstall/{@$packageID}/?action=update{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/update1.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" class="balloonTooltip" /></a></li>
					</ul>
				</div>
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
			<li><a href="index.php/DetailedPackageList/{@SID_ARG_1ST}">detailed package list</a></li>
		</ul>
	</nav>
</div>

{include file='footer'}
