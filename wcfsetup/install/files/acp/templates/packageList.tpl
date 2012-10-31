{include file='header' pageTitle='wcf.acp.package.list'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
		
		WCF.Language.add('wcf.acp.package.view.button.uninstall.sure', 'wcf.acp.package.view.button.uninstall.sure');
		new WCF.ACP.Package.Uninstallation($('.jsPackageContainer .jsUninstallButton'));
		
		{if $pluginsCount > 1}
			new WCF.ACP.Package.List({@($pluginsCount / 20)|ceil});
		{/if}
	});
	//]]>
</script>

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.package.list{/lang}</h1>
	</hgroup>
</header>

<div class="tabMenuContainer">
	<nav class="tabMenu">
		<ul>
			<li><a href="#applications" title="applications">{lang}wcf.acp.package.application.title{/lang}</a></li>
			{if $plugins|count}<li><a href="#plugins" title="plugins">{lang}wcf.acp.package.plugin.title{/lang}</a></li>{/if}
		</ul>
	</nav>
	
	<div id="applications" class="container containerPadding tabMenuContent hidden">
		<ol class="applicationList">
			{foreach from=$applications key=packageID item=package}
				<li>
					<fieldset>
						<legend>{$package->getName()}</legend>
						
						<div class="box96">
							<img src="{@$__wcf->getPath()}icon/wcfIcon1.svg" alt="" title="{$package->getName()}" class="icon96" />
							<dl>
								<dt>{lang}wcf.acp.package.identifier{/lang}</dt>
								<dd>{$package->package}</dd>
							</dl>
							<dl>
								<dt>{lang}wcf.acp.package.version{/lang}</dt>
								<dd>{$package->packageVersion}</dd>
							</dl>
							<dl>
								<dt>{lang}wcf.acp.package.packageDate{/lang}</dt>
								<dd>{$package->packageDate|date}</dd>
							</dl>
							<dl>
								<dt>{lang}wcf.acp.package.installDate{/lang}</dt>
								<dd>{@$package->installDate|time}</dd>
							</dl>
							<dl>
								<dt>{lang}wcf.acp.package.updateDate{/lang}</dt>
								<dd>{@$package->updateDate|time}</dd>
							</dl>
							<dl>
								<dt>{lang}wcf.acp.package.author{/lang}</dt>
								<dd>{if $package->authorURL}<a href="dereferrer.php?url={$package->authorURL|rawurlencode}">{/if}{$package->author}{if $package->authorURL}</a>{/if}</dd>
							</dl>
							
							<footer class="contentOptions clearfix">
								<nav>
									<ul class="smallButtons">
										<li><a href="{link controller='PackageView' id=$packageID}{/link}" class="button"><img src="{@$__wcf->getPath()}icon/info.svg" alt="" title="{lang}wcf.acp.package.button.info{/lang}" class="icon16" /> <span>{lang}wcf.acp.package.button.info{/lang}</span></a></li>
										<li><a href="{link controller='PackageStartInstall' id=$packageID}action=update{/link}" class="button"><img src="{@$__wcf->getPath()}icon/update.svg" alt="" title="{lang}wcf.acp.package.button.update{/lang}" class="icon16" /> <span>{lang}wcf.acp.package.button.update{/lang}</span></a></li>
									</ul>
								</nav>
							</footer>
						</div>
					</fieldset>
				</li>
			{/foreach}
		</ol>
	</div>
	
	{hascontent}
		<div id="plugins" class="container containerPadding tabMenuContent hidden">
			<div class="contentHeader jsPluginListPagination">
				
			</div>
			
			<section>
				<ol class="pluginList">
					{content}
						{include file='packageListPlugins'}
					{/content}
				</ol>
			</section>
			
			<div class="contentFooter jsPluginListPagination">
				
			</div>
		</div>
	{/hascontent}
</div>

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='PackageListDetailed'}{/link}" title="{lang}wcf.acp.menu.link.package.list{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/list.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.package.list.detailed{/lang}</span></a></li>
		</ul>
	</nav>
</div>

{include file='footer'}
