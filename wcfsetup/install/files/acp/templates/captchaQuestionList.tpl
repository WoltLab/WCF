{include file='header' pageTitle='wcf.acp.captcha.question.list'}

<script data-relocate="true">
	$(function() {
		new WCF.Action.Delete('wcf\\data\\captcha\\question\\CaptchaQuestionAction', '.jsQuestionRow');
		new WCF.Action.Toggle('wcf\\data\\captcha\\question\\CaptchaQuestionAction', '.jsQuestionRow');
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.captcha.question.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='CaptchaQuestionAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.captcha.question.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="CaptchaQuestionList" link="pageNo=%d"}{/content}
	</div>
{/hascontent}

{hascontent}
	<div id="captchaQuestionTabelContainer" class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnQuestionID active ASC" colspan="2">{lang}wcf.global.objectID{/lang}</th>
					<th class="columnText columnQuestion">{lang}wcf.acp.captcha.question.question{/lang}</th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{content}
					{foreach from=$objects item='question'}
						<tr class="jsQuestionRow">
							<td class="columnIcon">
								<span class="icon icon16 fa-{if !$question->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $question->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$question->questionID}"></span>
								<a href="{link controller='CaptchaQuestionEdit' id=$question->questionID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
								<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$question->questionID}" data-confirm-message-html="{lang __encode=true}wcf.acp.captcha.question.delete.confirmMessage{/lang}"></span>
								
								{event name='rowButtons'}
							</td>
							<td class="columnID columnQuestionID">{$question->questionID}</td>
							<td class="columnText columnQuestion"><a href="{link controller='CaptchaQuestionEdit' id=$question->questionID}{/link}">{$question->getQuestion()}</a></td>
							
							{event name='columns'}
						</tr>
					{/foreach}
				{/content}
			</tbody>
		</table>
	</div>
{hascontentelse}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/hascontent}

<footer class="contentFooter">
	{hascontent}
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}
	
	<nav class="contentFooterNavigation">
		<ul>
			<li><a href="{link controller='CaptchaQuestionAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.captcha.question.add{/lang}</span></a></li>
			
			{event name='contentFooterNavigation'}
		</ul>
	</nav>
</footer>

{include file='footer'}
 
