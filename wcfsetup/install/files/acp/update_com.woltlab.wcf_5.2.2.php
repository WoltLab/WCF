<?php
use wcf\system\database\table\column\BinaryDatabaseTableColumn;
use wcf\system\database\table\column\CharDatabaseTableColumn;
use wcf\system\database\table\column\DateDatabaseTableColumn;
use wcf\system\database\table\column\DatetimeDatabaseTableColumn;
use wcf\system\database\table\column\DefaultFalseBooleanDatabaseTableColumn;
use wcf\system\database\table\column\DefaultTrueBooleanDatabaseTableColumn;
use wcf\system\database\table\column\EnumDatabaseTableColumn;
use wcf\system\database\table\column\IntDatabaseTableColumn;
use wcf\system\database\table\column\MediumtextDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\column\ObjectIdDatabaseTableColumn;
use wcf\system\database\table\column\SmallintDatabaseTableColumn;
use wcf\system\database\table\column\TextDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\DatabaseTableUtil;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\index\DatabaseTablePrimaryIndex;
use wcf\system\package\plugin\ScriptPackageInstallationPlugin;

/**
 * Adds missing foreign keys from the 3.1 to 5.2.0 update.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license	WoltLab License <http://www.woltlab.com/license-agreement.html>
 */

$tables = [
	DatabaseTable::create('wcf1_bbcode_media_provider')
		->columns([
			DefaultFalseBooleanDatabaseTableColumn::create('isDisabled')
		]),
	
	DatabaseTable::create('wcf1_blacklist_status')
		->columns([
			DateDatabaseTableColumn::create('date')
				->notNull(),
			DefaultFalseBooleanDatabaseTableColumn::create('delta1'),
			DefaultFalseBooleanDatabaseTableColumn::create('delta2'),
			DefaultFalseBooleanDatabaseTableColumn::create('delta3'),
			DefaultFalseBooleanDatabaseTableColumn::create('delta4')
		])
		->indices([
			DatabaseTableIndex::create('day')
				->type(DatabaseTableIndex::UNIQUE_TYPE)
				->columns(['date']),
		]),
	
	DatabaseTable::create('wcf1_blacklist_entry')
		->columns([
			EnumDatabaseTableColumn::create('type')
				->enumValues(['email', 'ipv4', 'ipv6','username']),
			BinaryDatabaseTableColumn::create('hash')
				->length(32),
			DatetimeDatabaseTableColumn::create('lastSeen')
				->notNull(),
			SmallintDatabaseTableColumn::create('occurrences')
				->length(5)
				->notNull()
		])
		->indices([
			DatabaseTableIndex::create('entry')
				->type(DatabaseTableIndex::UNIQUE_TYPE)
				->columns(['type', 'hash']),
			DatabaseTableIndex::create('numberOfReports')
				->columns(['type', 'occurrences'])
		]),
	
	DatabaseTable::create('wcf1_box')
		->columns([
			DefaultFalseBooleanDatabaseTableColumn::create('isDisabled')
		]),
	
	DatabaseTable::create('wcf1_category')
		->columns([
			DefaultFalseBooleanDatabaseTableColumn::create('descriptionUseHtml')
		]),
	
	DatabaseTable::create('wcf1_comment')
		->columns([
			MediumtextDatabaseTableColumn::create('message')
				->notNull()
		]),
	
	DatabaseTable::create('wcf1_comment_response')
		->columns([
			MediumtextDatabaseTableColumn::create('message')
				->notNull()
		]),
	
	DatabaseTable::create('wcf1_contact_attachment')
		->columns([
			NotNullInt10DatabaseTableColumn::create('attachmentID'),
			CharDatabaseTableColumn::create('accessKey')
				->length(40)
				->notNull()
		])
		->foreignKeys([
			DatabaseTableForeignKey::create()
				->columns(['attachmentID'])
				->referencedTable('wcf1_attachment')
				->referencedColumns(['attachmentID'])
				->onDelete('CASCADE')
		]),
	
	DatabaseTable::create('wcf1_language_item')
		->columns([
			DefaultFalseBooleanDatabaseTableColumn::create('isCustomLanguageItem')
		]),
	
	DatabaseTable::create('wcf1_like')
		->columns([
			NotNullInt10DatabaseTableColumn::create('reactionTypeID')
		]),
	
	DatabaseTable::create('wcf1_like_object')
		->columns([
			TextDatabaseTableColumn::create('cachedReactions')
		]),
	
	DatabaseTable::create('wcf1_media')
		->columns([
			DefaultFalseBooleanDatabaseTableColumn::create('captionEnableHtml'),
			NotNullInt10DatabaseTableColumn::create('downloads')
				->defaultValue(0),
			NotNullInt10DatabaseTableColumn::create('lastDownloadTime')
				->defaultValue(0)
		]),
	
	DatabaseTable::create('wcf1_page')
		->columns([
			IntDatabaseTableColumn::create('overrideApplicationPackageID')
				->length(10),
			DefaultFalseBooleanDatabaseTableColumn::create('enableShareButtons')
		])
		->foreignKeys([
			DatabaseTableForeignKey::create()
				->columns(['overrideApplicationPackageID'])
				->referencedTable('wcf1_package')
				->referencedColumns(['packageID'])
				->onDelete('SET NULL')
		]),
	
	DatabaseTable::create('wcf1_package')
		->indices([
			DatabaseTableIndex::create('package')
				->type(DatabaseTableIndex::UNIQUE_TYPE)
				->columns(['package'])
		]),
	
	DatabaseTable::create('wcf1_reaction_type')
		->columns([
			ObjectIdDatabaseTableColumn::create('reactionTypeID'),
			NotNullVarchar255DatabaseTableColumn::create('title'),
			NotNullInt10DatabaseTableColumn::create('showOrder')
				->defaultValue(0),
			NotNullVarchar255DatabaseTableColumn::create('iconFile')
				->defaultValue(''),
			DefaultTrueBooleanDatabaseTableColumn::create('isAssignable')
		])
		->indices([
			DatabaseTablePrimaryIndex::create()
				->columns(['reactionTypeID'])
		]),
	
	DatabaseTable::create('wcf1_style')
		->columns([
			EnumDatabaseTableColumn::create('apiVersion')
				->notNull()
				->enumValues(['3.0', '3.1', '5.2'])
				->defaultValue('3.0')
		]),
	
	DatabaseTable::create('wcf1_trophy')
		->columns([
			DefaultFalseBooleanDatabaseTableColumn::create('revokeAutomatically'),
			DefaultFalseBooleanDatabaseTableColumn::create('trophyUseHtml'),
			NotNullInt10DatabaseTableColumn::create('showOrder')
				->defaultValue(0)
		]),
	
	DatabaseTable::create('wcf1_user')
		->columns([
			NotNullInt10DatabaseTableColumn::create('articles')
				->defaultValue(0),
			NotNullVarchar255DatabaseTableColumn::create('blacklistMatches')
				->defaultValue('')
		]),
	
	DatabaseTable::create('wcf1_user_group')
		->columns([
			DefaultFalseBooleanDatabaseTableColumn::create('allowMention')
		]),
	
	DatabaseTable::create('wcf1_user_trophy')
		->columns([
			DefaultFalseBooleanDatabaseTableColumn::create('trophyUseHtml')
		]),
];

DatabaseTableUtil::addMissingForeignKeys(
	/** @var ScriptPackageInstallationPlugin $this */
	$this->installation->getPackageID(),
	$tables
);
