# PDODB


## Requirements
- PHP 7.0 or above


## Usage
```php
use xy2z\PDODB\PDODB;

$pdo = new PDODB('database', 'user', 'password', 'mysql','utf8');

// Change default database.
$pdo->use('test');

// Select query
$data = $pdo->select("SELECT * FROM tablename");
$data = $pdo->select("SELECT * FROM tablename LIMIT :limit", array('limit' => 10));
$data = $pdo->select("SELECT id, age FROM tablename WHERE name LIKE :name", array('name' => '%Demogorgon%'));
foreach ($data as $row) {
	echo $row->id;
}


// Select a single row
$row = $pdo->select_row('SELECT name, age FROM users WHERE id = :id', array('id' => 4));
echo $row->name;

// Select a single field
$name = $pdo->select_field('SELECT name FROM tablename WHERE id = :id', array(
	'id' => 24
));
echo $name;

// Insert row
$id = $pdo->insert_row('tablename', array(
	'name' => 'The Dude',
	'age' => 234
));
echo 'Inserted ID: ' . $id;

// Insert multiple rows (accepts array of (objects/arrays))
$count = $pdo->insert_multi('tablename', array(
	(object) array('name' => 'Will', 'age' => '11'),
	array('name' => 'El', 'age' => '11'),
));
echo 'Inserted ' . $count . ' rows.';

// Update
$fields = array(
	'name' => 'New Name',
	'age' => '12'
);
$count = $pdo->update('tablename', $fields, array('id' => 24));
echo 'Updated ' . $count . ' rows.';

// Delete rows
$count = $pdo->delete('tablename', array('id' => 14));
echo 'Deleted ' . $count . ' rows.';

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

// Query
$statement = $pdo->query("DELETE FROM tablename WHERE name LIKE :name", array('name' => '%del%'));
echo 'Deleted ' . $pdo->affected_rows($statement) . ' rows.';
```
