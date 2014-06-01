{include file='header' pageTitle='wcf.acp.notice.'|concat:$action}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.notice.{$action}{/lang}</h1>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='NoticeList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.notice.list{/lang}</span></a></li>
			
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='NoticeAdd'}{/link}{else}{link controller='NoticeEdit' object=$notice}{/link}{/if}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.global.form.data{/lang}</legend>
			
			<dl{if $errorField == 'noticeName'} class="formError"{/if}>
				<dt><label for="noticeName">{lang}wcf.global.name{/lang}</label></dt>
				<dd>
					<input type="text" id="noticeName" name="noticeName" value="{$noticeName}" required="required" autofocus="autofocus" class="long" />
					{if $errorField == 'noticeName'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.notice.noticeName.error.{$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl{if $errorField == 'notice'} class="formError"{/if}>
				<dt><label for="notice">{lang}wcf.acp.notice.notice{/lang}</label></dt>
				<dd>
					<textarea id="notice" name="notice" cols="40" rows="10">{$i18nPlainValues['notice']}</textarea>
					{if $errorField == 'notice'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{elseif $errorType == 'multilingual'}
								{lang}wcf.global.form.error.multilingual{/lang}
							{else}
								{lang}wcf.acp.notice.notice.error.{$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			{include file='multipleLanguageInputJavascript' elementIdentifier='notice' forceSelection=false}
			
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="noticeUseHtml" value="1"{if $noticeUseHtml} checked="checked"{/if} /> {lang}wcf.acp.notice.noticeUseHtml{/lang}</label>
				</dd>
			</dl>
			
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="isDisabled" value="1"{if $isDisabled} checked="checked"{/if} /> {lang}wcf.acp.notice.isDisabled{/lang}</label>
				</dd>
			</dl>
			
			<dl>
				<dt><label for="position">{lang}wcf.acp.notice.showOrder{/lang}</label></dt>
				<dd>
					<input type="number" id="showOrder" name="showOrder" value="{$showOrder}" class="tiny" min="0" />
					<small>{lang}wcf.acp.notice.showOrder.description{/lang}</small>
				</dd>
			</dl>
			
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="isDismissible" value="1"{if $isDismissible} checked="checked"{/if} /> {lang}wcf.acp.notice.isDismissible{/lang}</label>
					<small>{lang}wcf.acp.notice.isDismissible.description{/lang}</small>
				</dd>
			</dl>
			
			{if $action == 'edit' && $notice->isDismissible}
				<dl>
					<dt></dt>
					<dd>
						<label><input type="checkbox" name="resetIsDismissed" value="1"{if $resetIsDismissed} checked="checked"{/if} /> {lang}wcf.acp.notice.resetIsDismissed{/lang}</label>
						<small>{lang}wcf.acp.notice.resetIsDismissed.description{/lang}</small>
					</dd>
				</dl>
			{/if}
			
			{event name='dataFields'}
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<header class="boxHeadline boxSubHeadline">
		<h2>{lang}wcf.acp.notice.conditions{/lang}</h2>
		<small>{lang}wcf.acp.notice.conditions.description{/lang}</small>
	</header>
	
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.acp.notice.conditions.page{/lang}</legend>
			<small>{lang}wcf.acp.notice.conditions.page.description{/lang}</small>
			
			{foreach from=$groupedConditionObjectTypes['com.woltlab.wcf.page'] item='pageConditionObjectType'}
				{@$pageConditionObjectType->getProcessor()->getHtml()}
			{/foreach}
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.acp.notice.conditions.pointInTime{/lang}</legend>
			<small>{lang}wcf.acp.notice.conditions.pointInTime.description{/lang}</small>
			
			{foreach from=$groupedConditionObjectTypes['com.woltlab.wcf.pointInTime'] item='pointInTimeConditionObjectType'}
				{@$pointInTimeConditionObjectType->getProcessor()->getHtml()}
			{/foreach}
		</fieldset>
		
		{event name='conditionTypeFieldsets'}
	</div>
	
	<header class="boxHeadline boxSubHeadline">
		<h2>{lang}wcf.acp.notice.conditions.user{/lang}</h2>
		<small>{lang}wcf.acp.notice.conditions.user.description{/lang}</small>
	</header>
	
	{include file='userConditions' groupedObjectTypes=$groupedConditionObjectTypes['com.woltlab.wcf.user']}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
