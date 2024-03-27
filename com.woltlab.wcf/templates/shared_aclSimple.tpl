{if !$__aclSimplePrefix|isset}{assign var='__aclSimplePrefix' value=''}{/if}
{if !$__aclInputName|isset}{assign var='__aclInputName' value='aclValues'}{/if}
{if !$__supportsInvertedPermissions|isset}{assign var='__supportsInvertedPermissions' value=false}{/if}

<div class="section">
	<dl>
		<dt><label for="{@$__aclSimplePrefix}aclAllowAll">{lang}wcf.acl.allowAll{/lang}</label></dt>
		<dd>
			<ol role="group" aria-label="{lang}wcf.acl.allowAll{/lang}" class="flexibleButtonGroup">
				<li>
					<input type="radio" id="{@$__aclSimplePrefix}aclAllowAll" name="{@$__aclInputName}[allowAll]" value="1"{if $aclValues[allowAll]} checked{/if}>
					<label for="{@$__aclSimplePrefix}aclAllowAll" class="green">{icon name='check'} {lang}wcf.acp.option.type.boolean.yes{/lang}</label>
				</li>
				<li>
					<input type="radio" id="{@$__aclSimplePrefix}aclAllowAll_no" name="{@$__aclInputName}[allowAll]" value="0"{if !$aclValues[allowAll]} checked{/if}>
					<label for="{@$__aclSimplePrefix}aclAllowAll_no" class="red">{icon name='xmark'} {lang}wcf.acp.option.type.boolean.no{/lang}</label>
				</li>
			</ol>
		</dd>
	</dl>

	{if $__supportsInvertedPermissions}
		{if !$invertPermissionsPrefixed|isset}{assign var='invertPermissionsPrefixed' value=$__aclSimplePrefix}{/if}
		<dl id="{@$__aclSimplePrefix}invertPermissionsDl" {if $aclValues[allowAll]} style="display: none;"{/if}>
			<dt><label for="{@$__aclSimplePrefix}invertPermissions">{lang}wcf.acl.access.invertPermissions{/lang}</label></dt>
			<dd>
				<ol class="flexibleButtonGroup">
					<li>
						<input type="radio" id="{@$__aclSimplePrefix}invertPermissions" name="{@$__aclSimplePrefix}invertPermissions" value="1"{if $invertPermissions} checked{/if}>
						<label for="{@$__aclSimplePrefix}invertPermissions" class="green">{icon name='check'} {lang}wcf.acp.option.type.boolean.yes{/lang}</label>
					</li>
					<li>
						<input type="radio" id="{@$__aclSimplePrefix}invertPermissions_no" name="{@$__aclSimplePrefix}invertPermissions" value="0"{if !$invertPermissions} checked{/if}>
						<label for="{@$__aclSimplePrefix}invertPermissions_no" class="red">{icon name='xmark'} {lang}wcf.acp.option.type.boolean.no{/lang}</label>
					</li>
				</ol>
				<small>{lang}wcf.acl.access.invertPermissions.description{/lang}</small>
			</dd>
		</dl>
	{/if}
</div>

<section class="section" id="{@$__aclSimplePrefix}aclInputContainer"{if $aclValues[allowAll]} style="display: none;"{/if}>
	<h2 class="sectionTitle">{lang}wcf.acl.access{/lang}</h2>
	<dl>
		<dt><label for="{@$__aclSimplePrefix}aclSearchInput" id="{@$__aclSimplePrefix}aclSearchInputLabel">{lang}wcf.acl.access.grant{/lang}</label></dt>
		<dd>
			<input type="text" id="{@$__aclSimplePrefix}aclSearchInput" class="aclSearchInput long" placeholder="{lang}wcf.acl.search.description{/lang}">
		</dd>
	</dl>

	<dl id="{@$__aclSimplePrefix}aclListContainer"{if $aclValues[allowAll]} style="display: none;"{/if}>
		<dt id="{@$__aclSimplePrefix}aclListContainerDt">{lang}wcf.acl.access.granted{/lang}</dt>
		<dd>
			<ul id="{@$__aclSimplePrefix}aclAccessList" class="aclList">
				{foreach from=$aclValues[group] item=aclGroup}
					<li class="aclListItem">
						{icon name='users'}
						<span class="aclLabel">{$aclGroup}</span>
						<button type="button" class="aclItemDeleteButton jsTooltip" title="{lang}wcf.global.button.delete{/lang}">
							<fa-icon name="xmark"></fa-icon>
						</button>
						<input type="hidden" name="{@$__aclInputName}[group][]" value="{$aclGroup->groupID}">
					</li>
				{/foreach}
				{foreach from=$aclValues[user] item=aclUser}
					<li class="aclListItem">
						{icon name='user'}
						<span class="aclLabel">{$aclUser}</span>
						<button type="button" class="aclItemDeleteButton jsTooltip" title="{lang}wcf.global.button.delete{/lang}">
							<fa-icon name="xmark"></fa-icon>
						</button>
						<input type="hidden" name="{@$__aclInputName}[user][]" value="{$aclUser->userID}">
					</li>
				{/foreach}
			</ul>
		</dd>
	</dl>
</section>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/Acl/Simple', 'Language'], function(UiAclSimple, Language) {
		Language.addObject({
			'wcf.acl.access.grant': '{jslang}wcf.acl.access.grant{/jslang}',
			'wcf.acl.access.deny': '{jslang}wcf.acl.access.deny{/jslang}',
			'wcf.acl.access.granted': '{jslang}wcf.acl.access.granted{/jslang}',
			'wcf.acl.access.denied': '{jslang}wcf.acl.access.denied{/jslang}',
		});

		new UiAclSimple('{@$__aclSimplePrefix}', '{@$__aclInputName}');
	});
</script>
