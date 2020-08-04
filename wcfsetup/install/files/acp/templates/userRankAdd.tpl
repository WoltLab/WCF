{include file='header' pageTitle='wcf.acp.user.rank.'|concat:$action}

<script data-relocate="true">
	(function() {
		var previews = [];
		elBySelAll('#labelList .jsRankPreview', undefined, function(preview) {
			previews.push(preview);
		});
		
		var input = elById('rankTitle');
		function updatePreview() {
			var value = input.value.trim() || '{lang}wcf.acp.user.rank.title{/lang}';
			previews.forEach(function(preview) {
				preview.textContent = value;
			});
		}
		input.addEventListener('input', updatePreview, { passive: true });
		
		updatePreview();
		
		elById('customCssClassName').addEventListener('focus', function () {
			elBySel('.jsCustomCssClassName').checked = true;
		});
	})();
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.rank.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='UserRankList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.user.rank.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='UserRankAdd'}{/link}{else}{link controller='UserRankEdit' id=$rankID}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'rankTitle'} class="formError"{/if}>
			<dt><label for="rankTitle">{lang}wcf.acp.user.rank.title{/lang}</label></dt>
			<dd>
				<input type="text" id="rankTitle" name="rankTitle" value="{$i18nPlainValues['rankTitle']}" required autofocus class="long">
				{if $errorField == 'rankTitle'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'multilingual'}
							{lang}wcf.global.form.error.multilingual{/lang}
						{else}
							{lang}wcf.acp.user.rank.title.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		{include file='multipleLanguageInputJavascript' elementIdentifier='rankTitle' forceSelection=false}
		
		<dl{if $errorField == 'cssClassName'} class="formError"{/if}>
			<dt><label for="cssClassName">{lang}wcf.acp.user.rank.cssClassName{/lang}</label></dt>
			<dd>
				<ul id="labelList">
					{foreach from=$availableCssClassNames item=className}
						{if $className == 'custom'}
							<li class="labelCustomClass"><input type="radio" name="cssClassName" class="jsCustomCssClassName" value="custom"{if $cssClassName == 'custom'} checked{/if}> <span><input type="text" id="customCssClassName" name="customCssClassName" value="{$customCssClassName}" class="long"></span></li>
						{else}
							<li><label><input type="radio" name="cssClassName" value="{$className}"{if $cssClassName == $className} checked{/if}> <span class="badge label{if $className != 'none'} {$className}{/if} jsRankPreview">{lang}wcf.acp.user.rank.title{/lang}</span></label></li>
						{/if}
					{/foreach}
				</ul>
				
				{if $errorField == 'cssClassName'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.user.rank.cssClassName.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
				<small>{lang}wcf.acp.user.rank.cssClassName.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='dataFields'}
	</div>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.user.rank.image{/lang}</h2>
		
		<dl{if $errorField == 'rankImage'} class="formError"{/if}>
			<dt><label for="rankImage">{lang}wcf.acp.user.rank.image{/lang}</label></dt>
			<dd>
				<input type="text" id="rankImage" name="rankImage" value="{$rankImage}" class="long">
				{if $errorField == 'rankImage'}
					<small class="innerError">
						{lang}wcf.acp.user.rank.image.error.{@$errorType}{/lang}
					</small>
				{/if}
				<small>{lang}wcf.acp.user.rank.rankImage.description{/lang}</small>
			</dd>
		</dl>
		
		<dl{if $errorField == 'repeatImage'} class="formError"{/if}>
			<dt><label for="repeatImage">{lang}wcf.acp.user.rank.repeatImage{/lang}</label></dt>
			<dd>
				<input type="number" id="repeatImage" name="repeatImage" value="{@$repeatImage}" min="1" class="tiny">
				{if $errorField == 'rankImage'}
					<small class="innerError">
						{lang}wcf.acp.user.rank.repeatImage.error.{@$errorType}{/lang}
					</small>
				{/if}
				<small>{lang}wcf.acp.user.rank.repeatImage.description{/lang}</small>
			</dd>
		</dl>
		
		{if $action == 'edit' && $rank->rankImage}
			<dl>
				<dt><label>{lang}wcf.acp.user.rank.currentImage{/lang}</label></dt>
				<dd>{@$rank->getImage()}</dd>
			</dl>
		{/if}
		
		<dl{if $errorField == 'hideTitle'} class="formError"{/if}>
			<dt></dt>
			<dd>
				<label><input type="checkbox" id="hideTitle" name="hideTitle" value="1"{if $hideTitle} checked{/if}> {lang}wcf.acp.user.rank.hideTitle{/lang}</label>
				{if $errorField == 'hideTitle'}
					<small class="innerError">
						{lang}wcf.acp.user.rank.hideTitle.error.{@$errorType}{/lang}
					</small>
				{/if}
				<small>{lang}wcf.acp.user.rank.hideTitle.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='imageFields'}
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.user.rank.requirement{/lang}</h2>
		
		<dl{if $errorField == 'groupID'} class="formError"{/if}>
			<dt><label for="groupID">{lang}wcf.user.group{/lang}</label></dt>
			<dd>
				<select id="groupID" name="groupID">
					{foreach from=$availableGroups item=group}
						<option value="{@$group->groupID}"{if $group->groupID == $groupID} selected{/if}>{$group->getTitle()}</option>
					{/foreach}
				</select>
				{if $errorField == 'groupID'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.user.rank.userGroup.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
				<small>{lang}wcf.acp.user.rank.userGroup.description{/lang}</small>
			</dd>
		</dl>
		
		<dl{if $errorField == 'requiredGender'} class="formError"{/if}>
			<dt><label for="requiredGender">{lang}wcf.user.option.gender{/lang}</label></dt>
			<dd>
				<select id="requiredGender" name="requiredGender">
					<option value="0">{lang}wcf.global.noSelection{/lang}</option>
					<option value="1"{if $requiredGender == 1} selected{/if}>{lang}wcf.user.gender.male{/lang}</option>
					<option value="2"{if $requiredGender == 2} selected{/if}>{lang}wcf.user.gender.female{/lang}</option>
					<option value="3"{if $requiredGender == 3} selected{/if}>{lang}wcf.user.gender.other{/lang}</option>
				</select>
				{if $errorField == 'requiredGender'}
					<small class="innerError">
						{lang}wcf.acp.user.rank.requiredGender.error.{@$errorType}{/lang}
					</small>
				{/if}
				<small>{lang}wcf.acp.user.rank.requiredGender.description{/lang}</small>
			</dd>
		</dl>
		
		<dl{if $errorField == 'requiredPoints'} class="formError"{/if}>
			<dt><label for="requiredPoints">{lang}wcf.acp.user.rank.requiredPoints{/lang}</label></dt>
			<dd>
				<input type="number" id="requiredPoints" name="requiredPoints" value="{@$requiredPoints}" min="0" class="tiny">
				{if $errorField == 'requiredPoints'}
					<small class="innerError">
						{lang}wcf.acp.user.rank.requiredPoints.error.{@$errorType}{/lang}
					</small>
				{/if}
				<small>{lang}wcf.acp.user.rank.requiredPoints.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='requirementFields'}
	</section>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
