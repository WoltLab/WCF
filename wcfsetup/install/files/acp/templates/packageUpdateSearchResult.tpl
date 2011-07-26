{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/packageSearchL.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.packageUpdate.search{/lang}</h1>
	</hgroup>
</header>

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=PackageUpdateSearchResult&searchID=$searchID&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:SID_ARG_2ND_NOT_ENCODED}
</div>

<form method="post" action="index.php?form=PackageUpdate">
	{foreach from=$packages item=package}
		<div class="message content">
			<div class="messageInner container-{cycle name='styles' values='1,2'}">
				<h3 class="subHeadline">
					{if $package.standalone == 1}
						<img src="{@RELATIVE_WCF_DIR}icon/packageTypeStandaloneS.png" alt="" title="{lang}wcf.acp.package.list.standalone{/lang}" />
					{elseif $package.plugin != ''}
						<img src="{@RELATIVE_WCF_DIR}icon/packageTypePluginS.png" alt="" title="{lang}wcf.acp.package.list.plugin{/lang}" />
					{else}
						<img src="{@RELATIVE_WCF_DIR}icon/packageS.png" alt="" title="{lang}wcf.acp.package.list.other{/lang}" />
					{/if}
					{$package.packageName}
				</h3>

				<div class="messageBody">
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="packageVersion-{$package.package}">{lang}wcf.acp.package.list.version{/lang}</label>
						</div>
						<div class="formField">
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
						</div>
					</div>
					
					{if $package.author != ''}
						<div class="formElement">
							<div class="formFieldLabel">
								<label>{lang}wcf.acp.package.list.author{/lang}</label>
							</div>
							<div class="formField">
								<span>{if $package.authorURL}<a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$package.authorURL|rawurlencode}" class="externalURL">{$package.author}</a>{else}{$package.author}{/if}</span>
							</div>
						</div>
					{/if}
					
					{if $package.packageDescription}
						<div class="formElement">
							<p class="formFieldLabel">{lang}wcf.acp.package.description{/lang}</p>
							<p class="formField">{$package.packageDescription}</p>
						</div>
					{/if}
					
					<fieldset>
						<legend>{lang}wcf.acp.packageUpdate.options{/lang}</legend>
					
						<div class="formField">
							<ul class="formOptionsLong">
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
		</div>			
	{/foreach}
	
	<div class="formSubmit">
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
		{@SID_INPUT_TAG}
		<input type="hidden" name="searchID" value="{@$searchID}" />
 	</div>
</form>

<div class="contentFooter">
	{@$pagesLinks}
</div>

{include file='footer'}
