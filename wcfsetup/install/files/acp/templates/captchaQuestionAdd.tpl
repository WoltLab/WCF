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

{@$form->getHtml()}

{include file='footer'}
