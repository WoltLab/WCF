{include file='__formFieldHeader'}

<textarea
	id="{@$field->getPrefixedId()}"
	name="{@$field->getPrefixedId()}"
	class="wysiwygTextarea"
	data-disable-attachments="true"
	{if $field->getAutosaveId() !== null}
		data-autosave="{@$field->getAutosaveId()}"
		{if $field->getLastEditTime() !== 0}
			data-autosave-last-edit-time="{@$field->getLastEditTime()}"
		{/if}
	{/if}
>{$field->getValue()}</textarea>

{include file='wysiwyg' wysiwygSelector=$field->getPrefixedId()}

{include file='__formFieldFooter'}
