{include file='header'}

<form method="post" action="install.php">
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.global.license{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.global.license.description{/lang}</p>
		</header>
		
		<dl{if $missingAcception|isset} class="formError"{/if}>
			<dt></dt>
			<dd>
				<textarea rows="20" cols="40" readonly="readonly" autofocus="autofocus" id="license">{$license}</textarea>
				<label><input type="checkbox" name="accepted" value="1" /> {lang}wcf.global.license.accept.description{/lang}</label>
				{if $missingAcception|isset}
					<small class="innerError">
						{lang}wcf.global.license.missingAcception{/lang}
					</small>
				{/if}
			</dd>
		</dl>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.next{/lang}" accesskey="s" />
			<input type="hidden" name="send" value="1" />
			<input type="hidden" name="step" value="{@$nextStep}" />
			<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
			<input type="hidden" name="languageCode" value="{@$languageCode}" />
			<input type="hidden" name="dev" value="{@$developerMode}" />
		</div>
	</section>
</form>

{include file='footer'}
