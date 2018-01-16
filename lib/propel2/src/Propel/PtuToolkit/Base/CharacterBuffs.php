<?php

namespace Propel\PtuToolkit\Base;

use \Exception;
use \PDO;
use Propel\PtuToolkit\Battles as ChildBattles;
use Propel\PtuToolkit\BattlesQuery as ChildBattlesQuery;
use Propel\PtuToolkit\CharacterBuffsQuery as ChildCharacterBuffsQuery;
use Propel\PtuToolkit\Characters as ChildCharacters;
use Propel\PtuToolkit\CharactersQuery as ChildCharactersQuery;
use Propel\PtuToolkit\Map\CharacterBuffsTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;

/**
 * Base class that represents a row from the 'character_buffs' table.
 *
 *
 *
 * @package    propel.generator.Propel.PtuToolkit.Base
 */
abstract class CharacterBuffs implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\Propel\\PtuToolkit\\Map\\CharacterBuffsTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var boolean
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var boolean
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = array();

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = array();

    /**
     * The value for the character_buff_id field.
     *
     * @var        int
     */
    protected $character_buff_id;

    /**
     * The value for the character_id field.
     *
     * @var        int
     */
    protected $character_id;

    /**
     * The value for the battle_id field.
     *
     * @var        int
     */
    protected $battle_id;

    /**
     * The value for the value field.
     *
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $value;

    /**
     * The value for the type field.
     *
     * Note: this column has a database default value of: 'ADD'
     * @var        string
     */
    protected $type;

    /**
     * The value for the prereq field.
     *
     * @var        string
     */
    protected $prereq;

    /**
     * The value for the target_stat field.
     *
     * @var        string
     */
    protected $target_stat;

    /**
     * @var        ChildCharacters
     */
    protected $aCharacters;

    /**
     * @var        ChildBattles
     */
    protected $aBattles;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see __construct()
     */
    public function applyDefaultValues()
    {
        $this->value = 0;
        $this->type = 'ADD';
    }

    /**
     * Initializes internal state of Propel\PtuToolkit\Base\CharacterBuffs object.
     * @see applyDefaults()
     */
    public function __construct()
    {
        $this->applyDefaultValues();
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return boolean True if the object has been modified.
     */
    public function isModified()
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param  string  $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return boolean True if $col has been modified.
     */
    public function isColumnModified($col)
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns()
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return boolean true, if the object has never been persisted.
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param boolean $b the state of the object.
     */
    public function setNew($b)
    {
        $this->new = (boolean) $b;
    }

    /**
     * Whether this object has been deleted.
     * @return boolean The deleted state of this object.
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param  boolean $b The deleted state of this object.
     * @return void
     */
    public function setDeleted($b)
    {
        $this->deleted = (boolean) $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param  string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified($col = null)
    {
        if (null !== $col) {
            if (isset($this->modifiedColumns[$col])) {
                unset($this->modifiedColumns[$col]);
            }
        } else {
            $this->modifiedColumns = array();
        }
    }

    /**
     * Compares this with another <code>CharacterBuffs</code> instance.  If
     * <code>obj</code> is an instance of <code>CharacterBuffs</code>, delegates to
     * <code>equals(CharacterBuffs)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param  mixed   $obj The object to compare to.
     * @return boolean Whether equal to the object specified.
     */
    public function equals($obj)
    {
        if (!$obj instanceof static) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey() || null === $obj->getPrimaryKey()) {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns()
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param  string  $name The virtual column name
     * @return boolean
     */
    public function hasVirtualColumn($name)
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param  string $name The virtual column name
     * @return mixed
     *
     * @throws PropelException
     */
    public function getVirtualColumn($name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of inexistent virtual column %s.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name  The virtual column name
     * @param mixed  $value The value to give to the virtual column
     *
     * @return $this|CharacterBuffs The current object, for fluid interface
     */
    public function setVirtualColumn($name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param  string  $msg
     * @param  int     $priority One of the Propel::LOG_* logging levels
     * @return boolean
     */
    protected function log($msg, $priority = Propel::LOG_INFO)
    {
        return Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param  mixed   $parser                 A AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param  boolean $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @return string  The exported data
     */
    public function exportTo($parser, $includeLazyLoadColumns = true)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     */
    public function __sleep()
    {
        $this->clearAllReferences();

        $cls = new \ReflectionClass($this);
        $propertyNames = [];
        $serializableProperties = array_diff($cls->getProperties(), $cls->getProperties(\ReflectionProperty::IS_STATIC));

        foreach($serializableProperties as $property) {
            $propertyNames[] = $property->getName();
        }

        return $propertyNames;
    }

    /**
     * Get the [character_buff_id] column value.
     *
     * @return int
     */
    public function getCharacterBuffId()
    {
        return $this->character_buff_id;
    }

    /**
     * Get the [character_id] column value.
     *
     * @return int
     */
    public function getCharacterId()
    {
        return $this->character_id;
    }

    /**
     * Get the [battle_id] column value.
     *
     * @return int
     */
    public function getBattleId()
    {
        return $this->battle_id;
    }

    /**
     * Get the [value] column value.
     *
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the [type] column value.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the [prereq] column value.
     *
     * @return string
     */
    public function getPrereq()
    {
        return $this->prereq;
    }

    /**
     * Get the [target_stat] column value.
     *
     * @return string
     */
    public function getTargetStat()
    {
        return $this->target_stat;
    }

    /**
     * Set the value of [character_buff_id] column.
     *
     * @param int $v new value
     * @return $this|\Propel\PtuToolkit\CharacterBuffs The current object (for fluent API support)
     */
    public function setCharacterBuffId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->character_buff_id !== $v) {
            $this->character_buff_id = $v;
            $this->modifiedColumns[CharacterBuffsTableMap::COL_CHARACTER_BUFF_ID] = true;
        }

        return $this;
    } // setCharacterBuffId()

    /**
     * Set the value of [character_id] column.
     *
     * @param int $v new value
     * @return $this|\Propel\PtuToolkit\CharacterBuffs The current object (for fluent API support)
     */
    public function setCharacterId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->character_id !== $v) {
            $this->character_id = $v;
            $this->modifiedColumns[CharacterBuffsTableMap::COL_CHARACTER_ID] = true;
        }

        if ($this->aCharacters !== null && $this->aCharacters->getCharacterId() !== $v) {
            $this->aCharacters = null;
        }

        return $this;
    } // setCharacterId()

    /**
     * Set the value of [battle_id] column.
     *
     * @param int $v new value
     * @return $this|\Propel\PtuToolkit\CharacterBuffs The current object (for fluent API support)
     */
    public function setBattleId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->battle_id !== $v) {
            $this->battle_id = $v;
            $this->modifiedColumns[CharacterBuffsTableMap::COL_BATTLE_ID] = true;
        }

        if ($this->aBattles !== null && $this->aBattles->getBattleId() !== $v) {
            $this->aBattles = null;
        }

        return $this;
    } // setBattleId()

    /**
     * Set the value of [value] column.
     *
     * @param int $v new value
     * @return $this|\Propel\PtuToolkit\CharacterBuffs The current object (for fluent API support)
     */
    public function setValue($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->value !== $v) {
            $this->value = $v;
            $this->modifiedColumns[CharacterBuffsTableMap::COL_VALUE] = true;
        }

        return $this;
    } // setValue()

    /**
     * Set the value of [type] column.
     *
     * @param string $v new value
     * @return $this|\Propel\PtuToolkit\CharacterBuffs The current object (for fluent API support)
     */
    public function setType($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->type !== $v) {
            $this->type = $v;
            $this->modifiedColumns[CharacterBuffsTableMap::COL_TYPE] = true;
        }

        return $this;
    } // setType()

    /**
     * Set the value of [prereq] column.
     *
     * @param string $v new value
     * @return $this|\Propel\PtuToolkit\CharacterBuffs The current object (for fluent API support)
     */
    public function setPrereq($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->prereq !== $v) {
            $this->prereq = $v;
            $this->modifiedColumns[CharacterBuffsTableMap::COL_PREREQ] = true;
        }

        return $this;
    } // setPrereq()

    /**
     * Set the value of [target_stat] column.
     *
     * @param string $v new value
     * @return $this|\Propel\PtuToolkit\CharacterBuffs The current object (for fluent API support)
     */
    public function setTargetStat($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->target_stat !== $v) {
            $this->target_stat = $v;
            $this->modifiedColumns[CharacterBuffsTableMap::COL_TARGET_STAT] = true;
        }

        return $this;
    } // setTargetStat()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
            if ($this->value !== 0) {
                return false;
            }

            if ($this->type !== 'ADD') {
                return false;
            }

        // otherwise, everything was equal, so return TRUE
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array   $row       The row returned by DataFetcher->fetch().
     * @param int     $startcol  0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @param string  $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : CharacterBuffsTableMap::translateFieldName('CharacterBuffId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->character_buff_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : CharacterBuffsTableMap::translateFieldName('CharacterId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->character_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : CharacterBuffsTableMap::translateFieldName('BattleId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->battle_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : CharacterBuffsTableMap::translateFieldName('Value', TableMap::TYPE_PHPNAME, $indexType)];
            $this->value = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : CharacterBuffsTableMap::translateFieldName('Type', TableMap::TYPE_PHPNAME, $indexType)];
            $this->type = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : CharacterBuffsTableMap::translateFieldName('Prereq', TableMap::TYPE_PHPNAME, $indexType)];
            $this->prereq = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : CharacterBuffsTableMap::translateFieldName('TargetStat', TableMap::TYPE_PHPNAME, $indexType)];
            $this->target_stat = (null !== $col) ? (string) $col : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 7; // 7 = CharacterBuffsTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Propel\\PtuToolkit\\CharacterBuffs'), 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {
        if ($this->aCharacters !== null && $this->character_id !== $this->aCharacters->getCharacterId()) {
            $this->aCharacters = null;
        }
        if ($this->aBattles !== null && $this->battle_id !== $this->aBattles->getBattleId()) {
            $this->aBattles = null;
        }
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param      boolean $deep (optional) Whether to also de-associated any related objects.
     * @param      ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(CharacterBuffsTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildCharacterBuffsQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aCharacters = null;
            $this->aBattles = null;
        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see CharacterBuffs::setDeleted()
     * @see CharacterBuffs::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(CharacterBuffsTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildCharacterBuffsQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $this->setDeleted(true);
            }
        });
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($this->alreadyInSave) {
            return 0;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(CharacterBuffsTableMap::DATABASE_NAME);
        }

        return $con->transaction(function () use ($con) {
            $ret = $this->preSave($con);
            $isInsert = $this->isNew();
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                CharacterBuffsTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }

            return $affectedRows;
        });
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            // We call the save method on the following object(s) if they
            // were passed to this object by their corresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aCharacters !== null) {
                if ($this->aCharacters->isModified() || $this->aCharacters->isNew()) {
                    $affectedRows += $this->aCharacters->save($con);
                }
                $this->setCharacters($this->aCharacters);
            }

            if ($this->aBattles !== null) {
                if ($this->aBattles->isModified() || $this->aBattles->isNew()) {
                    $affectedRows += $this->aBattles->save($con);
                }
                $this->setBattles($this->aBattles);
            }

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                    $affectedRows += 1;
                } else {
                    $affectedRows += $this->doUpdate($con);
                }
                $this->resetModified();
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @throws PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[CharacterBuffsTableMap::COL_CHARACTER_BUFF_ID] = true;
        if (null !== $this->character_buff_id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . CharacterBuffsTableMap::COL_CHARACTER_BUFF_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(CharacterBuffsTableMap::COL_CHARACTER_BUFF_ID)) {
            $modifiedColumns[':p' . $index++]  = 'character_buff_id';
        }
        if ($this->isColumnModified(CharacterBuffsTableMap::COL_CHARACTER_ID)) {
            $modifiedColumns[':p' . $index++]  = 'character_id';
        }
        if ($this->isColumnModified(CharacterBuffsTableMap::COL_BATTLE_ID)) {
            $modifiedColumns[':p' . $index++]  = 'battle_id';
        }
        if ($this->isColumnModified(CharacterBuffsTableMap::COL_VALUE)) {
            $modifiedColumns[':p' . $index++]  = 'value';
        }
        if ($this->isColumnModified(CharacterBuffsTableMap::COL_TYPE)) {
            $modifiedColumns[':p' . $index++]  = 'type';
        }
        if ($this->isColumnModified(CharacterBuffsTableMap::COL_PREREQ)) {
            $modifiedColumns[':p' . $index++]  = 'prereq';
        }
        if ($this->isColumnModified(CharacterBuffsTableMap::COL_TARGET_STAT)) {
            $modifiedColumns[':p' . $index++]  = 'target_stat';
        }

        $sql = sprintf(
            'INSERT INTO character_buffs (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'character_buff_id':
                        $stmt->bindValue($identifier, $this->character_buff_id, PDO::PARAM_INT);
                        break;
                    case 'character_id':
                        $stmt->bindValue($identifier, $this->character_id, PDO::PARAM_INT);
                        break;
                    case 'battle_id':
                        $stmt->bindValue($identifier, $this->battle_id, PDO::PARAM_INT);
                        break;
                    case 'value':
                        $stmt->bindValue($identifier, $this->value, PDO::PARAM_INT);
                        break;
                    case 'type':
                        $stmt->bindValue($identifier, $this->type, PDO::PARAM_STR);
                        break;
                    case 'prereq':
                        $stmt->bindValue($identifier, $this->prereq, PDO::PARAM_STR);
                        break;
                    case 'target_stat':
                        $stmt->bindValue($identifier, $this->target_stat, PDO::PARAM_STR);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setCharacterBuffId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @return Integer Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = CharacterBuffsTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getCharacterBuffId();
                break;
            case 1:
                return $this->getCharacterId();
                break;
            case 2:
                return $this->getBattleId();
                break;
            case 3:
                return $this->getValue();
                break;
            case 4:
                return $this->getType();
                break;
            case 5:
                return $this->getPrereq();
                break;
            case 6:
                return $this->getTargetStat();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {

        if (isset($alreadyDumpedObjects['CharacterBuffs'][$this->hashCode()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['CharacterBuffs'][$this->hashCode()] = true;
        $keys = CharacterBuffsTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getCharacterBuffId(),
            $keys[1] => $this->getCharacterId(),
            $keys[2] => $this->getBattleId(),
            $keys[3] => $this->getValue(),
            $keys[4] => $this->getType(),
            $keys[5] => $this->getPrereq(),
            $keys[6] => $this->getTargetStat(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aCharacters) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'characters';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'characters';
                        break;
                    default:
                        $key = 'Characters';
                }

                $result[$key] = $this->aCharacters->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aBattles) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'battles';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'battles';
                        break;
                    default:
                        $key = 'Battles';
                }

                $result[$key] = $this->aBattles->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param  string $name
     * @param  mixed  $value field value
     * @param  string $type The type of fieldname the $name is of:
     *                one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                Defaults to TableMap::TYPE_PHPNAME.
     * @return $this|\Propel\PtuToolkit\CharacterBuffs
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = CharacterBuffsTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param  int $pos position in xml schema
     * @param  mixed $value field value
     * @return $this|\Propel\PtuToolkit\CharacterBuffs
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setCharacterBuffId($value);
                break;
            case 1:
                $this->setCharacterId($value);
                break;
            case 2:
                $this->setBattleId($value);
                break;
            case 3:
                $this->setValue($value);
                break;
            case 4:
                $this->setType($value);
                break;
            case 5:
                $this->setPrereq($value);
                break;
            case 6:
                $this->setTargetStat($value);
                break;
        } // switch()

        return $this;
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = CharacterBuffsTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setCharacterBuffId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setCharacterId($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setBattleId($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setValue($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setType($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setPrereq($arr[$keys[5]]);
        }
        if (array_key_exists($keys[6], $arr)) {
            $this->setTargetStat($arr[$keys[6]]);
        }
    }

     /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     * @param string $keyType The type of keys the array uses.
     *
     * @return $this|\Propel\PtuToolkit\CharacterBuffs The current object, for fluid interface
     */
    public function importFrom($parser, $data, $keyType = TableMap::TYPE_PHPNAME)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), $keyType);

        return $this;
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(CharacterBuffsTableMap::DATABASE_NAME);

        if ($this->isColumnModified(CharacterBuffsTableMap::COL_CHARACTER_BUFF_ID)) {
            $criteria->add(CharacterBuffsTableMap::COL_CHARACTER_BUFF_ID, $this->character_buff_id);
        }
        if ($this->isColumnModified(CharacterBuffsTableMap::COL_CHARACTER_ID)) {
            $criteria->add(CharacterBuffsTableMap::COL_CHARACTER_ID, $this->character_id);
        }
        if ($this->isColumnModified(CharacterBuffsTableMap::COL_BATTLE_ID)) {
            $criteria->add(CharacterBuffsTableMap::COL_BATTLE_ID, $this->battle_id);
        }
        if ($this->isColumnModified(CharacterBuffsTableMap::COL_VALUE)) {
            $criteria->add(CharacterBuffsTableMap::COL_VALUE, $this->value);
        }
        if ($this->isColumnModified(CharacterBuffsTableMap::COL_TYPE)) {
            $criteria->add(CharacterBuffsTableMap::COL_TYPE, $this->type);
        }
        if ($this->isColumnModified(CharacterBuffsTableMap::COL_PREREQ)) {
            $criteria->add(CharacterBuffsTableMap::COL_PREREQ, $this->prereq);
        }
        if ($this->isColumnModified(CharacterBuffsTableMap::COL_TARGET_STAT)) {
            $criteria->add(CharacterBuffsTableMap::COL_TARGET_STAT, $this->target_stat);
        }

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @throws LogicException if no primary key is defined
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = ChildCharacterBuffsQuery::create();
        $criteria->add(CharacterBuffsTableMap::COL_CHARACTER_BUFF_ID, $this->character_buff_id);

        return $criteria;
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        $validPk = null !== $this->getCharacterBuffId();

        $validPrimaryKeyFKs = 0;
        $primaryKeyFKs = [];

        if ($validPk) {
            return crc32(json_encode($this->getPrimaryKey(), JSON_UNESCAPED_UNICODE));
        } elseif ($validPrimaryKeyFKs) {
            return crc32(json_encode($primaryKeyFKs, JSON_UNESCAPED_UNICODE));
        }

        return spl_object_hash($this);
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getCharacterBuffId();
    }

    /**
     * Generic method to set the primary key (character_buff_id column).
     *
     * @param       int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setCharacterBuffId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {
        return null === $this->getCharacterBuffId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \Propel\PtuToolkit\CharacterBuffs (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setCharacterId($this->getCharacterId());
        $copyObj->setBattleId($this->getBattleId());
        $copyObj->setValue($this->getValue());
        $copyObj->setType($this->getType());
        $copyObj->setPrereq($this->getPrereq());
        $copyObj->setTargetStat($this->getTargetStat());
        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setCharacterBuffId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param  boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return \Propel\PtuToolkit\CharacterBuffs Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }

    /**
     * Declares an association between this object and a ChildCharacters object.
     *
     * @param  ChildCharacters $v
     * @return $this|\Propel\PtuToolkit\CharacterBuffs The current object (for fluent API support)
     * @throws PropelException
     */
    public function setCharacters(ChildCharacters $v = null)
    {
        if ($v === null) {
            $this->setCharacterId(NULL);
        } else {
            $this->setCharacterId($v->getCharacterId());
        }

        $this->aCharacters = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildCharacters object, it will not be re-added.
        if ($v !== null) {
            $v->addCharacterBuffs($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildCharacters object
     *
     * @param  ConnectionInterface $con Optional Connection object.
     * @return ChildCharacters The associated ChildCharacters object.
     * @throws PropelException
     */
    public function getCharacters(ConnectionInterface $con = null)
    {
        if ($this->aCharacters === null && ($this->character_id != 0)) {
            $this->aCharacters = ChildCharactersQuery::create()->findPk($this->character_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aCharacters->addCharacterBuffss($this);
             */
        }

        return $this->aCharacters;
    }

    /**
     * Declares an association between this object and a ChildBattles object.
     *
     * @param  ChildBattles $v
     * @return $this|\Propel\PtuToolkit\CharacterBuffs The current object (for fluent API support)
     * @throws PropelException
     */
    public function setBattles(ChildBattles $v = null)
    {
        if ($v === null) {
            $this->setBattleId(NULL);
        } else {
            $this->setBattleId($v->getBattleId());
        }

        $this->aBattles = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildBattles object, it will not be re-added.
        if ($v !== null) {
            $v->addCharacterBuffs($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildBattles object
     *
     * @param  ConnectionInterface $con Optional Connection object.
     * @return ChildBattles The associated ChildBattles object.
     * @throws PropelException
     */
    public function getBattles(ConnectionInterface $con = null)
    {
        if ($this->aBattles === null && ($this->battle_id != 0)) {
            $this->aBattles = ChildBattlesQuery::create()->findPk($this->battle_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aBattles->addCharacterBuffss($this);
             */
        }

        return $this->aBattles;
    }

    /**
     * Clears the current object, sets all attributes to their default values and removes
     * outgoing references as well as back-references (from other objects to this one. Results probably in a database
     * change of those foreign objects when you call `save` there).
     */
    public function clear()
    {
        if (null !== $this->aCharacters) {
            $this->aCharacters->removeCharacterBuffs($this);
        }
        if (null !== $this->aBattles) {
            $this->aBattles->removeCharacterBuffs($this);
        }
        $this->character_buff_id = null;
        $this->character_id = null;
        $this->battle_id = null;
        $this->value = null;
        $this->type = null;
        $this->prereq = null;
        $this->target_stat = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->applyDefaultValues();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references and back-references to other model objects or collections of model objects.
     *
     * This method is used to reset all php object references (not the actual reference in the database).
     * Necessary for object serialisation.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
        } // if ($deep)

        $this->aCharacters = null;
        $this->aBattles = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(CharacterBuffsTableMap::DEFAULT_STRING_FORMAT);
    }

    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preSave(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preSave')) {
            return parent::preSave($con);
        }
        return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface $con
     */
    public function postSave(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postSave')) {
            parent::postSave($con);
        }
    }

    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preInsert')) {
            return parent::preInsert($con);
        }
        return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postInsert')) {
            parent::postInsert($con);
        }
    }

    /**
     * Code to be run before updating the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preUpdate')) {
            return parent::preUpdate($con);
        }
        return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface $con
     */
    public function postUpdate(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postUpdate')) {
            parent::postUpdate($con);
        }
    }

    /**
     * Code to be run before deleting the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preDelete(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preDelete')) {
            return parent::preDelete($con);
        }
        return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface $con
     */
    public function postDelete(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postDelete')) {
            parent::postDelete($con);
        }
    }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed  $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);

            return $this->importFrom($format, reset($params));
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = isset($params[0]) ? $params[0] : true;

            return $this->exportTo($format, $includeLazyLoadColumns);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
