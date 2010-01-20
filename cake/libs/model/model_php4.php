<?php
/* SVN FILE: $Id: model_php4.php 7691 2008-10-02 04:59:12Z nate $ */
/**
 * Object-relational mapper.
 *
 * DBO-backed object data model, for mapping database tables to Cake objects.
 *
 * PHP versions 4
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.cake.libs.model
 * @since			CakePHP(tm) v 0.10.0.0
 * @version			$Revision: 7691 $
 * @modifiedby		$LastChangedBy: nate $
 * @lastmodified	$Date: 2008-10-02 00:59:12 -0400 (Thu, 02 Oct 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Included libs
 */
uses('class_registry', 'validators', 'model' . DS . 'connection_manager', 'set');
/**
 * Object-relational mapper.
 *
 * DBO-backed object data model.
 * Automatically selects a database table name based on a pluralized lowercase object class name
 * (i.e. class 'User' => table 'users'; class 'Man' => table 'men')
 * The table is required to have at least 'id auto_increment', 'created datetime',
 * and 'modified datetime' fields.
 *
 * @package		cake
 * @subpackage	cake.cake.libs.model
 */
class Model extends Object{
/**
 * The name of the DataSource connection that this Model uses
 *
 * @var string
 * @access public
 */
	var $useDbConfig = 'default';
/**
 * Custom database table name.
 *
 * @var string
 * @access public
 */
	var $useTable = null;
/**
 * Custom display field name. Display fields are used by Scaffold, in SELECT boxes' OPTION elements.
 *
 * @var string
 * @access public
 */
	var $displayField = null;

/**
 * Value of the primary key ID of the record that this model is currently pointing to
 *
 * @var string
 * @access public
 */
	var $id = false;
/**
 * Container for the data that this model gets from persistent storage (the database).
 *
 * @var array
 * @access public
 */
	var $data = array();
/**
 * Table name for this Model.
 *
 * @var string
 * @access public
 */
	var $table = false;
/**
 * The name of the ID field for this Model.
 *
 * @var string
 * @access public
 */
	var $primaryKey = null;
/**
 * Table metadata
 *
 * @var array
 * @access protected
 */
	var $_tableInfo = null;
/**
 * List of validation rules. Append entries for validation as ('field_name' => '/^perl_compat_regexp$/')
 * that have to match with preg_match(). Use these rules with Model::validate()
 *
 * @var array
 * @access public
 */
	var $validate = array();
/**
 * Errors in validation
 * @var array
 * @access public
 */
	var $validationErrors = array();
/**
 * Database table prefix for tables in model.
 *
 * @var string
 * @access public
 */
	var $tablePrefix = null;
/**
 * Name of the model.
 *
 * @var string
 * @access public
 */
	var $name = null;
/**
 * Name of the current model.
 *
 * @var string
 * @access public
 */
	var $currentModel = null;
/**
 * List of table names included in the Model description. Used for associations.
 *
 * @var array
 * @access public
 */
	var $tableToModel = array();
/**
 * List of Model names by used tables. Used for associations.
 *
 * @var array
 * @access public
 */
	var $modelToTable = array();
/**
 * List of Foreign Key names to used tables. Used for associations.
 *
 * @var array
 * @access public
 */
	var $keyToTable = array();
/**
 * Alias name for model.
 *
 * @var array
 * @access public
 */
	var $alias = null;
/**
 * Whether or not transactions for this model should be logged
 *
 * @var boolean
 * @access public
 */
	var $logTransactions = false;
/**
 * Whether or not to enable transactions for this model (i.e. BEGIN/COMMIT/ROLLBACK)
 *
 * @var boolean
 * @access public
 */
	var $transactional = false;
/**
 * Whether or not to cache queries for this model.  This enables in-memory
 * caching only, the results are not stored beyond this execution.
 *
 * @var boolean
 * @access public
 */
	var $cacheQueries = true;
/**
 * belongsTo association
 *
 * @var array
 * @access public
 */
	var $belongsTo = array();
/**
 * hasOne association
 *
 * @var array
 * @access public
 */
	var $hasOne = array();
/**
 * hasMany association
 *
 * @var array
 * @access public
 */
	var $hasMany = array();
/**
 * hasAndBelongsToMany association
 *
 * @var array
 * @access public
 */
	var $hasAndBelongsToMany = array();
/**
 * Depth of recursive association
 *
 * @var int
 * @access public
 */
	var $recursive = 1;
/**
 * Whitelist of fields allowed to be saved
 *
 * @var array
 */
	var $whitelist = array();
/**
 * Enter description here...
 *
 * @var boolean
 */
	var $cacheSources = true;
/**
 * Default association keys
 *
 * @var array
 * @access private
 */
	var $__associationKeys = array('belongsTo' => array('className', 'foreignKey', 'conditions', 'fields', 'order', 'counterCache'),
												'hasOne' => array('className', 'foreignKey','conditions', 'fields','order', 'dependent'),
												'hasMany' => array('className', 'foreignKey', 'conditions', 'fields', 'order', 'limit', 'offset', 'dependent', 'exclusive', 'finderQuery', 'counterQuery'),
												'hasAndBelongsToMany' => array('className', 'joinTable', 'foreignKey', 'associationForeignKey', 'conditions', 'fields', 'order', 'limit', 'offset', 'unique', 'finderQuery', 'deleteQuery', 'insertQuery'));
/**
 * Holds provided/generated association key names and other data for all associations
 *
 * @var array
 * @access private
 */
	var $__associations = array('belongsTo', 'hasOne', 'hasMany', 'hasAndBelongsToMany');
/**
 * The last inserted ID of the data that this model created
 *
 * @var int
 * @access private
 */
	var $__insertID = null;
/**
 * The number of records returned by the last query
 *
 * @var int
 * @access private
 */
	var $__numRows = null;
/**
 * The number of records affected by the last query
 *
 * @var int
 * @access private
 */
	var $__affectedRows = null;
/**
 * Holds model associations temporarily to allow for dynamic (un)binding
 *
 * @var array
 * @access private
 */
	var $__backAssociation = array();
/**
 * Constructor. Binds the Model's database table to the object.
 *
 * @param integer $id
 * @param string $table Name of database table to use.
 * @param DataSource $ds DataSource connection object.
 */
	function __construct($id = false, $table = null, $ds = null) {
		parent::__construct();

		if (is_array($id) && isset($id['name'])) {
			$options = array_merge(array('id' => false, 'table' => null, 'ds' => null, 'alias' => null), $id);
			list($id, $table, $ds) = array($options['id'], $options['table'], $options['ds']);
			$this->name = $options['name'];
		}

		if ($this->name === null) {
			$this->name = get_class($this);
		}

		if ($this->primaryKey === null) {
			$this->primaryKey = 'id';
		}

		if (isset($options['alias']) || !empty($options['alias'])) {
			$this->alias = $options['alias'];
			unset($options);
		} else {
			$this->alias = $this->name;
		}
		ClassRegistry::addObject($this->alias, $this);

		$this->id = $id;
		unset($id);

		if ($table === false) {
			$this->useTable = false;
		} elseif ($table) {
			$this->useTable = $table;
		}

		if ($this->useTable !== false) {
			$this->setDataSource($ds);

			if ($this->useTable === null) {
				$this->useTable = Inflector::tableize($this->name);
			}

			if (in_array('settableprefix', get_class_methods($this))) {
				$this->setTablePrefix();
			}

			$this->setSource($this->useTable);
			$this->__createLinks();

			if ($this->displayField == null) {
				if ($this->hasField('title')) {
					$this->displayField = 'title';
				}

				if ($this->hasField('name')) {
					$this->displayField = 'name';
				}

				if ($this->displayField == null) {
					$this->displayField = $this->primaryKey;
				}
			}
		}
	}

/**
 * PHP4 Only
 *
 * Handles custom method calls, like findBy<field> for DB models,
 * and custom RPC calls for remote data sources
 *
 * @param unknown_type $method
 * @param unknown_type $params
 * @param unknown_type $return
 * @return unknown
 * @access protected
 */
	function __call($method, $params, &$return) {
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		$return = $db->query($method, $params, $this);
		if (isset($this->__backAssociation)) {
			$this->__resetAssociations();
		}
		return true;
	}
/**
 * Bind model associations on the fly.
 *
 * @param array $params
 * @return true
 * @access public
 */
	function bindModel($params) {
		foreach ($params as $assoc => $model) {
			if(!isset($this->__backAssociation[$assoc])) {
				$this->__backAssociation[$assoc] = $this->{$assoc};
			}

			foreach ($model as $key => $value) {
				$assocName = $key;

				if (is_numeric($key)) {
					$assocName = $value;
					$value = array();
				}
				$modelName = $assocName;
				$this->{$assoc}[$assocName] = $value;
			}
		}
		$this->__createLinks();
		return true;
	}
/**
 * Turn off associations on the fly.
 *
 * @param array $params
 * @return true
 * @access public
 */
	function unbindModel($params) {
		foreach ($params as $assoc => $models) {
			if(!isset($this->__backAssociation[$assoc])) {
				$this->__backAssociation[$assoc] = $this->{$assoc};
			}

			foreach ($models as $model) {
				$this->__backAssociation = array_merge($this->__backAssociation, $this->{$assoc});
				unset ($this->{$assoc}[$model]);
			}
		}
		return true;
	}
/**
 * Private helper method to create a set of associations.
 *
 * @access private
 */
	function __createLinks() {

		foreach ($this->__associations as $type) {
			if (!is_array($this->{$type})) {
				$this->{$type} = explode(',', $this->{$type});

				foreach ($this->{$type} as $i => $className) {
					$className = trim($className);
					unset ($this->{$type}[$i]);
					$this->{$type}[$className] = array();
				}
			}

			foreach ($this->{$type} as $assoc => $value) {
				if (is_numeric($assoc)) {
					unset ($this->{$type}[$assoc]);
					$assoc = $value;
					$value = array();
					$this->{$type}[$assoc] = $value;
				}

				$className = $assoc;

				if (isset($value['className']) && !empty($value['className'])) {
					$className = $value['className'];
				}
				$this->__constructLinkedModel($assoc, $className);
			}
		}

		foreach ($this->__associations as $type) {
			$this->__generateAssociation($type);
		}
	}

/**
 * Private helper method to create associated models of given class.
 * @param string $assoc
 * @param string $className Class name
 * @param string $type Type of assocation
 * @access private
 */
	function __constructLinkedModel($assoc, $className) {
		if(empty($className)) {
			$className = $assoc;
		}

		if (!class_exists($className)) {
			loadModel($className);
		}
		$colKey = Inflector::underscore($className);
		$model = array('name' => $className, 'alias' => $assoc);

		if (ClassRegistry::isKeySet($colKey)) {
			$this->{$assoc} =& ClassRegistry::getObject($colKey);
			$this->{$className} =& $this->{$assoc};
		} else {
			$this->{$assoc} =& new $className($model);
			$this->{$className} =& $this->{$assoc};
		}
		$this->tableToModel[$this->{$assoc}->table] = $className;
		$this->modelToTable[$assoc] = $this->{$assoc}->table;
	}
/**
 * Build array-based association from string.
 *
 * @param string $type "Belongs", "One", "Many", "ManyTo"
 * @access private
 */
	function __generateAssociation($type) {
		foreach ($this->{$type}as $assocKey => $assocData) {
			$class = $assocKey;

			foreach ($this->__associationKeys[$type] as $key) {
				if (!isset($this->{$type}[$assocKey][$key]) || $this->{$type}[$assocKey][$key] == null) {
					$data = '';

					switch($key) {
						case 'fields':
							$data = '';
						break;

						case 'foreignKey':
							$data = Inflector::singularize($this->table) . '_id';

							if ($type == 'belongsTo') {
								$data = Inflector::singularize($this->{$class}->table) . '_id';
							}
						break;

						case 'associationForeignKey':
							$data = Inflector::singularize($this->{$class}->table) . '_id';
						break;

						case 'joinTable':
							$tables = array($this->table, $this->{$class}->table);
							sort ($tables);
							$data = $tables[0] . '_' . $tables[1];
						break;

						case 'className':
							$data = $class;
						break;
					}

					$this->{$type}[$assocKey][$key] = $data;
				}

				if ($key == 'foreignKey' && !isset($this->keyToTable[$this->{$type}[$assocKey][$key]])) {
					$this->keyToTable[$this->{$type}[$assocKey][$key]][0] = $this->{$class}->table;
					$this->keyToTable[$this->{$type}[$assocKey][$key]][1] = $this->{$class}->alias;
				}
			}
		}
	}
/**
 * Sets a custom table for your controller class. Used by your controller to select a database table.
 *
 * @param string $tableName Name of the custom table
 * @access public
 */
	function setSource($tableName) {
		$this->setDataSource($this->useDbConfig);
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		$db->cacheSources = $this->cacheSources;

		if ($db->isInterfaceSupported('listSources')) {
			$sources = $db->listSources();
			if (is_array($sources) && !in_array(low($this->tablePrefix . $tableName), array_map('low', $sources))) {
				return $this->cakeError('missingTable', array(array(
												'className' => $this->alias,
												'table' => $this->tablePrefix . $tableName)));

			}
			$this->_tableInfo = null;
		}
		$this->table = $this->useTable = $tableName;
		$this->tableToModel[$this->table] = $this->alias;
		$this->loadInfo();
	}
/**
 * This function does two things: 1) it scans the array $one for the primary key,
 * and if that's found, it sets the current id to the value of $one[id].
 * For all other keys than 'id' the keys and values of $one are copied to the 'data' property of this object.
 * 2) Returns an array with all of $one's keys and values.
 * (Alternative indata: two strings, which are mangled to
 * a one-item, two-dimensional array using $one for a key and $two as its value.)
 *
 * @param mixed $one Array or string of data
 * @param string $two Value string for the alternative indata method
 * @return array
 * @access public
 */
	function set($one, $two = null) {
		if (is_array($one)) {
			if (countdim($one) == 1) {
				$data = array($this->alias => $one);
			} else {
				$data = $one;
			}
		} else {
			$data = array($this->alias => array($one => $two));
		}

		foreach ($data as $n => $v) {
			if (is_array($v)) {

				foreach ($v as $x => $y) {
					if (empty($this->whitelist) || (in_array($x, $this->whitelist) || $n !== $this->alias)) {
						if (isset($this->validationErrors[$x])) {
							unset ($this->validationErrors[$x]);
						}

						if ($n == $this->name || is_array($y)) {
							if ($x === $this->primaryKey) {
								$this->id = $y;
							}
							$this->data[$n][$x] = $y;
						}
					}
				}
			}
		}
		return $data;
	}
/**
 * Returns an array of table metadata (column names and types) from the database.
 *
 * @return array Array of table metadata
 * @access public
 */
	function loadInfo() {
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		$db->cacheSources = $this->cacheSources;

		if (!is_object($this->_tableInfo) && $db->isInterfaceSupported('describe') && $this->useTable !== false) {
			$info = new Set($db->describe($this));

			foreach($info->value as $field => $value) {
				$fields[] = am(array('name'=> $field), $value);
			}
			unset($info);
			$this->_tableInfo = new Set($fields);
		} elseif ($this->useTable === false) {
			$this->_tableInfo = new Set();
		}
		return $this->_tableInfo;
	}
/**
 * Returns an associative array of field names and column types.
 *
 * @return array
 * @access public
 */
	function getColumnTypes() {
		$columns = $this->loadInfo();
		$columns = $columns->value;
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		$cols = array();

		foreach ($columns as $col) {
			$cols[$col['name']] = $col['type'];
		}
		return $cols;
	}
/**
 * Returns the column type of a column in the model
 *
 * @param string $column The name of the model column
 * @return string
 * @access public
 */
	function getColumnType($column) {
		$columns = $this->loadInfo();
		$columns = $columns->value;
		$cols = array();

		foreach ($columns as $col) {
			if ($col['name'] == $column) {
				return $col['type'];
			}
		}
		return null;
	}
/**
 * Returns true if this Model has given field in its database table.
 *
 * @param string $name Name of field to look for
 * @return boolean
 * @access public
 */
	function hasField($name) {
		if (is_array($name)) {
			foreach ($name as $n) {
				if ($this->hasField($n)) {
					return $n;
				}
			}
			return false;
		}

		if (empty($this->_tableInfo)) {
			$this->loadInfo();
		}

		if ($this->_tableInfo != null) {
			return in_array($name, $this->_tableInfo->extract('{n}.name'));
		}
		return false;
	}
/**
 * Initializes the model for writing a new record.
 *
 * @return boolean True
 * @access public
 */
	function create() {
		$this->id = false;
		unset ($this->data);
		$this->data = $this->validationErrors = array();
		return true;
	}
/**
 * @deprecated
 */
	function setId($id) {
		$this->id = $id;
	}
/**
 * Use query() instead.
 * @deprecated
 */
	function findBySql($sql) {
		return $this->query($sql);
	}
/**
 * Returns a list of fields from the database
 *
 * @param mixed $id The ID of the record to read
 * @param mixed $fields String of single fieldname, or an array of fieldnames.
 * @return array Array of database fields
 * @access public
 */
	function read($fields = null, $id = null) {
		$this->validationErrors = array();

		if ($id != null) {
			$this->id = $id;
		}

		$id = $this->id;

		if (is_array($this->id)) {
			$id = $this->id[0];
		}

		if ($this->id !== null && $this->id !== false) {
			$db =& ConnectionManager::getDataSource($this->useDbConfig);
			$field = $db->name($this->alias) . '.' . $db->name($this->primaryKey);
			return $this->find($field . ' = ' . $db->value($id, $this->getColumnType($this->primaryKey)), $fields);
		} else {
			return false;
		}
	}
/**
 * Returns contents of a field in a query matching given conditions.
 *
 * @param string $name Name of field to get
 * @param array $conditions SQL conditions (defaults to NULL)
 * @param string $order SQL ORDER BY fragment
 * @return field contents
 * @access public
 */
	function field($name, $conditions = null, $order = null) {
		if ($conditions === null && $this->id !== false) {
			$conditions = array($this->alias . '.' . $this->primaryKey => $this->id);
		}

		if ($data = $this->find($conditions, $name, $order, 0)) {

			if (strpos($name, '.') === false) {
				if (isset($data[$this->alias][$name])) {
					return $data[$this->alias][$name];
				} else {
					return false;
				}
			} else {
				$name = explode('.', $name);

				if (isset($data[$name[0]][$name[1]])) {
					return $data[$name[0]][$name[1]];
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
	}
/**
 * Saves a single field to the database.
 *
 * @param string $name Name of the table field
 * @param mixed $value Value of the field
 * @param boolean $validate Whether or not this model should validate before saving (defaults to false)
 * @return boolean True on success save
 * @access public
 */
	function saveField($name, $value, $validate = false) {
		return $this->save(array($this->alias => array($name => $value)), $validate);
	}
/**
 * Saves model data to the database.
 * By default, validation occurs before save.
 *
 * @param array $data Data to save.
 * @param boolean $validate If set, validation will be done before the save
 * @param array $fieldList List of fields to allow to be written
 * @return boolean success
 * @access public
 */
	function save($data = null, $validate = true, $fieldList = array()) {
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		$_whitelist = $this->whitelist;

		if (!empty($fieldList)) {
			$this->whitelist = $fieldList;
		} elseif ($fieldList === null) {
			$this->whitelist = array();
		}

		if ($data) {
			if (countdim($data) == 1) {
				$this->set(array($this->alias => $data));
			} else {
				$this->set($data);
			}
		}

		if ($validate && !$this->validates()) {
			$this->whitelist = $_whitelist;
			return false;
		}

		if (!$this->beforeSave()) {
			$this->whitelist = $_whitelist;
			return false;
		}
		$fields = $values = array();

		if (isset($this->data[$this->alias][$this->primaryKey]) && empty($this->data[$this->alias][$this->primaryKey])) {
			unset($this->data[$this->alias][$this->primaryKey]);
		}

		if (count($this->data) > 1) {
			$weHaveMulti = true;
			$joined = false;
		} else {
			$weHaveMulti = false;
		}

		foreach ($this->data as $n => $v) {
			if (isset($weHaveMulti) && isset($v[$n]) && in_array($n, array_keys($this->hasAndBelongsToMany))) {
				$joined[] = $v;
			} else {
				if ($n === $this->alias) {
					foreach (array('created', 'updated', 'modified') as $field) {
						if (array_key_exists($field, $v) && (empty($v[$field]) || $v[$field] === null)) {
							unset($v[$field]);
						}
					}

					foreach ($v as $x => $y) {
						if ($this->hasField($x)) {
							$fields[] = $x;
							$values[] = $y;
						}
					}
				}
			}
		}
		$exists = $this->exists();

		if (!$exists && $this->hasField('created') && !in_array('created', $fields)) {
			$fields[] = 'created';
			$values[] = date('Y-m-d H:i:s');
		}

		if ($this->hasField('modified') && !in_array('modified', $fields)) {
			$fields[] = 'modified';
			$values[] = date('Y-m-d H:i:s');
		}

		if ($this->hasField('updated') && !in_array('updated', $fields)) {
			$fields[] = 'updated';
			$values[] = date('Y-m-d H:i:s');
		}

		if (!$exists) {
			$this->id = false;
		}
		$this->whitelist = $_whitelist;

		if (count($fields)) {
			if (!empty($this->id)) {
				if ($db->update($this, $fields, $values)) {
					if (!empty($joined)) {
						$this->__saveMulti($joined, $this->id);
					}

					$this->afterSave();
					$this->data = false;
					$this->_clearCache();
					return true;
				} else {
					return false;
				}
			} else {
				if ($db->create($this, $fields, $values)) {
					if (!empty($joined)) {
						$this->__saveMulti($joined, $this->id);
					}

					$this->afterSave();
					$this->data = false;
					$this->_clearCache();
					$this->validationErrors = array();
					return true;
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
	}
/**
 * Saves model hasAndBelongsToMany data to the database.
 *
 * @param array $joined Data to save.
 * @param string $id
 * @return void
 * @access private
 */
	function __saveMulti($joined, $id) {
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		foreach ($joined as $x => $y) {
			foreach ($y as $assoc => $value) {
				if (isset($this->hasAndBelongsToMany[$assoc])) {
					$joinTable[$assoc] = $this->hasAndBelongsToMany[$assoc]['joinTable'];
					$mainKey[$assoc] = $this->hasAndBelongsToMany[$assoc]['foreignKey'];
					$keys[] = $this->hasAndBelongsToMany[$assoc]['foreignKey'];
					$keys[] = $this->hasAndBelongsToMany[$assoc]['associationForeignKey'];
					$fields[$assoc]  = join(',', $keys);
					unset($keys);

					foreach ($value as $update) {
						if (!empty($update)) {
							$values[]  = $db->value($id, $this->getColumnType($this->primaryKey));
							$values[]  = $db->value($update);
							$values    = join(',', $values);
							$newValues[] = "({$values})";
							unset ($values);
						}
					}

					if (!empty($newValues)) {
						$newValue[$assoc] = $newValues;
						unset($newValues);
					} else {
						$newValue[$assoc] = array();
					}
				}
			}
		}

		if (isset($joinTable)) {
			$total = count($joinTable);

			if (is_array($newValue)) {
				foreach ($newValue as $loopAssoc => $val) {
					$db =& ConnectionManager::getDataSource($this->useDbConfig);
					$table = $db->name($db->fullTableName($joinTable[$loopAssoc]));
					$db->query("DELETE FROM {$table} WHERE {$mainKey[$loopAssoc]} = '{$id}'");

					if (!empty($newValue[$loopAssoc])) {
						$secondCount = count($newValue[$loopAssoc]);
						for ($x = 0; $x < $secondCount; $x++) {
							$db->query("INSERT INTO {$table} ({$fields[$loopAssoc]}) VALUES {$newValue[$loopAssoc][$x]}");
						}
					}
				}
			}
		}
	}
/**
 * Synonym for del().
 *
 * @param mixed $id
 * @see function del
 * @return boolean True on success
 * @access public
 */
	function remove($id = null, $cascade = true) {
		return $this->del($id, $cascade);
	}
/**
 * Removes record for given id. If no id is given, the current id is used. Returns true on success.
 *
 * @param mixed $id Id of record to delete
 * @return boolean True on success
 * @access public
 */
	function del($id = null, $cascade = true) {
		if ($id) {
			$this->id = $id;
		}
		$id = $this->id;

		if ($this->exists() && $this->beforeDelete()) {
			$db =& ConnectionManager::getDataSource($this->useDbConfig);

			$this->_deleteMulti($id);
			$this->_deleteHasMany($id, $cascade);
			$this->_deleteHasOne($id, $cascade);
			$this->id = $id;

			if ($db->delete($this)) {
				$this->afterDelete();
				$this->_clearCache();
				$this->id = false;
				return true;
			}
		}

		return false;
	}
/**
 * Alias for del()
 *
 * @param mixed $id Id of record to delete
 * @return boolean True on success
 * @access public
 */
	function delete($id = null, $cascade = true) {
		return $this->del($id, $cascade);
	}
/**
 * Cascades model deletes to hasMany relationships.
 *
 * @param string $id
 * @return null
 * @access protected
 */
	function _deleteHasMany($id, $cascade) {
		if (!empty($this->__backAssociation)) {
			$savedAssociatons = $this->__backAssociation;
			$this->__backAssociation = array();
		}
		foreach ($this->hasMany as $assoc => $data) {
			if ($data['dependent'] === true && $cascade === true) {
				$model =& $this->{$data['className']};
				$field = $model->escapeField($data['foreignKey']);
				$model->recursive = 0;
				$records = $model->findAll("$field = '$id'", $model->primaryKey, null, null);

				if ($records != false) {
					foreach ($records as $record) {
						$model->del($record[$data['className']][$model->primaryKey]);
					}
				}
			}
		}
		if (isset($savedAssociatons)) {
			$this->__backAssociation = $savedAssociatons;
		}
	}
/**
 * Cascades model deletes to hasOne relationships.
 *
 * @param string $id
 * @return null
 * @access protected
 */
	function _deleteHasOne($id, $cascade) {
		if (!empty($this->__backAssociation)) {
			$savedAssociatons = $this->__backAssociation;
			$this->__backAssociation = array();
		}
		foreach ($this->hasOne as $assoc => $data) {
			if ($data['dependent'] === true && $cascade === true) {
				$model =& $this->{$data['className']};
				$field = $model->escapeField($data['foreignKey']);
				$model->recursive = 0;
				$records = $model->findAll("$field = '$id'", $model->primaryKey, null, null);

				if ($records != false) {
					foreach ($records as $record) {
						$model->del($record[$data['className']][$model->primaryKey]);
					}
				}
			}
		}
		if (isset($savedAssociatons)) {
			$this->__backAssociation = $savedAssociatons;
		}
	}
/**
 * Cascades model deletes to HABTM join keys.
 *
 * @param string $id
 * @return null
 * @access protected
 */
	function _deleteMulti($id) {
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		foreach ($this->hasAndBelongsToMany as $assoc => $data) {
			$db->execute("DELETE FROM " . $db->name($db->fullTableName($data['joinTable'])) . " WHERE " . $db->name($data['foreignKey']) . " = '{$id}'");
		}
	}
/**
 * Returns true if a record with set id exists.
 *
 * @return boolean True if such a record exists
 * @access public
 */
	function exists() {
		if ($this->id) {
			$id = $this->id;

			if (is_array($id)) {
				$id = $id[0];
			}

			$db =& ConnectionManager::getDataSource($this->useDbConfig);
			return $db->hasAny($this, array($this->primaryKey => $id));
		}
		return false;
	}
/**
 * Returns true if a record that meets given conditions exists
 *
 * @param array $conditions SQL conditions array
 * @return boolean True if such a record exists
 * @access public
 */
	function hasAny($conditions = null) {
		return ($this->findCount($conditions) != false);
	}
/**
 * Return a single row as a resultset array.
 * By using the $recursive parameter, the call can access further "levels of association" than
 * the ones this model is directly associated to.
 *
 * @param array $conditions SQL conditions array
 * @param mixed $fields Either a single string of a field name, or an array of field names
 * @param string $order SQL ORDER BY conditions (e.g. "price DESC" or "name ASC")
 * @param int $recursive The number of levels deep to fetch associated records
 * @return array Array of records
 * @access public
 */
	function find($conditions = null, $fields = null, $order = null, $recursive = null) {
		$data = $this->findAll($conditions, $fields, $order, 1, null, $recursive);

		if (empty($data[0])) {
			return false;
		}

		return $data[0];
	}
/**
 * Returns a resultset array with specified fields from database matching given conditions.
 * By using the $recursive parameter, the call can access further "levels of association" than
 * the ones this model is directly associated to.
 *
 * @param mixed $conditions SQL conditions as a string or as an array('field' =>'value',...)
 * @param mixed $fields Either a single string of a field name, or an array of field names
 * @param string $order SQL ORDER BY conditions (e.g. "price DESC" or "name ASC")
 * @param int $limit SQL LIMIT clause, for calculating items per page.
 * @param int $page Page number, for accessing paged data
 * @param int $recursive The number of levels deep to fetch associated records
 * @return array Array of records
 * @access public
 */
	function findAll($conditions = null, $fields = null, $order = null, $limit = null, $page = 1, $recursive = null) {

		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		$this->id = $this->getID();
		$offset = null;

		if ($page > 1 && $limit != null) {
			$offset = ($page - 1) * $limit;
		}

		if ($order == null) {
			$order = array();
		} else {
			$order = array($order);
		}

		$queryData = array('conditions' => $conditions,
							'fields'    => $fields,
							'joins'     => array(),
							'limit'     => $limit,
							'offset'	=> $offset,
							'order'     => $order
		);

		$ret = $this->beforeFind($queryData);
		if (is_array($ret)) {
			$queryData = $ret;
		} elseif ($ret === false) {
			return null;
		}

		$return = $this->afterFind($db->read($this, $queryData, $recursive));

		if (!empty($this->__backAssociation)) {
			$this->__resetAssociations();
		}

		return $return;
	}
/**
 * Method is called only when bindTo<ModelName>() is used.
 * This resets the association arrays for the model back
 * to the original as set in the model.
 *
 * @return boolean
 * @access private
 */
	function __resetAssociations() {
		foreach ($this->__associations as $type) {
			if (isset($this->__backAssociation[$type])) {
				$this->{$type} = $this->__backAssociation[$type];
			}
		}

		$this->__backAssociation = array();
		return true;
	}
/**
 * Runs a direct query against the bound DataSource, and returns the result.
 *
 * @param string $data Query data
 * @return array
 * @access public
 */
	function execute($data) {
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		$data = $db->fetchAll($data, $this->cacheQueries);

		foreach ($data as $key => $value) {
			foreach ($this->tableToModel as $key1 => $value1) {
				if (isset($data[$key][$key1])) {
					$newData[$key][$value1] = $data[$key][$key1];
				}
			}
		}

		if (!empty($newData)) {
			return $newData;
		}

		return $data;
	}
/**
 * Returns number of rows matching given SQL condition.
 *
 * @param array $conditions SQL conditions array for findAll
 * @param int $recursize The number of levels deep to fetch associated records
 * @return int Number of matching rows
 * @see Model::findAll
 * @access public
 */
	function findCount($conditions = null, $recursive = 0) {
		$db =& ConnectionManager::getDataSource($this->useDbConfig);

		list($data) = $this->findAll($conditions, 'COUNT(*) AS ' . $db->name('count'), null, null, 1, $recursive);

		if (isset($data[0]['count'])) {
			return $data[0]['count'];
		} elseif (isset($data[$this->alias]['count'])) {
			return $data[$this->alias]['count'];
		}

		return false;
	}
/**
 * Special findAll variation for tables joined to themselves.
 * The table needs the fields id and parent_id to work.
 *
 * @param array $conditions Conditions for the findAll() call
 * @param array $fields Fields for the findAll() call
 * @param string $sort SQL ORDER BY statement
 * @return array
 * @access public
 * @todo Perhaps create a Component with this logic
 */
	function findAllThreaded($conditions = null, $fields = null, $sort = null) {
		return $this->__doThread(Model::findAll($conditions, $fields, $sort), null);
	}
/**
 * Private, recursive helper method for findAllThreaded.
 *
 * @param array $data
 * @param string $root NULL or id for root node of operation
 * @return array
 * @access private
 * @see findAllThreaded
 */
	function __doThread($data, $root) {
		$out = array();
		$sizeOf = sizeof($data);

		for ($ii = 0; $ii < $sizeOf; $ii++) {
			if (($data[$ii][$this->alias]['parent_id'] == $root) || (($root === null) && ($data[$ii][$this->alias]['parent_id'] == '0'))) {
				$tmp = $data[$ii];

				if (isset($data[$ii][$this->alias][$this->primaryKey])) {
					$tmp['children'] = $this->__doThread($data, $data[$ii][$this->alias][$this->primaryKey]);
				} else {
					$tmp['children'] = null;
				}

				$out[] = $tmp;
			}
		}

		return $out;
	}
/**
 * Returns an array with keys "prev" and "next" that holds the id's of neighbouring data,
 * which is useful when creating paged lists.
 *
 * @param string $conditions SQL conditions for matching rows
 * @param string $field Field name (parameter for findAll)
 * @param unknown_type $value
 * @return array Array with keys "prev" and "next" that holds the id's
 * @access public
 */
	function findNeighbours($conditions = null, $field, $value) {
		$db =& ConnectionManager::getDataSource($this->useDbConfig);

		if (!is_null($conditions)) {
				$conditions = $conditions . ' AND ';
		}

		@list($prev) = Model::findAll($conditions . $field . ' < ' . $db->value($value), $field, $field . ' DESC', 1, null, 0);
		@list($next) = Model::findAll($conditions . $field . ' > ' . $db->value($value), $field, $field . ' ASC', 1, null, 0);

		if (!isset($prev)) {
			$prev = null;
		}

		if (!isset($next)) {
			$next = null;
		}

		return array('prev' => $prev, 'next' => $next);
	}
/**
 * Returns a resultset for given SQL statement. Generic SQL queries should be made with this method.
 *
 * @param string $sql SQL statement
 * @return array Resultset
 * @access public
 */
	function query() {
		$params = func_get_args();
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		return call_user_func_array(array(&$db, 'query'), $params);
	}
/**
 * Returns true if all fields pass validation, otherwise false.
 *
 * @param array $data POST data
 * @return boolean True if there are no errors
 * @access public
 */
	function validates($data = array()) {
		$errors = $this->invalidFields($data);
		return count($errors) == 0;
	}
/**
 * Returns an array of invalid fields.
 *
 * @param array $data
 * @return array Array of invalid fields or boolean case any error occurs
 * @access public
 */
	function invalidFields($data = array()) {
		if (empty($data)) {
			$data = $this->data;
		}

		if (!$this->beforeValidate()) {
			return $this->validationErrors;
		}

		if (!isset($this->validate)) {
			return $this->validationErrors;
		}

		if (!empty($data)) {
			$data = $data;
		} elseif (isset($this->data)) {
			$data = $this->data;
		}

		if (isset($data[$this->alias])) {
			$data = $data[$this->alias];
		}

		foreach ($this->validate as $field_name => $validator) {
			if (isset($data[$field_name]) && !preg_match($validator, $data[$field_name])) {
				$this->invalidate($field_name);
			}
		}
		return $this->validationErrors;
	}
/**
 * Sets a field as invalid
 *
 * @param string $field The name of the field to invalidate
 * @return void
 * @access public
 */
	function invalidate($field) {
		if (!is_array($this->validationErrors)) {
			$this->validationErrors = array();
		}
		$this->validationErrors[$field] = 1;
	}
/**
 * Returns true if given field name is a foreign key in this Model.
 *
 * @param string $field Returns true if the input string ends in "_id"
 * @return True if the field is a foreign key listed in the belongsTo array.
 * @access public
 */
	function isForeignKey($field) {
		$foreignKeys = array();

		if (count($this->belongsTo)) {
			foreach ($this->belongsTo as $assoc => $data) {
				$foreignKeys[] = $data['foreignKey'];
			}
		}
		return (bool)(in_array($field, $foreignKeys));
	}
/**
 * Gets the display field for this model
 *
 * @return string The name of the display field for this Model (i.e. 'name', 'title').
 * @access public
 */
	function getDisplayField() {
		return $this->displayField;
	}
/**
 * Returns a resultset array with specified fields from database matching given conditions.
 * Method can be used to generate option lists for SELECT elements.
 *
 * @param mixed $conditions SQL conditions as a string or as an array('field' =>'value',...)
 * @param string $order SQL ORDER BY conditions (e.g. "price DESC" or "name ASC")
 * @param int $limit SQL LIMIT clause, for calculating items per page
 * @param string $keyPath A string path to the key, i.e. "{n}.Post.id"
 * @param string $valuePath A string path to the value, i.e. "{n}.Post.title"
 * @return array An associative array of records, where the id is the key, and the display field is the value
 * @access public
 */
	function generateList($conditions = null, $order = null, $limit = null, $keyPath = null, $valuePath = null) {
		if ($keyPath == null && $valuePath == null && $this->hasField($this->displayField)) {
			$fields = array($this->primaryKey, $this->displayField);
		} else {
			$fields = null;
		}
		$recursive = $this->recursive;

		if ($recursive >= 1) {
			$this->recursive = -1;
		}
		$result = $this->findAll($conditions, $fields, $order, $limit);
		$this->recursive = $recursive;

		if (!$result) {
			return false;
		}

		if ($keyPath == null) {
			$keyPath = '{n}.' . $this->alias . '.' . $this->primaryKey;
		}

		if ($valuePath == null) {
			$valuePath = '{n}.' . $this->alias . '.' . $this->displayField;
		}

		$keys = Set::extract($result, $keyPath);
		$vals = Set::extract($result, $valuePath);

		if (!empty($keys) && !empty($vals)) {
			$return = array_combine($keys, $vals);
			return $return;
		}
		return null;
	}
/**
 * Escapes the field name and prepends the model name. Escaping will be done according to the current database driver's rules.
 *
 * @param unknown_type $field
 * @return string The name of the escaped field for this Model (i.e. id becomes `Post`.`id`).
 * @access public
 */
	function escapeField($field) {
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		return $db->name($this->alias) . '.' . $db->name($field);
	}
/**
 * Returns the current record's ID
 *
 * @param unknown_type $list
 * @return mixed The ID of the current record
 * @access public
 */
	function getID($list = 0) {
		if (!is_array($this->id)) {
			return $this->id;
		}

		if (count($this->id) == 0) {
			return false;
		}

		if (isset($this->id[$list])) {
			return $this->id[$list];
		}

		foreach ($this->id as $id) {
			return $id;
		}

		return false;
	}
/**
 * Returns the ID of the last record this Model inserted
 *
 * @return mixed
 * @access public
 */
	function getLastInsertID() {
		return $this->getInsertID();
	}
/**
 * Returns the ID of the last record this Model inserted
 *
 * @return mixed
 * @access public
 */
	function getInsertID() {
		return $this->__insertID;
	}
/**
 * Sets the ID of the last record this Model inserted
 *
 * @param mixed $id
 * @return void
 */
	function setInsertID($id) {
		$this->__insertID = $id;
	}
/**
 * Returns the number of rows returned from the last query
 *
 * @return int
 * @access public
 */
	function getNumRows() {
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		return $db->lastNumRows();
	}
/**
 * Returns the number of rows affected by the last query
 *
 * @return int
 * @access public
 */
	function getAffectedRows() {
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		return $db->lastAffected();
	}
/**
 * Sets the DataSource to which this model is bound
 *
 * @param string $dataSource The name of the DataSource, as defined in Connections.php
 * @return boolean True on success
 * @access public
 */
	function setDataSource($dataSource = null) {
		if ($dataSource != null) {
			$this->useDbConfig = $dataSource;
		}
		$db =& ConnectionManager::getDataSource($this->useDbConfig);

		if (!empty($db->config['prefix']) && $this->tablePrefix === null) {
			$this->tablePrefix = $db->config['prefix'];
		}

		if (empty($db) || $db == null || !is_object($db)) {
			return $this->cakeError('missingConnection', array(array('className' => $this->alias)));
		}
	}
/**
 * Before find callback
 *
 * @param array $queryData Data used to execute this query, i.e. conditions, order, etc.
 * @return boolean True if the operation should continue, false if it should abort
 * @access public
 */
	function beforeFind(&$queryData) {
		return true;
	}
/**
 * After find callback. Can be used to modify any results returned by find and findAll.
 *
 * @param mixed $results The results of the find operation
 * @return mixed Result of the find operation
 * @access public
 */
	function afterFind($results) {
		return $results;
	}
/**
 * Before save callback
 *
 * @return boolean True if the operation should continue, false if it should abort
 * @access public
 */
	function beforeSave() {
		return true;
	}
/**
 * After save callback
 *
 * @return boolean
 * @access public
 */
	function afterSave() {
		return true;
	}
/**
 * Before delete callback
 *
 * @return boolean True if the operation should continue, false if it should abort
 * @access public
 */
	function beforeDelete() {
		return true;
	}
/**
 * After delete callback
 *
 * @return boolean
 * @access public
 */
	function afterDelete() {
		return true;
	}
/**
 * Before validate callback
 *
 * @return boolean
 * @access public
 */
	function beforeValidate() {
		return true;
	}
/**
 * DataSource error callback
 *
 * @return void
 */
	function onError() {
	}
/**
 * Private method.  Clears cache for this model
 *
 * @param string $type If null this deletes cached views if CACHE_CHECK is true
 *                     Will be used to allow deleting query cache also
 * @return boolean true on delete
 * @access protected
 */
	function _clearCache($type = null) {
		if ($type === null) {
			if (defined('CACHE_CHECK') && CACHE_CHECK === true) {
				$assoc[] = strtolower(Inflector::pluralize($this->alias));

				foreach ($this->__associations as $key => $association) {
					foreach ($this->$association as $key => $className) {
						$check = strtolower(Inflector::pluralize($className['className']));

						if (!in_array($check, $assoc)) {
							$assoc[] = strtolower(Inflector::pluralize($className['className']));
						}
					}
				}
				clearCache($assoc);
				return true;
			}
		} else {
			//Will use for query cache deleting
		}
	}
/**
 * Called when serializing a model
 *
 * @return array
 * @access public
 */
	function __sleep() {
		$return = array_keys(get_object_vars($this));
		return $return;
	}
/**
 * Called when unserializing a model
 *
 * @return void
 * @access public
 */
	function __wakeup() {
	}
}
// --- PHP4 Only
overload ('Model');
// --- PHP4 Only

?>
