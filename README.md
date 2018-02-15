# PDODB


## Requirements
- PHP 7.0 or above


## Usage
```php
use xy2z\PDODB\PDODB;

$pdo = new PDODB('database', 'user', 'password', 'mysql','utf8');

// Change default database.
$pdo->use('test');

// Query
$result = $pdo->query("DELETE FROM tablename WHERE name LIKE :name", array(':name' => '%del%'));

// Select query
$data = $pdo->select("SELECT * FROM tablename");
$data = $pdo->select("SELECT * FROM tablename LIMIT :limit", array('limit' => 10));
$data = $pdo->select("SELECT * FROM tablename WHERE name LIKE :name", array('name' => '%Demogorgon%'));

// Select a single row
$row = $pdo->select_row('SELECT * FROM tablename WHERE id = :id', array(':id' => 4));
echo $row->id;

// Select a single field
$name = $pdo->select_field('SELECT name FROM tablename WHERE id = :id', array(
	':id' => 24
));
echo $name;

// Insert row
$id = $pdo->insert_row('tablename', array(
	'name' => 'The Dude',
	'age' => 234
));

// Insert multiple rows (accepts array of objects/arrays)
$insert = $pdo->insert_multi('tablename', array(
	(object) array('name' => 'Will', 'age' => '11'),
	array('name' => 'El', 'age' => '11'),
));

// Update
$fields = array(
	'name' => 'New Name',
	'age' => '12'
);
$result = $pdo->update('tablename', $fields, array('id' => 24));

// Delete rows
$result = $pdo->delete('tablename', array('id' => 14));

// Transaction.
try {
	$pdo->transaction_start();

	$pdo->insert_row('users', array('username' => 'Elliot'));
	$pdo->delete('tablename', array('id' => 60));

	$pdo->transaction_commit();
} catch(Exception $e) {
	var_dump('Error: ' . $e->getMessage());
	$pdo->transaction_rollback();
}
```
