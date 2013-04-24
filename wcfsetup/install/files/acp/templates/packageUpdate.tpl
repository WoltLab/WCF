{include file='header' pageTitle='wcf.acp.packageUpdate'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.acp.package.update.unauthorized': '{lang}wcf.acp.package.update.unauthorized{/lang}'
		})
		
		new WCF.ACP.Package.Update.Manager();
	});
	//]]>
</script>

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.packageUpdate{/lang}</h1>
	</hgroup>
</header>

<div class="container containerPadding marginTop">
	{foreach from=$availableUpdates item=update}
		<fieldset class="jsPackageUpdate" data-package="{$update[package]}">
			<legend><label>
				<input type="checkbox" value="1" checked="checked" />
				{$update[packageName]|language}
			</label></legend>
			{if $update[packageDescription]}<small>{$update[packageDescription]|language}</small>{/if}
			
			<dl{if $update[packageDescription]} class="marginTop"{/if}>
				<dt>
					{lang}wcf.acp.package.installedVersion{/lang}
				</dt>
				<dd>
					{$update[packageVersion]} ({$update[packageDate]|date})
				</dd>
			</dl>
			<dl>
				<dt>
					{lang}wcf.acp.package.availableVersions{/lang}
				</dt>
				<dd>
					<select>
						{foreach from=$update[versions] item=version}
							<option value="{@$version[packageVersion]}">{$version[packageVersion]}</option>
						{/foreach}
					</select>
				</dd>
			</dl>
		</fieldset>
	{/foreach}
</div>

<div class="formSubmit">
	<button>{lang}wcf.global.button.submit{/lang}</button>
</div>

{include file='footer'}
