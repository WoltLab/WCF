{include file='header' pageTitle='wcf.acp.notice.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.notice.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='NoticeList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.notice.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formNotice'}

<form id="formContainer" method="post" action="{if $action == 'add'}{link controller='NoticeAdd'}{/link}{else}{link controller='NoticeEdit' object=$notice}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'noticeName'} class="formError"{/if}>
			<dt><label for="noticeName">{lang}wcf.global.name{/lang}</label></dt>
			<dd>
				<input type="text" id="noticeName" name="noticeName" value="{$noticeName}" required autofocus class="long">
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
		{include file='shared_multipleLanguageInputJavascript' elementIdentifier='notice' forceSelection=false}
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" id="noticeUseHtml" name="noticeUseHtml" value="1"{if $noticeUseHtml} checked{/if}> {lang}wcf.acp.notice.noticeUseHtml{/lang}</label>
			</dd>
		</dl>
		
		<dl>
			<dt><label for="showOrder">{lang}wcf.global.showOrder{/lang}</label></dt>
			<dd>
				<input type="number" id="showOrder" name="showOrder" value="{$showOrder}" class="tiny" min="0">
				<small>{lang}wcf.acp.notice.showOrder.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='dataFields'}
	</div>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.settings{/lang}</h2>
		
		<dl>
			<dt><label for="cssClassName">{lang}wcf.acp.notice.cssClassName{/lang}</label></dt>
			<dd>
				{foreach from=$availableCssClassNames item=className}
					<label><input type="radio" name="cssClassName" value="{$className}"{if $cssClassName == $className} checked{/if}> <span>{lang}wcf.acp.notice.cssClassName.{$className}{/lang}</span></label>
				{/foreach}

				<label><input type="radio" name="cssClassName" value="custom"{if $cssClassName == 'custom'} checked{/if}> <span><input type="text" id="customCssClassName" name="customCssClassName" value="{$customCssClassName}" class="medium"></span></label>
				
				{if $errorField == 'cssClassName'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.notice.cssClassName.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
				<small>{lang}wcf.acp.notice.cssClassName.description{/lang}</small>
				
				<woltlab-core-notice type="info" id="cssClassNameExample">{lang}wcf.acp.notice.example{/lang}</woltlab-core-notice>
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="isDisabled" value="1"{if $isDisabled} checked{/if}> {lang}wcf.acp.notice.isDisabled{/lang}</label>
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="isDismissible" value="1"{if $isDismissible} checked{/if}> {lang}wcf.acp.notice.isDismissible{/lang}</label>
				<small>{lang}wcf.acp.notice.isDismissible.description{/lang}</small>
			</dd>
		</dl>
		
		{if $action == 'edit' && $notice->isDismissible}
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="resetIsDismissed" value="1"{if $resetIsDismissed} checked{/if}> {lang}wcf.acp.notice.resetIsDismissed{/lang}</label>
					<small>{lang}wcf.acp.notice.resetIsDismissed.description{/lang}</small>
				</dd>
			</dl>
		{/if}
		
		{event name='settingsFields'}
	</section>
	
	{event name='sections'}
	
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.notice.conditions{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.acp.notice.conditions.description{/lang}</p>
		</header>
		
		<section class="section">
			<header class="sectionHeader">
				<h2 class="sectionTitle">{lang}wcf.acp.notice.conditions.page{/lang}</h2>
				<p class="sectionDescription">{lang}wcf.acp.notice.conditions.page.description{/lang}</p>
			</header>
			
			{foreach from=$groupedConditionObjectTypes['com.woltlab.wcf.page'] item='pageConditionObjectType'}
				{@$pageConditionObjectType->getProcessor()->getHtml()}
			{/foreach}
		</section>
		
		<section class="section">
			<header class="sectionHeader">
				<h2 class="sectionTitle">{lang}wcf.acp.notice.conditions.pointInTime{/lang}</h2>
				<p class="sectionDescription">{lang}wcf.acp.notice.conditions.pointInTime.description{/lang}</p>
			</header>
			
			{foreach from=$groupedConditionObjectTypes['com.woltlab.wcf.pointInTime'] item='pointInTimeConditionObjectType'}
				{@$pointInTimeConditionObjectType->getProcessor()->getHtml()}
			{/foreach}
		</section>
		
		{event name='conditionTypeSections'}
	</section>
	
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.notice.conditions.user{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.acp.notice.conditions.user.description{/lang}</p>
		</header>

		{include file='shared_userConditions' groupedObjectTypes=$groupedConditionObjectTypes['com.woltlab.wcf.user']}
	</section>
	
	{event name='conditionContainers'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>


<script data-relocate="true">
	{
		const example = document.getElementById('cssClassNameExample');
		const updateExample = (cssClassName) => {
			if (cssClassName == 'custom') {
				example.hidden = true;
			}
			else {
				example.type = cssClassName;
				example.hidden = false;
			}
		};
		
		document.querySelectorAll('input[name=cssClassName]').forEach((element) => {
			element.addEventListener('change', () => {
				updateExample(element.value);
			});
		});

		updateExample(document.querySelector('input[name=cssClassName]:checked').value);

		document.getElementById('customCssClassName').addEventListener('focus', function () {
			this.closest('label').querySelector('input[type=radio]').checked = true;
			updateExample('custom');
		});
	}
</script>

{include file='footer'}
