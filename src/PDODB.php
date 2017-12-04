<?php

	namespace xy2z\Tools;
	use \PDO, \PDOStatement;
	use \stdClass;

	/**
	 * PDO Database class
	 * Requires PHP 7.0+
	 *
	 * @author Alexander Pedersen <xy2z@protonmail.com>
	 */
	class PDODB {

		/**
		 * The PDO object
		 *
		 * @var object
		 */
		private $connection;

		/**
		 * The database name
		 *
		 * @var string
		 */
		private $db_name;

		/**
		 * Constructor
		 *
		 * @param string $db       Database name
		 * @param string $user     Username.
		 * @param string $password Password. Default empty.
		 * @param string $engine   Default 'mysql'.
		 */
		public function __construct(string $db, string $user, string $password = '', string $engine = 'mysql') {
			$dsn = strtolower($engine) . ':dbname=' . $db . ';host=127.0.0.1;charset=utf8';
			// $dsn = $engine . ':dbname=' . $db . ';host=127.0.0.1';
			$this->db_name = $db;
			$this->connection = new PDO($dsn, $user, $password);

			// Set error mode to throw exceptions.
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// Don't bother setting ints as PDO::PARAM_INT, like in LIMIT's, this will do it for us.
			$this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		}

		/**
		 * Use a new database
		 * @param  string $db Database name.
		 *
		 * @return bool       False on error.
		 */
		public function use(string $db) {
			$this->db_name = $db;
			return $this->connection->exec('USE ' . $db);
		}

		/**
		 * Get MySQL error message
		 *
		 * @return array Error info
		 */
		public function get_error() : array {
			return $this->connection->errorInfo();
		}

		/**
		 * Get affected rows for a INSERT, UPDATE or DELETE query.
		 *
		 * @param PDOStatement $statement PDO Statement object
		 *
		 * @return int Affected rows
		 */
		public function affected_rows(PDOStatement $statement) {
			return $statement->rowCount();
		}

		/**
		 * Get the last inserted id.
		 *
		 * @return int Last inserted id.
		 */
		public function last_insert_id() : int {
			return $this->connection->lastInsertId();
		}

		/**
		 * Query
		 *
		 * @param  string $query  The query.
		 * @param  array  $params The params to be replaced.
		 *
		 * @return array          Array of objects for each row.
		 */
		public function query(string $query, array $params = array()) : PDOStatement {
			$statement = $this->connection->prepare($query);
			$statement->execute($params);
			return $statement;
		}

		/**
		 * Select table data as array of objects.
		 *
		 * @param  string $query  The select query.
		 * @param  array  $params The params to be replaced.
		 *
		 * @return array          Array of objects for each row.
		 */
		public function select(string $query, array $params = array()) : array {
			$statement = $this->query($query, $params);
			return $statement->fetchAll(PDO::FETCH_CLASS);
		}

		/**
		 * Select a single row.
		 *
		 * @param  string $query  Select query.
		 * @param  array  $params The params to be replaced.
		 *
		 * @return stdClass       The row. NULL if not found.
		 */
		public function select_row(string $query, array $params = array()) /*: ?stdClass*/ {
			$statement = $this->query($query, $params);
			$fetch = $statement->fetchAll(PDO::FETCH_CLASS);

			if (!$fetch) {
				return null;
			}

			return $fetch[0];
		}

		/**
		 * Select a single field.
		 *
		 * @param  string $query  The select query.
		 * @param  array  $params The params to be replaced.
		 *
		 * @return mixed          The value of the field. NULL if not found.
		 */
		public function select_field(string $query, array $params = array()) /*: ?string*/ {
			$statement = $this->query($query, $params);
			// Fetch the first row. (there should only be 1)
			$fetch =  $statement->fetch(PDO::FETCH_NUM);
			return $fetch[0];
		}

		/**
		 * Get query fields from array to string
		 *
		 * @param array $fields
		 * @param string $separator
		 * @param string $key_value_prepend
		 *
		 * @return string Fields as a string ready for query.
		 */
		private static function get_query_fields(array $fields, $separator = ',', string $key_value_prepend = '') : string {
			$query = array();

			// Add fields to the query
			// So it would look like 'id = :id'
			foreach ($fields as $key => $value) {
				$query[] = '`' . $key . '` = :' . $key_value_prepend . $key;
			}

			return implode(' ' . $separator . ' ', $query);
		}

		/**
		 * Get fields values for a query with the key.
		 * Used for example in insert_mulit() for the VALUES().
		 *
		 * @param array $fields
		 * @param string $separator
		 * @param string $key_value_prepend
		 *
		 * @return string Values without the keys
		 */
		private static function get_query_fields_values(array $fields, $separator = ',', string $key_value_prepend = '') : string {
			$query = array();

			// Add fields to the query
			// So it would look like 'id = :id'
			foreach ($fields as $key => $value) {
				$query[] = ':' . $key_value_prepend . $key;
			}

			return implode(' ' . $separator . ' ', $query);
		}

		/**
		 * Get execute fields
		 *
		 * @param array $fields
		 * @param string $key_prepend Prepend to each key.
		 *
		 * @return array [description]
		 */
		private static function get_execute_fields(array $fields, string $key_prepend = '') : array {
			$params = array();

			// Add fields to the insert query
			// So it would look like 'id = :id'
			foreach ($fields as $key => $value) {
				$params[':' . $key_prepend . $key] = $value;
			}

			// Return $params to be used in the execute().
			return $params;
		}

		/**
		 * Insert a single row
		 *
		 * @param  string $table  Table name
		 * @param  array  $fields Fields to set
		 *
		 * @return int            Insert ID.
		 */
		public function insert_row(string $table, array $fields) : int {
			$query = "INSERT INTO `" . self::clean_string($table) . "` SET " . self::get_query_fields($fields);
			$statement = $this->query($query, self::get_execute_fields($fields));
			return (int) $this->connection->lastInsertId();
		}

		/**
		 * Insert multiple rows to a table
		 *
		 * @param string $table Table name
		 * @param array $fields Multiarray of fields
		 *
		 * @return int Affected rows
		 */
		public function insert_multi(string $table, array $fields) : int {
			// Convert to array if object.
			$fields_0 = (is_object($fields[0])) ? (array) $fields[0] : $fields[0];

			$query = "INSERT INTO `" . self::clean_string($table) . "` (" . implode(', ', array_keys($fields_0)) . ") ";
			$query .= " VALUES ";

			$values = array();
			$params = array();

			foreach ($fields as $key => $row) {
				if (is_object($row)) {
					$row = (array) $row;
				}
				$prepend = $key. "_";
				$values[] = '(' . self::get_query_fields_values($row, ', ', $prepend) . ')';

				// Add to params array for the execute().
				$params = array_merge($params, self::get_execute_fields($row, $prepend));
			}
			$query .= implode(', ', $values);

			$statement = $this->query($query, $params);
			return $statement->rowCount();
		}

		/**
		 * Update table data
		 *
		 * @param string $table Table name
		 * @param array $fields Fields to update.
		 * @param array $where the sql WHERE as array.
		 * @param int $limit Limit rows to update
		 *
		 * @return int Number of rows affected
		 */
		public function update(string $table, array $fields, $where = false, $limit = null) {
			$query = "UPDATE `" . self::clean_string($table) . "` SET " . self::get_query_fields($fields);
			$params = $fields;

			// WHERE
			if ($where) {
				// Add to query.
				$query .= " WHERE " . self::get_query_fields($where, 'AND', 'where_');
			}

			if (!is_null($limit)) {
				$params['pdodb_limit'] = $limit;
				$query .= " LIMIT :pdodb_limit";
			}

			if ($where) {
				// Format WHERE array so the keys doesn't interfere with the $fields keys.
				$params = array_merge($params, self::format_where($where));
			}

			$statement = $this->query($query, self::get_execute_fields($params));
			return $statement->rowCount();
		}

		/**
		 * Clean/escape a string when it's not possible in execute()
		 * Eg. table name, or other data that shouldn't have plings (')
		 *
		 * @param string $var The variable to clean/escape.
		 *
		 * @return string The cleaned/escaped string.
		 */
		private static function clean_string(string $var) {
			$valid_chars = array('$', '-', '_');

			// Fail if other chars than the valid are in the string (except a-z and 0-9).
			if (!ctype_alnum(str_replace($valid_chars, '', $var))) {
				throw new Exception('String contains invalid character(s).');
			}

			return $var;
		}

		/**
		 * Format the WHERE array.
		 *
		 * @param array $where WHERE array.
		 * @param string $key_prepend Prepend a string to all keys.
		 *
		 * @return array The new WHERE array.
		 */
		private static function format_where(array $where, $key_prepend = 'where_') {
			foreach ($where as $key => $value) {
				unset($where[$key]);
				$where[$key_prepend . $key] = $value;
			}
			return $where;
		}

		/**
		 * Delete table data
		 *
		 * @param string $table Table name.
		 * @param array $where Array of fields in WHERE clause .
		 * @param int $limit Limit of deleting rows.
		 *
		 * @return int Number of affected rows.
		 */
		public function delete(string $table, array $where, $limit = null) {
			$params = $where;
			$query = "DELETE FROM " . self::clean_string($table) . " WHERE " . self::get_query_fields($where);

			if (!is_null($limit)) {
				$params['pdodb_limit'] = $limit;
				$query .= " LIMIT :pdodb_limit";
			}

			$statement = $this->query($query, $params);

			return $statement->rowCount();
		}

	}
