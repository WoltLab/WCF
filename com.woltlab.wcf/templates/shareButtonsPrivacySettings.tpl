<small style="display: inline-block; max-width: 500px;">{lang}wcf.message.share.privacy.description{/lang}</small>

<ul class="marginTop">
	<li><label><input type="checkbox" name="facebook" value="1"{if $settings[facebook]} checked="checked"{/if} /> {lang}wcf.message.share.facebook{/lang}</label></li>
	<li><label><input type="checkbox" name="twitter" value="1"{if $settings[twitter]} checked="checked"{/if} /> {lang}wcf.message.share.twitter{/lang}</label></li>
	<li><label><input type="checkbox" name="google" value="1"{if $settings[google]} checked="checked"{/if} /> {lang}wcf.message.share.google{/lang}</label></li>
	<li><label><input type="checkbox" name="reddit" value="1"{if $settings[reddit]} checked="checked"{/if} /> {lang}wcf.message.share.reddit{/lang}</label></li>
</ul>

<div class="formSubmit">
	<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
</div>
