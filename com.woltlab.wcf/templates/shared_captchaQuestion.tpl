{if !$captchaQuestionAnswered}
	<dl class="{if (($errorType|isset && $errorType|is_array && $errorType[captchaAnswer]|isset) || ($errorField|isset && $errorField == 'captchaAnswer'))} formError{/if}">
		<dt><label for="captchaAnswer">{$captchaQuestionObject->getQuestion()}</label></dt>
		<dd>
			<input type="text" id="captchaAnswer" name="captchaAnswer" class="long">
			{if (($errorType|isset && $errorType|is_array && $errorType[captchaAnswer]|isset) || ($errorField|isset && $errorField == 'captchaAnswer'))}
				{if $errorType|is_array && $errorType[captchaAnswer]|isset}
					{assign var='__errorType' value=$errorType[captchaAnswer]}
				{else}
					{assign var='__errorType' value=$errorType}
				{/if}
				
				{if $__errorType == 'empty'}
					<small class="innerError">{lang}wcf.global.form.error.empty{/lang}</small>
				{else}
					<small class="innerError">{lang}wcf.captcha.question.answer.error.{$__errorType}{/lang}</small>
				{/if}
			{/if}
			<small>{lang}wcf.captcha.question.captcha.description{/lang}</small>
			<input type="hidden" name="captchaQuestion" value="{$captchaQuestion}">
		</dd>
	</dl>
		
	{if !$ajaxCaptcha|empty}
		<script data-relocate="true">
			$(function() {
				WCF.System.Captcha.addCallback('{$captchaID}', function() {
					return {
						captchaAnswer: $('#captchaAnswer').val(),
						captchaQuestion: '{$captchaQuestion}'
					};
				});
			});
		</script>
	{/if}
{else}
	<input type="hidden" name="captchaQuestion" value="{$captchaQuestion}">
{/if}
