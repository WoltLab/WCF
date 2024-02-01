{include file='header' pageTitle='wcf.acp.captcha.question.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.captcha.question.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='CaptchaQuestionList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.captcha.question.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formNotice'}

<form id="adForm" method="post" action="{if $action == 'add'}{link controller='CaptchaQuestionAdd'}{/link}{else}{link controller='CaptchaQuestionEdit' id=$captchaQuestion->questionID}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'question'} class="formError"{/if}>
			<dt><label for="question">{lang}wcf.acp.captcha.question.question{/lang}</label></dt>
			<dd>
				<input type="text" id="question" name="question" value="{$i18nPlainValues[question]}" required autofocus class="long">
				{if $errorField == 'question'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'multilingual'}
							{lang}wcf.global.form.error.multilingual{/lang}
						{else}
							{lang}wcf.acp.captcha.question.question.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		{include file='shared_multipleLanguageInputJavascript' elementIdentifier='question' forceSelection=false}
		
		<dl{if $errorField == 'answers'} class="formError"{/if}>
			<dt><label for="answers">{lang}wcf.acp.captcha.question.answers{/lang}</label></dt>
			<dd>
				<textarea id="answers" name="answers" cols="40" rows="10">{$i18nPlainValues[answers]}</textarea>
				<small>{lang}wcf.acp.captcha.question.answers.description{/lang}</small>
				{if $errorField == 'answers'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'multilingual'}
							{lang}wcf.global.form.error.multilingual{/lang}
						{else}
							{lang}wcf.acp.captcha.question.answers.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		{include file='shared_multipleLanguageInputJavascript' elementIdentifier='answers' forceSelection=false}
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="isDisabled" value="1"{if $isDisabled} checked{/if}> {lang}wcf.acp.captcha.question.isDisabled{/lang}</label>
			</dd>
		</dl>
		
		{event name='dataFields'}
	</div>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
