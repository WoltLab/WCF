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

<div class="tabMenuContainer">
	<nav class="tabMenu">
		<ul>
			<li><a href="#applications" title="applications">applications</a></li>
			{if $plugins|count}<li><a href="#plugins" title="plugins">plugins</a></li>{/if}
		</ul>
	</nav>
	
	<div id="applications" class="wcf-border tabMenuContent hidden">
		<hgroup class="wcf-subHeading">
			<h1>Installed Applications</h1>
		</hgroup>
		
		{foreach from=$applications key=packageID item=package}
			<fieldset class="infoPackageApplication">
				<legend>{$package->getName()}</legend>
				
				<img src="{@RELATIVE_WCF_DIR}icon/wcfIcon1.svg" alt="" title="{$package->getName()}" class="packageApplicationIcon" />
				
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
						<ul class="smallButtons">
							<li><a href="{link controller='PackageView' id=$packageID}{/link}" class="button"><img src="{@RELATIVE_WCF_DIR}icon/info1.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" /> <span>Details</span></a></li>
							<li><a href="{link controller='PackageStartInstall' id=$packageID}action=update{/link}" class="button"><img src="{@RELATIVE_WCF_DIR}icon/update1.svg" alt="" title="{lang}wcf.acp.package.view.button.update{/lang}" /> <span>Update</span></a></li>
						</ul>
					</nav>
				</footer>
			</fieldset>
		{/foreach}
	</div>
	
	{hascontent}
		<div id="plugins" class="wcf-border tabMenuContent hidden">
			<hgroup class="wcf-subHeading">
				<h1>Installed Plugins</h1>
			</hgroup>
			
			<div class="wcf-contentHeader">
				
			</div>
			
			<section>
				<ol class="packageListPlugin">
					{content}
						{include file='packageListPlugins'}
					{/content}
				</ol>
			</section>
			
			<div class="wcf-contentFooter">
				
			</div>
		</div>
	{/hascontent}
</div>

<div class="wcf-contentFooter">
	<nav>
		<ul class="largeButtons">
			<li><a href="{link controller='PackageListDetailed'}{/link}" title="{lang}wcf.acp.menu.link.package.list{/lang}" class="button"><img src="{@RELATIVE_WCF_DIR}icon/packageApplication1.svg" alt="" /> <span>detailed package list</span></a></li>
		</ul>
	</nav>
</div>

{include file='footer'}
