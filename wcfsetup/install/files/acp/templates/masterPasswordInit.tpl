{include file='header' pageTitle='wcf.acp.masterPassword.init'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		$('#setPassword').click(function() {
			$('input[type="password"]').val($.proxy(function(index, element) {
				return $(this).text();
			}, this));
		});
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.masterPassword.init{/lang}</h1>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="{link controller='MasterPasswordInit'}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.acp.masterPassword.init{/lang}</legend>
			
			<dl{if $errorField == 'masterPassword'} class="formError"{/if}>
				<dt><label for="masterPassword">{lang}wcf.acp.masterPassword{/lang}</label></dt>
				<dd>
					<input type="password" id="masterPassword" name="masterPassword" value="{$masterPassword}" class="medium" />
					{if $errorField == 'masterPassword'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.masterPassword.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.masterPassword.init.description{/lang}</small>
				</dd>
			</dl>
			
			<dl{if $errorField == 'confirmMasterPassword'} class="formError"{/if}>
				<dt><label for="confirmMasterPassword">{lang}wcf.acp.masterPassword.confirm{/lang}</label></dt>
				<dd>
					<input type="password" id="confirmMasterPassword" name="confirmMasterPassword" value="{$confirmMasterPassword}" class="medium" />
					{if $errorField == 'confirmMasterPassword'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.masterPassword.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl>
				<dt><label for="exampleMasterPassword">{lang}wcf.acp.masterPassword.example{/lang}</label></dt>
				<dd>
					<p><a class="jsTooltip" id="setPassword" title="{lang}wcf.acp.masterPassword.example.set{/lang}">{@$exampleMasterPassword}</a></p>
					<input type="hidden" id="exampleMasterPassword" name="exampleMasterPassword" value="{@$exampleMasterPassword}" />
				</dd>
			</dl>
			
			{event name='initFields'}
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" name="submitButton" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		<input type="hidden" name="url" value="{$url}" />
	</div>
</form>

{include file='footer'}
