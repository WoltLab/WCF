<?php
namespace wcf\system\search;
use wcf\data\search\ISearchResultObject;
use wcf\form\IForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * All searchable object types should implement this interface. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search
 */
interface ISearchableObjectType {
	/**
	 * Caches the data for the given object ids.
	 * 
	 * @param	array		$objectIDs
	 * @param	array		$additionalData
	 */
	public function cacheObjects(array $objectIDs, array $additionalData = null);
	
	/**
	 * Returns the object with the given object id.
	 * 
	 * @param	integer		$objectID
	 * @return	ISearchResultObject
	 */
	public function getObject($objectID);
	
	/**
	 * Shows the form part of this object type.
	 * 
	 * @param	IForm		$form		instance of the form class where the search has taken place
	 */
	public function show(IForm $form = null);
	
	/**
	 * Returns the application abbreviation.
	 * 
	 * @return	string
	 */
	public function getApplication();
	
	/**
	 * Returns the search conditions of this message type.
	 * 
	 * @param	IForm		$form
	 * @return	PreparedStatementConditionBuilder
	 */
	public function getConditions(IForm $form = null);
	
	/**
	 * Provides the ability to add additional joins to sql search query. 
	 * 
	 * @return	string
	 */
	public function getJoins();
	
	/**
	 * Returns the database table name of this message.
	 * 
	 * @return	string
	 */
	public function getTableName();
	
	/**
	 * Returns the database field name of the message id.
	 * 
	 * @return	string
	 */
	public function getIDFieldName();
	
	/**
	 * Returns the database field name of the subject field.
	 * 
	 * @return	string
	 */
	public function getSubjectFieldName();
	
	/**
	 * Returns the database field name of the username.
	 * 
	 * @return	string
	 */
	public function getUsernameFieldName();
	
	/**
	 * Returns the database field name of the time.
	 * 
	 * @return	string
	 */
	public function getTimeFieldName();
	
	/**
	 * Returns additional search information.
	 * 
	 * @return	mixed
	 */
	public function getAdditionalData();
	
	/**
	 * Returns true if the current user can use this searchable object type.
	 * 
	 * @return	boolean
	 */
	public function isAccessible();
	
	/**
	 * Returns the name of the form template for this object type.
	 * 
	 * @return	string
	 */
	public function getFormTemplateName();
	
	/**
	 * Replaces the outer SQL query with a custom version. Querying the search index requires the
	 * placeholder {WCF_SEARCH_INNER_JOIN} within an empty INNER JOIN() statement.
	 * 
	 * @param	string					$q
	 * @param	PreparedStatementConditionBuilder	$searchIndexConditions
	 * @param	PreparedStatementConditionBuilder	$additionalConditions
	 * @return	string
	 */
	public function getOuterSQLQuery($q, PreparedStatementConditionBuilder &$searchIndexConditions = null, PreparedStatementConditionBuilder &$additionalConditions = null);
	
	/**
	 * Sets the location in menu/breadcrumbs.
	 * 
	 * @since	3.0
	 */
	public function setLocation();
	
	/**
	 * Returns the name of the active main menu item.
	 * 
	 * @return	string
	 * @deprecated  3.0
	 */
	public function getActiveMenuItem();
}
