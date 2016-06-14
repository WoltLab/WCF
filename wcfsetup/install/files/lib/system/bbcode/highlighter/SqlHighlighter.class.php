<?php
namespace wcf\system\bbcode\highlighter;
use wcf\system\Callback;
use wcf\util\StringStack;
use wcf\util\StringUtil;

/**
 * Highlights syntax of sql queries.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode\Highlighter
 */
class SqlHighlighter extends Highlighter {
	/**
	 * @inheritDoc
	 */
	protected $allowsNewslinesInQuotes = true;
	
	/**
	 * @inheritDoc
	 */
	protected $quotes = ["'", '"'];
	
	/**
	 * @inheritDoc
	 */
	protected $singleLineComment = ['#', '--'];
	
	/**
	 * @inheritDoc
	 */
	protected $separators = ['(', ')', ',', ';'];
	
	/**
	 * @inheritDoc
	 */
	protected $operators = ['<>', '~=', '!=', '^=', '=', '<', '<=', '>', '>=', '*', '/', '+', '-', '||', '@', '%', '&', '?', '\$'];
	
	/**
	 * @inheritDoc
	 */
	protected function cacheComments($string) {
		if ($this->cacheCommentsRegEx !== null) {
			$string = $this->cacheCommentsRegEx->replace($string, new Callback(function (array $matches) {
				$string = $matches[1];
				if (isset($matches[2])) $comment = $matches[2];
				else $comment = '';
				
				// strip slashes
				$string = str_replace("\\\"", "\"", $string);
				$hash = '';
				if (!empty($comment)) {
					$comment = str_replace("\\\"", "\"", $comment);
						
					// create hash
					$hash = StringStack::pushToStringStack('<span class="hlComments">'.StringUtil::encodeHTML($comment).'</span>', 'highlighterComments', "\0\0");
				}
				
				return $string.$hash;
			}));
		}
		
		return $string;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function cacheQuotes($string) {
		if ($this->quotesRegEx !== null) {
			$string = $this->quotesRegEx->replace($string, new Callback(function (array $matches) {
				return StringStack::pushToStringStack('<span class="hlQuotes">'.StringUtil::encodeHTML($matches[0]).'</span>', 'highlighterQuotes', "\0\0");
			}));
		}
		
		return $string;
	}
	
	/**
	 * @inheritDoc
	 */
	protected $keywords1 = [
		'action',
		'add',
		'aggregate',
		'all',
		'alter',
		'after',
		'and',
		'as',
		'asc',
		'avg',
		'avg_row_length',
		'auto_increment',
		'between',
		'bigint',
		'bit',
		'binary',
		'blob',
		'bool',
		'both',
		'by',
		'cascade',
		'case',
		'char',
		'character',
		'change',
		'check',
		'checksum',
		'column',
		'columns',
		'comment',
		'constraint',
		'create',
		'cross',
		'current_date',
		'current_time',
		'current_timestamp',
		'data',
		'database',
		'databases',
		'date',
		'datetime',
		'day',
		'day_hour',
		'day_minute',
		'day_second',
		'dayofmonth',
		'dayofweek',
		'dayofyear',
		'dec',
		'decimal',
		'default',
		'delayed',
		'delay_key_write',
		'delete',
		'desc',
		'describe',
		'distinct',
		'distinctrow',
		'double',
		'drop',
		'end',
		'else',
		'escape',
		'escaped',
		'enclosed',
		'enum',
		'explain',
		'exists',
		'fields',
		'file',
		'first',
		'float',
		'float4',
		'float8',
		'flush',
		'foreign',
		'from',
		'for',
		'full',
		'function',
		'global',
		'grant',
		'grants',
		'group',
		'having',
		'heap',
		'high_priority',
		'hour',
		'hour_minute',
		'hour_second',
		'hosts',
		'identified',
		'ignore',
		'in',
		'index',
		'infile',
		'inner',
		'insert',
		'insert_id',
		'int',
		'integer',
		'interval',
		'int1',
		'int2',
		'int3',
		'int4',
		'int8',
		'into',
		'if',
		'is',
		'isam',
		'join',
		'key',
		'keys',
		'kill',
		'last_insert_id',
		'leading',
		'left',
		'length',
		'like',
		'lines',
		'limit',
		'load',
		'local',
		'lock',
		'logs',
		'long',
		'longblob',
		'longtext',
		'low_priority',
		'max',
		'max_rows',
		'match',
		'mediumblob',
		'mediumtext',
		'mediumint',
		'middleint',
		'min_rows',
		'minute',
		'minute_second',
		'modify',
		'month',
		'monthname',
		'myisam',
		'natural',
		'numeric',
		'no',
		'not',
		'null',
		'on',
		'optimize',
		'option',
		'optionally',
		'or',
		'order',
		'outer',
		'outfile',
		'pack_keys',
		'partial',
		'password',
		'precision',
		'primary',
		'procedure',
		'process',
		'processlist',
		'privileges',
		'read',
		'real',
		'references',
		'reload',
		'regexp',
		'rename',
		'replace',
		'restrict',
		'returns',
		'revoke',
		'rlike',
		'row',
		'rows',
		'second',
		'select',
		'set',
		'show',
		'shutdown',
		'smallint',
		'soname',
		'sql_big_tables',
		'sql_big_selects',
		'sql_low_priority_updates',
		'sql_log_off',
		'sql_log_update',
		'sql_select_limit',
		'sql_small_result',
		'sql_big_result',
		'sql_warnings',
		'straight_join',
		'starting',
		'status',
		'string',
		'table',
		'tables',
		'temporary',
		'terminated',
		'text',
		'then',
		'time',
		'timestamp',
		'tinyblob',
		'tinytext',
		'tinyint',
		'trailing',
		'to',
		'type',
		'use',
		'using',
		'unique',
		'unlock',
		'unsigned',
		'update',
		'usage',
		'values',
		'varchar',
		'variables',
		'varying',
		'varbinary',
		'with',
		'write',
		'when',
		'where',
		'year',
		'year_month',
		'zerofill'
	];
	
	/**
	 * @inheritDoc
	 */
	protected $keywords2 = [
		'ABS',
		'ACOS',
		'ADDDATE',
		'ASCII',
		'ASIN',
		'ATAN',
		'ATAN2',
		'AVG',
		'BENCHMARK',
		'BIN',
		'CEILING',
		'CHAR',
		'COALESCE',
		'CONCAT',
		'CONV',
		'COS',
		'COT',
		'COUNT',
		'CURDATE',
		'CURTIME',
		'DATABASE',
		'DAYNAME',
		'DAYOFMONTH',
		'DAYOFWEEK',
		'DAYOFYEAR',
		'DECODE',
		'DEGREES',
		'ELT',
		'ENCODE',
		'ENCRYPT',
		'EXP',
		'EXTRACT',
		'FIELD',
		'FLOOR',
		'FORMAT',
		'GREATEST',
		'HEX',
		'HOUR',
		'IF',
		'IFNULL',
		'INSERT',
		'INSTR',
		'INTERVAL',
		'ISNULL',
		'LCASE',
		'LEAST',
		'LEFT',
		'LENGTH',
		'LOCATE',
		'LOCATE',
		'LOG',
		'LOG10',
		'LOWER',
		'LPAD',
		'LTRIM',
		'MAX',
		'MD5',
		'MID',
		'MIN',
		'MINUTE',
		'MOD',
		'MONTH',
		'MONTHNAME',
		'NOW',
		'NULLIF',
		'OCT',
		'ORD',
		'PASSWORD',
		'PI',
		'POSITION',
		'POW',
		'POWER',
		'prepare',
		'QUARTER',
		'RADIANS',
		'RAND',
		'REPEAT',
		'REPLACE',
		'REVERSE',
		'RIGHT',
		'ROUND',
		'ROUND',
		'RPAD',
		'RTRIM',
		'SECOND',
		'SIGN',
		'SIN',
		'SOUNDEX',
		'SPACE',
		'SQRT',
		'STD',
		'STDDEV',
		'STRCMP',
		'SUBDATE',
		'SUBSTRING',
		'SUBSTRING',
		'SUM',
		'SYSDATE',
		'TAN',
		'TRIM',
		'TRUNCATE',
		'UCASE',
		'UPPER',
		'USER',
		'VERSION',
		'WEEK',
		'WEEKDAY',
		'YEAR'
	];
}
