<?php

class Database {
	/**
	 * @var PDOObject The handle to the database.
	 */
	private static $handle;

	/**
	 * Returns the PDOObject handle
	 *
	 * @returns PDOOBject The handle to the CanyonCMS database.
	 */
	public static function getHandle () {
		return self::$handle;
	}

	/**
	 * FOR INTERNAL USE ONLY.
	 */
	public static function setHandle ($handle) {
		self::$handle = $handle;
	}

	/**
	 * Perform a select query on the CanyonCMS database.
	 *
	 * This will perform a select query on $table (the table prefix is automatically prefixed) and returns the specified $cols where $where is true. The arguments in $where are automatically escaped properly. Example:
	 * <code>
	 * // DB is an alias for Database
	 * DB::select('settings', array('name', 'data'), array('id > ?', 3));
	 * </code>
	 *
	 * @param string $table The table to access. The database prefix is automatically added.
	 * @param mixed $cols An array of the columns to return, or '*' for all of them. Optional if $cols is associative.
	 * @param array $where An array where the first item is one or more comparision statements. Question marks (?) can optionally be used for binding. Then the second item and beyond are the values for the binds. These binds are automatically escaped for security. Example: array('`col1' > ? AND `col2` < ?', '12', '24');
	 * @param array $order An array where the first item is a column and the second is the direction to sort (desc or asc).
	 * @param int $limit The limit on the number of results to return. Sanizied for your convience.
	 * @return PDOStatement A PDOStatement with all of the results.
	 */
	public static function select ($table, $cols = '*', $where = null, $order = null, $limit = -1) {
		$prepare = array();

		$sql = "SELECT %s from `%s%s` %s %s %s";

		// $cols is like array('col1', 'col2');
		if(is_array($cols)) {
			$cols = '`'.implode('`', $cols).'`';
		}

		// using where statement
		// should be like array('`col1' > ? AND `col2` < ?', '12', '24');
		if(is_array($where)) {
			if(count($where) > 1) {
				$prepare = array_slice($where, 1);
			}
			$where = 'WHERE '.$where[0];
		}
		elseif(!is_null($where)) {
			$where = 'WHERE '.$where;
		}

		if(is_array($order)) {
			$order = "ORDER BY `{$order[0]}` {$order[1]}";
		}
		else {
			$order = '';
		}

		if($limit > 0 && is_number($limit))  {
			$limit = "LIMIT ".$limit;
		}
		else {
			$limit = '';
		}

		$sql = trim(sprintf($sql, $cols, CC_DB_PREFIX, $table, $where, $order, $limit));
		Log::add('DB Query: '.$sql);
		$smt = self::getHandle()->prepare($sql);

		if(!$smt) {
			print_r(self::getHandle()->errorInfo());
			return false;
		}

		if($smt->execute($prepare)) {
			return $smt;
		}
		else {
			echo '<h1>DB Error:</h1>';
			print_r(self::getHandle()->errorInfo());
			die();
		}
	}

	/**
	 * Perform a insert query on the CanyonCMS database.
	 *
	 * This will perform a insert query on $table (the table prefix is automatically prefixed) and returns the number of affected rows.
	 * <code>
	 * // DB is an alias for Database
	 * DB::insert('settings', array('package', 'name', 'data'), array('the package', 'the name', 'the data'));
	 *
	 * // same as above
	 * DB::insert('settings', array('package' => 'the package', 'name' => 'the name', 'data' => 'the data'));
	 * </code>
	 *
	 * @param string $table The table to access. The database prefix is automatically added.
	 * @param array $cols Either the list of columns, or an associative array in the form `column => value`.
	 * @param array $values The respective values for $cols. The data is automatically sanitized. Optional if $cols is associative.
	 * @return int The number of rows affected.
	 */
	public static function insert ($table, $cols, $values = null) {
		$prepare = array();

		$sql = "INSERT INTO `%s%s` (%s) VALUES (%s)";

		if($values === null) {
			$keys = '`'.implode('`,`',array_keys($cols)).'`';
			$binds = array_values($cols);
			$values = array_fill(0, count($binds), '?');
		}
		else {
			$keys = '`'.implode('`,`',array_values($cols)).'`';
			$binds = $values;
			$values = array_fill(0, count($binds), '?');
		}

		$sql = trim(sprintf($sql, CC_DB_PREFIX, $table, $keys, implode(',',$values)));
		Log::add('DB Query: '.$sql);
		$smt = self::getHandle()->prepare($sql);

		if(!$smt) {
			print_r(self::getHandle()->errorInfo());
			return false;
		}

		if($smt->execute($binds)) {
			return $smt->rowCount();
		}
		else {
			echo '<h1>DB Error:</h1>';
			print_r(self::getHandle()->errorInfo());
			die();
		}
	}

	/**
	 * Perform a update query on the CanyonCMS database.
	 *
	 * This will perform a update query on $table (the table prefix is automatically prefixed) and returns the number of affected rows.
	 * <code>
	 * // DB is an alias for Database
	 * DB::update('settings', array('package', 'name', 'data'), array('the package', 'the name', 'the data'), array('id = ?', 4));
	 *
	 * // same as above
	 * DB::update('settings', array('package' => 'the package', 'name' => 'the name', 'data' => 'the data'), null, array('id = ?', 4));
	 * </code>
	 *
	 * @param string $table The table to access. The database prefix is automatically added.
	 * @param array $cols Either the list of columns, or an associative array in the form `column => value`.
	 * @param array $values The respective values for $cols. The data is automatically sanitized. Optional if $cols is associative.
	 * @param array $where An array where the first item is one or more comparision statements. Question marks (?) can optionally be used for binding. Then the second item and beyond are the values for the binds. These binds are automatically escaped for security. Example: array('`col1' > ? AND `col2` < ?', '12', '24');
	 * @return int The number of rows affected.
	 */
	public static function update ($table, $cols, $values = null, $where) {
		$prepare = array();

		$sql = "UPDATE `%s%s` SET%s %s";

		if(!is_array($values)) {
			foreach($cols as $key => $value) {
				$set .= " `$key` = ?,";
				$binds[] = $value;
			}
		}
		else {
			foreach($cols as $key => $value) {
				$set .= " `$value` = ?,";
			}
			$binds = $values;
		}
		$set = ' '.trim($set, ', ');

		// using where statement
		// should be like array('`col1' > ? AND `col2` < ?', '12', '24');
		if(is_array($where)) {
			if(count($where) > 1) {
				$prepare = array_slice($where, 1);
			}
			$where = 'WHERE '.$where[0];
		}
		elseif(!is_null($where)) {
			$where = 'WHERE '.$where;
		}

		if($limit > 0 && is_numeric($limit))  {
			$limit = "LIMIT ".$limit;
		}
		else {
			$limit = '';
		}
		
		$sql = trim(sprintf($sql, CC_DB_PREFIX, $table, $set, $where));
		Log::add('DB Query: '.$sql);
		$smt = self::getHandle()->prepare($sql);
		var_dump($binds+$prepare);
		if(!$smt) {
			print_r(self::getHandle()->errorInfo());
			debug_print_backtrace();
			return false;
		}

		if($smt->execute($binds+$prepare)) {
			return $smt->rowCount();
		}
		else {
			echo '<h1>DB Error:</h1>';
			print_r(self::getHandle()->errorInfo());
			die();
		}
	}
}
class_alias('Database', 'DB');

try {
	global $database, $db_username, $db_password, $db_prefix;

	// create the pdo object
	DB::setHandle(new PDO($database, $db_username, $db_password));
	define('CC_DB_PREFIX', $db_prefix);

	// remove now unused vars
	unset($database);
	unset($db_password);
	unset($db_username);
	unset($db_prefix);
} catch (PDOException $e) {
	die('DB ERROR: '.$e);
}