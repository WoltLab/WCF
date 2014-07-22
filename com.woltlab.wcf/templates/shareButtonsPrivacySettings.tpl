<fieldset>
	<legend>{lang}wcf.message.share.privacy{/lang}</legend>
	<small style="max-width: 500px;">{lang}wcf.message.share.privacy.description{/lang}</small>
	
	<dl class="wide">
		<dt></dt>
		<dd><label><input type="checkbox" name="facebook" value="1"{if $settings[facebook]} checked="checked"{/if} /> {lang}wcf.message.share.facebook{/lang}</label></dd>
	</dl>
	<dl class="wide">
		<dt></dt>
		<dd><label><input type="checkbox" name="twitter" value="1"{if $settings[twitter]} checked="checked"{/if} /> {lang}wcf.message.share.twitter{/lang}</label></dd>
	</dl>
	<dl class="wide">
		<dt></dt>
		<dd><label><input type="checkbox" name="google" value="1"{if $settings[google]} checked="checked"{/if} /> {lang}wcf.message.share.google{/lang}</label></dd>
	</dl>
	<dl class="wide">
		<dt></dt>
		<dd><label><input type="checkbox" name="reddit" value="1"{if $settings[reddit]} checked="checked"{/if} /> {lang}wcf.message.share.reddit{/lang}</label></dd>
	</dl>
</fieldset>

<div class="formSubmit">
	<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
</div>
