{include file='header'}

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/search1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.packageUpdate.search{/lang}</h1>
	</hgroup>
</header>

<div class="contentHeader">
	{pages print=true assign=pagesLinks controller="PackageUpdateSearchResult" id=$searchID link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
</div>

<form method="post" action="{link controller='PackageUpdate'}{/link}">
	{foreach from=$packages item=package}
		<article class="message content">
			<div class="messageInner container-{cycle name='styles' values='1,2'}">
				<hgroup class="subHeading">
					<h1>
						{if $package.isApplication == 1}
							<img src="{@RELATIVE_WCF_DIR}icon/packageApplication1.svg" alt="" title="{lang}wcf.acp.package.list.isApplication{/lang}" class="balloonTooltip" />
						{elseif $package.plugin != ''}
							<img src="{@RELATIVE_WCF_DIR}icon/packagePlugin1.svg" alt="" title="{lang}wcf.acp.package.list.plugin{/lang}" class="balloonTooltip" />
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/package1.svg" alt="" title="{lang}wcf.acp.package.list.other{/lang}" class="balloonTooltip" />
						{/if}
						{$package.packageName}
					</h1>
				<hgroup>

				<div class="messageBody">
					<dl>
						<dt><label for="packageVersion-{$package.package}">{lang}wcf.acp.package.list.version{/lang}</label></dt>
						<dd>
							<select id="packageVersion-{$package.package}">
								{foreach from=$package.packageVersions item=$packageVersion}
									<option value="{$packageVersion}"{if $packageVersion == $package.packageVersion} selected="selected"{/if}>{$packageVersion}</option>
								{/foreach}
							</select>
							<script type="text/javascript">
								//<![CDATA[
								onloadEvents.push(function() {
									document.getElementById('packageVersion-{$package.package|encodeJS}').onchange = function() {
										// get value
										var select = document.getElementById('packageVersion-{$package.package|encodeJS}');
										var packageVersion = select.options[select.selectedIndex].value;
										
										// set value
										{if !$package.isUnique}document.getElementById('updates-{$package.package}').value = packageVersion;{/if}
										{foreach from=$package.updatableInstances item=updatableInstance}
											document.getElementById('updates-{$updatableInstance.packageID}').value = packageVersion;
										{/foreach}
									}
								});
								//]]>
							</script>
						</dd>
					</dl>
					
					{if $package.author != ''}
						<dl>
							<dt><label>{lang}wcf.acp.package.list.author{/lang}</label></dt>
							<dd>
								<span>{if $package.authorURL}<a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$package.authorURL|rawurlencode}" class="externalURL">{$package.author}</a>{else}{$package.author}{/if}</span>
							</dd>
						</dl>
					{/if}
					
					{if $package.packageDescription}
						<dl>
							<dt>{lang}wcf.acp.package.description{/lang}</dt>
							<dd>{$package.packageDescription}</dd>
						</dl>
					{/if}
					
					<fieldset>
						<legend>{lang}wcf.acp.packageUpdate.options{/lang}</legend>
					
						<div><!-- ToDo: Definition List -->
							<ul>
								{* new installation *}
								{if $package.isUnique && !$package.updatableInstances|count}
									<li>{lang}wcf.acp.packageUpdate.options.alreadyInstalledUnique{/lang}</li>
								{/if}
								{if !$package.isUnique}
									<li><label><input type="checkbox" id="updates-{$package.package}" name="updates[{$package.package}]" value="{$package.packageVersion}" {if $selectedPackages[$package.package]|isset}checked="checked" {/if}/> {if $package.instances}{lang}wcf.acp.packageUpdate.options.installAlreadyInstalled{/lang}{else}{lang}wcf.acp.packageUpdate.options.install{/lang}{/if}</label></li>
								{/if}
								
								{* update *}
								{foreach from=$package.updatableInstances item=updatableInstance}
									<li><label><input type="checkbox" id="updates-{$updatableInstance.packageID}" name="updates[{$updatableInstance.packageID}]" value="{$package.packageVersion}" {if $selectedPackages[$updatableInstance.packageID]|isset}checked="checked" {/if}/> {lang}wcf.acp.packageUpdate.options.update{/lang}</label></li>
								{/foreach}
							</<ul>
  						</div>
					</fieldset>
					
				</div>
				<hr />
			</div>
		</article>			
	{/foreach}
	
	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
		<input type="hidden" name="id" value="{@$searchID}" />
 	</div>
</form>

<div class="contentFooter">
	{@$pagesLinks}
</div>

{include file='footer'}
