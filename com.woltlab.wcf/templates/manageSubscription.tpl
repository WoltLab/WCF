<div class="section">
	<dl class="wide">
		<dd>
			<label><input type="radio" name="subscribe" value="1"{if $userObjectWatch} checked{/if}> {lang}wcf.user.objectWatch.subscribe.{@$objectType->objectType}{/lang}</label>
			
			<small><label><input type="checkbox" name="enableNotification" value="1"{if $userObjectWatch && $userObjectWatch->notification} checked{/if}> {lang}wcf.user.objectWatch.enableNotification.{@$objectType->objectType}{/lang}</label></small>
		</dd>
	</dl>
	<dl class="wide">
		<dd>
			<label><input type="radio" name="subscribe" value="0"{if !$userObjectWatch} checked{/if}> {lang}wcf.user.objectWatch.unsubscribe.{@$objectType->objectType}{/lang}</label>
		</dd>
	</dl>
	
	{event name='fields'}
</div>

<div class="formSubmit">
	<button class="jsButtonSave buttonPrimary">{lang}wcf.global.button.save{/lang}</button>
</div>