{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();

		WCF.Language.add('wcf.acp.package.view.button.uninstall.sure', 'wcf.acp.package.view.button.uninstall.sure');
		new WCF.ACP.Package.Uninstallation($('.package .uninstallButton'));

		{if $pluginsCount > 1}
			WCF.Icon.addObject({
				'wcf.icon.arrow.down': '{@RELATIVE_WCF_DIR}icon/dropdown1.svg',
				'wcf.icon.next': '{@RELATIVE_WCF_DIR}icon/next1.svg',
				'wcf.icon.next.disabled': '{@RELATIVE_WCF_DIR}icon/next1D.svg',
				'wcf.icon.previous': '{@RELATIVE_WCF_DIR}icon/previous1.svg',
				'wcf.icon.previous.disabled': '{@RELATIVE_WCF_DIR}icon/previous1D.svg'
			});
			new WCF.ACP.Package.List({@($pluginsCount / 20)|ceil});
		{/if}
	});
	//]]>
</script>

<header class="wcf-mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/packageApplication1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.package.list{/lang}</h1>
	</hgroup>
</header>

<div class="wcf-tabMenuContainer">
	<nav class="wcf-tabMenu">
		<ul>
			<li><a href="#applications" title="applications">applications</a></li>
			{if $plugins|count}<li><a href="#plugins" title="plugins">plugins</a></li>{/if}
		</ul>
	</nav>
	
	<div id="applications" class="wcf-border wcf-tabMenuContent hidden">
		<hgroup class="wcf-subHeading">
			<h1>Installed Applications</h1>
		</hgroup>
		
		<ol class="wcf-applicationList">
			{foreach from=$applications key=packageID item=package}
				<li class="wcf-infoPackageApplication">
					<fieldset>
						<legend>{$package->getName()}</legend>
						
						<img src="{@RELATIVE_WCF_DIR}icon/wcfIcon1.svg" alt="" title="{$package->getName()}" class="wcf-packageApplicationIcon" />
						
						<div>
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
						</div>
						
						<footer>
							<nav>
								<ul class="wcf-smallButtons">
									<li><a href="{link controller='PackageView' id=$packageID}{/link}" class="wcf-button"><img src="{@RELATIVE_WCF_DIR}icon/info1.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" /> <span>Details</span></a></li>
									<li><a href="{link controller='PackageStartInstall' id=$packageID}action=update{/link}" class="wcf-button"><img src="{@RELATIVE_WCF_DIR}icon/update1.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" /> <span>Update</span></a></li>
								</ul>
							</nav>
						</footer>
					</fieldset>
				</li>
			{/foreach}
		</ol>
	</div>
	
	{hascontent}
		<div id="plugins" class="wcf-border wcf-tabMenuContent hidden">
			<hgroup class="wcf-subHeading">
				<h1>Installed Plugins</h1>
			</hgroup>
			
			<div class="wcf-contentHeader jsPluginListPagination">
				
			</div>
			
			<section>
				<ol class="wcf-pluginList">
					{content}
						{include file='packageListPlugins'}
					{/content}
				</ol>
			</section>
			
			<div class="wcf-contentFooter jsPluginListPagination">
				
			</div>
		</div>
	{/hascontent}
</div>

<div class="wcf-contentFooter">
	<nav>
		<ul class="wcf-largeButtons">
			<li><a href="{link controller='PackageListDetailed'}{/link}" title="{lang}wcf.acp.menu.link.package.list{/lang}" class="wcf-button"><img src="{@RELATIVE_WCF_DIR}icon/packageApplication1.svg" alt="" /> <span>detailed package list</span></a></li>
		</ul>
	</nav>
</div>

{include file='footer'}
