{include file='header' pageTitle='wcf.acp.user.rank.'|concat:$action}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.user.rank.{$action}{/lang}</h1>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='UserRankList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.user.rank.list{/lang}</span></a></li>
			
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='UserRankAdd'}{/link}{else}{link controller='UserRankEdit' id=$rankID}{/link}{/if}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.global.form.data{/lang}</legend>
			
			<dl{if $errorField == 'rankTitle'} class="formError"{/if}>
				<dt><label for="rankTitle">{lang}wcf.acp.user.rank.title{/lang}</label></dt>
				<dd>
					<input type="text" id="rankTitle" name="rankTitle" value="{$rankTitle}" required="required" autofocus="autofocus" class="long" />
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
								<li class="labelCustomClass"><label><input type="radio" name="cssClassName" value="custom"{if $cssClassName == 'custom'} checked="checked"{/if} /> <span><input type="text" id="customCssClassName" name="customCssClassName" value="{$customCssClassName}" class="long" /></span></label></li>
							{else}
								<li><label><input type="radio" name="cssClassName" value="{$className}"{if $cssClassName == $className} checked="checked"{/if} /> <span class="badge label{if $className != 'none'} {$className}{/if}">{lang}wcf.acp.user.rank.title{/lang}</span></label></li>
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
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.acp.user.rank.image{/lang}</legend>
			
			<dl{if $errorField == 'rankImage'} class="formError"{/if}>
				<dt><label for="rankImage">{lang}wcf.acp.user.rank.image{/lang}</label></dt>
				<dd>
					<input type="text" id="rankImage" name="rankImage" value="{$rankImage}" class="long" />
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
					<input type="number" id="repeatImage" name="repeatImage" value="{@$repeatImage}" min="1" class="tiny" />
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
			
			{event name='imageFields'}
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.acp.user.rank.requirement{/lang}</legend>
			
			<dl{if $errorField == 'groupID'} class="formError"{/if}>
				<dt><label for="groupID">{lang}wcf.user.group{/lang}</label></dt>
				<dd>
					<select id="groupID" name="groupID">
						{foreach from=$availableGroups item=group}
							<option value="{@$group->groupID}"{if $group->groupID == $groupID} selected="selected"{/if}>{$group->groupName|language}</option>
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
						<option value="0"></option>
						<option value="1"{if $requiredGender == 1} selected="selected"{/if}>{lang}wcf.user.gender.male{/lang}</option>
						<option value="2"{if $requiredGender == 2} selected="selected"{/if}>{lang}wcf.user.gender.female{/lang}</option>
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
					<input type="number" id="requiredPoints" name="requiredPoints" value="{@$requiredPoints}" min="0" class="tiny" />
					{if $errorField == 'requiredPoints'}
						<small class="innerError">
							{lang}wcf.acp.user.rank.requiredPoints.error.{@$errorType}{/lang}
						</small>
					{/if}
					<small>{lang}wcf.acp.user.rank.requiredPoints.description{/lang}</small>
				</dd>
			</dl>
			
			{event name='requirementFields'}
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
	</div>
</form>


{include file='footer'}
