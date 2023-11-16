{include file='header' pageTitle='wcf.acp.captcha.question.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.captcha.question.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='CaptchaQuestionAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.captcha.question.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="CaptchaQuestionList" link="pageNo=%d"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div id="captchaQuestionTabelContainer" class="section tabularBox">
		<table class="table jsObjectActionContainer" data-object-action-class-name="wcf\data\captcha\question\CaptchaQuestionAction">
			<thead>
				<tr>
					<th class="columnID columnQuestionID active ASC" colspan="2">{lang}wcf.global.objectID{/lang}</th>
					<th class="columnText columnQuestion">{lang}wcf.acp.captcha.question.question{/lang}</th>
					<th class="columnDigits columnViews">{lang}wcf.acp.captcha.question.views{/lang}</th>
					<th class="columnDigits columnCorrectSubmissions">{lang}wcf.acp.captcha.question.correctSubmissions{/lang}</th>
					<th class="columnDigits columnIncorrectSubmissions">{lang}wcf.acp.captcha.question.incorrectSubmissions{/lang}</th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item='question'}
					<tr class="jsQuestionRow jsObjectActionObject" data-object-id="{@$question->getObjectID()}">
						<td class="columnIcon">
							{objectAction action="toggle" isDisabled=$question->isDisabled}
							<a href="{link controller='CaptchaQuestionEdit' id=$question->questionID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip">{icon name='pencil'}</a>
							{objectAction action="delete" objectTitle=$question->getQuestion()}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID columnQuestionID">{$question->questionID}</td>
						<td class="columnText columnQuestion"><a href="{link controller='CaptchaQuestionEdit' id=$question->questionID}{/link}">{$question->getQuestion()}</a></td>
						<td class="columnDigits columnViews">{#$question->views}</td>
						<td class="columnDigits columnCorrectSubmissions">{#$question->correctSubmissions}</td>
						<td class="columnDigits columnIncorrectSubmissions">{#$question->incorrectSubmissions}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
		<nav class="contentFooterNavigation">
			<ul>
				<li><a href="{link controller='CaptchaQuestionAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.captcha.question.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<woltlab-core-notice type="info">{lang}wcf.global.noItems{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}
 
