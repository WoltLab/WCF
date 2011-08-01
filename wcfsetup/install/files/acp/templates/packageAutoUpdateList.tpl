{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	var checkedAll = true;
	function checkUncheckAllPackages(parent) {
		var inputs = parent.getElementsByTagName('input');
		for (var i = 0, j = inputs.length; i < j; i++) {
			if (inputs[i].getAttribute('type') == 'checkbox') {
				inputs[i].checked = checkedAll;
			}
		}
		
		var selects = parent.getElementsByTagName('select');
		for (var i = 0, j = selects.length; i < j; i++) {
			selects[i].disabled = !checkedAll;
		}
		
		checkedAll = (checkedAll) ? false : true;
	}
	//]]>
</script>

<form method="post" action="index.php?form=PackageUpdate" id="updateForm">
	<header class="mainHeading">
		<img src="{@RELATIVE_WCF_DIR}icon/packageUpdateL.png" alt="" />
		<hgroup>
			<h1>{lang}wcf.acp.packageUpdate{/lang}</h1>
			{if $availableUpdates|count}
				<h2><label><input type="checkbox" onclick="checkUncheckAllPackages(document.getElementById('updateForm'))" /> {lang}wcf.acp.packageUpdate.selectAll{/lang}</label></h2>
			{/if}
		</hgroup>
	</header>
	
	{if !$availableUpdates|count}
		<div class="border content">
			<div class="container-1">
				<p class="info">{lang}wcf.acp.packageUpdate.noneAvailable{/lang}</p>
			</div>
		</div>
	{else}
		{foreach from=$availableUpdates item=availableUpdate}
			<article class="message content"{if $availableUpdate.version.updateType == 'security'} style="border-color: #c00"{/if}>
				<div class="messageInner container-{cycle name='styles' values='1,2'}">
					<h1 class="subHeading">
						<label>
							<input type="checkbox" name="updates[{@$availableUpdate.packageID}]" onclick="enableFormElements(document.getElementById('version-{@$availableUpdate.packageID}Div'), this.checked)" value="{$availableUpdate.version.packageVersion}" />
							{$availableUpdate.packageName}{if $availableUpdate.instanceNo > 1} (#{#$availableUpdate.instanceNo}){/if}
						</label>
					</h1>

					<div class="messageBody">
						<div class="formElement">
							<div class="formFieldLabel">
								<label>{lang}wcf.acp.packageUpdate.currentVersion{/lang}</label>
							</div>
							<div class="formField">
								<span>{$availableUpdate.packageVersion}</span>
							</div>
						</div>
						
						<div id="version-{@$availableUpdate.packageID}Div" class="formElement">
							<div class="formFieldLabel">
								<label for="version-{@$availableUpdate.packageID}">{lang}wcf.acp.packageUpdate.updateVersion{/lang}</label>
							</div>
							<div class="formField">
								<select id="version-{@$availableUpdate.packageID}" name="updates[{@$availableUpdate.packageID}]" disabled="disabled">
									{foreach from=$availableUpdate.versions item=$version}
										<option value="{$version.packageVersion}"{if $version.packageVersion == $availableUpdate.version.packageVersion} selected="selected"{/if}>{$version.packageVersion}</option>
									{/foreach}
								</select>
							</div>
						</div>
						
						{if $availableUpdate.author}
							<div class="formElement">
								<div class="formFieldLabel">
									<label>{lang}wcf.acp.package.list.author{/lang}</label>
								</div>
								<div class="formField">
									<span>{if $availableUpdate.authorURL}<a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$availableUpdate.authorURL|rawurlencode}" class="externalURL">{$availableUpdate.author}</a>{else}{$availableUpdate.author}{/if}</span>
								</div>
							</div>
						{/if}
						
						{if $availableUpdate.packageDescription}
							<div class="formElement">
								<p class="formFieldLabel">{lang}wcf.acp.package.description{/lang}</p>
								<p class="formField">{$availableUpdate.packageDescription}</p>
							</div>
						{/if}
							
					</div>

					<hr />
				</div>
			</article>			
		{/foreach}
		
		<div class="formSubmit">
			<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
			{@SID_INPUT_TAG}
	 	</div>
	{/if}
</form>

{include file='footer'}
