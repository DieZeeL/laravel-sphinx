<?php

namespace DieZeeL\Database\SphinxConnection\Schema;

use Closure;
use BadMethodCallException;
use DieZeeL\Database\SphinxConnection\SphinxConnection;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Fluent;
use Illuminate\Database\Connection;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\Schema\Grammars\Grammar;

//use \Illuminate\Database\Schema\Blueprint as IlluminateBlueprint;

class Blueprint //extends IlluminateBlueprint
{
    use Macroable;

    /**
     * The table the blueprint describes.
     *
     * @var string
     */
    protected $table;

    /**
     * The prefix of the table.
     *
     * @var string
     */
    protected $prefix;

    /**
     * The columns that should be added to the table.
     *
     * @var \Illuminate\Database\Schema\ColumnDefinition[]
     */
    protected $columns = [];

    /**
     * The commands that should be run for the table.
     *
     * @var \Illuminate\Support\Fluent[]
     */
    protected $commands = [];

    /**
     * The storage engine that should be used for the table.
     *
     * @var string
     */
    public $engine;

    /**
     * The default character set that should be used for the table.
     */
    public $charset;

    /**
     * The collation that should be used for the table.
     */
    public $collation;

    /**
     * Whether to make the table temporary.
     *
     * @var bool
     */
    public $temporary = false;

    /**
     * Create a new schema blueprint.
     *
     * @param string $table
     * @param \Closure|null $callback
     * @param string $prefix
     * @return void
     */
    public function __construct($table, Closure $callback = null, $prefix = '')
    {
        $this->table = $table;
        $this->prefix = $prefix;

        if (!is_null($callback)) {
            $callback($this);
        }
    }

    /**
     * Execute the blueprint against the database.
     *
     * @param \Illuminate\Database\Connection $connection
     * @param \Illuminate\Database\Schema\Grammars\Grammar $grammar
     * @return void
     */
    public function build(Connection $connection, Grammar $grammar)
    {
        foreach ($this->toSql($connection, $grammar) as $statement) {
            $connection->statement($statement);
        }
    }

    /**
     * Get the raw SQL statements for the blueprint.
     *
     * @param \Illuminate\Database\Connection $connection
     * @param \Illuminate\Database\Schema\Grammars\Grammar $grammar
     * @return array
     */
    public function toSql(Connection $connection, Grammar $grammar)
    {
        $this->addImpliedCommands($grammar);

        $statements = [];

        // Each type of command has a corresponding compiler function on the schema
        // grammar which is used to build the necessary SQL statements to build
        // the blueprint element, so we'll just call that compilers function.
        $this->ensureCommandsAreValid($connection);

        foreach ($this->commands as $command) {
            $method = 'compile' . ucfirst($command->name);

            if (method_exists($grammar, $method)) {
                if (!is_null($sql = $grammar->$method($this, $command, $connection))) {
                    $statements = array_merge($statements, (array)$sql);
                }
            }
        }

        return $statements;
    }

    /**
     * Ensure the commands on the blueprint are valid for the connection type.
     *
     * @param \Illuminate\Database\Connection $connection
     * @return void
     *
     * @throws \BadMethodCallException
     */
    protected function ensureCommandsAreValid(Connection $connection)
    {
        if ($connection instanceof SphinxConnection) {
            if ($this->commandsNamed(['dropForeign', 'renameColumn'])->count() > 0) {
                throw new BadMethodCallException(
                    "Sphinx doesn't support this commands."
                );
            }
        }
    }

    /**
     * Get all of the commands matching the given names.
     *
     * @param array $names
     * @return \Illuminate\Support\Collection
     */
    protected function commandsNamed(array $names)
    {
        return collect($this->commands)->filter(function ($command) use ($names) {
            return in_array($command->name, $names);
        });
    }

    /**
     * Add the commands that are implied by the blueprint's state.
     *
     * @param \Illuminate\Database\Schema\Grammars\Grammar $grammar
     * @return void
     */
    protected function addImpliedCommands(Grammar $grammar)
    {
        if (count($this->getAddedColumns()) > 0 && !$this->creating()) {
            array_unshift($this->commands, $this->createCommand('add'));
        }

        if (count($this->getChangedColumns()) > 0 && !$this->creating()) {
            array_unshift($this->commands, $this->createCommand('change'));
        }

        $this->addFluentIndexes();

        $this->addFluentCommands($grammar);
    }

    /**
     * Add the index commands fluently specified on columns.
     * @return void
     * @todo refactor!
     */
    protected function addFluentIndexes()
    {
        foreach ($this->columns as $column) {
            foreach (['primary', 'unique', 'index', 'spatialIndex'] as $index) {
                // If the index has been specified on the given column, but is simply equal
                // to "true" (boolean), no name has been specified for this index so the
                // index method can be called without a name and it will generate one.
                if ($column->{$index} === true) {
                    $this->{$index}($column->name);

                    continue 2;
                }

                // If the index has been specified on the given column, and it has a string
                // value, we'll go ahead and call the index method and pass the name for
                // the index since the developer specified the explicit name for this.
                elseif (isset($column->{$index})) {
                    $this->{$index}($column->name, $column->{$index});

                    continue 2;
                }
            }
        }
    }

    /**
     * Add the fluent commands specified on any columns.
     *
     * @param \Illuminate\Database\Schema\Grammars\Grammar $grammar
     * @return void
     */
    public function addFluentCommands(Grammar $grammar)
    {
        foreach ($this->columns as $column) {
            foreach ($grammar->getFluentCommands() as $commandName) {
                $attributeName = lcfirst($commandName);

                if (!isset($column->{$attributeName})) {
                    continue;
                }

                $value = $column->{$attributeName};

                $this->addCommand(
                    $commandName, compact('value', 'column')
                );
            }
        }
    }

    /**
     * Indicate that the given columns should be dropped.
     *
     * @param array|mixed $columns
     * @return \Illuminate\Support\Fluent
     */
    public function dropColumn($columns)
    {
        $columns = is_array($columns) ? $columns : func_get_args();

        return $this->addCommand('dropColumn', compact('columns'));
    }


    /**
     * Indicate that the timestamp columns should be dropped.
     *
     * @return void
     */
    public function dropTimestamps()
    {
        $this->dropColumn('created_at', 'updated_at');
    }

    /**
     * Indicate that the timestamp columns should be dropped.
     *
     * @return void
     */
    public function dropTimestampsTz()
    {
        $this->dropTimestamps();
    }

    /**
     * Indicate that the soft delete column should be dropped.
     *
     * @param string $column
     * @return void
     */
    public function dropSoftDeletes($column = '__soft_deleted')
    {
        $this->dropColumn($column);
    }

    /**
     * Indicate that the soft delete column should be dropped.
     *
     * @param string $column
     * @return void
     */
    public function dropSoftDeletesTz($column = '__soft_deleted')
    {
        $this->dropSoftDeletes($column);
    }

    /**
     * Create a new char column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function char($column)
    {
        $length = $length ?: Builder::$defaultStringLength;

        return $this->addColumn('STRING', $column);
    }

    /**
     * Create a new string column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function string($column)
    {
        return $this->addColumn('STRING', $column);
    }

    /**
     * Create a new text column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function text($column)
    {
        return $this->addColumn('STRING', $column);
    }

    /**
     * Create a new integer (4-byte) column on the table.
     *
     * @param string $column
     * @param bool $autoIncrement
     * @param bool $unsigned
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function integer($column)
    {
        return $this->addColumn('INTEGER', $column);
    }

    /**
     * Create a new unsigned integer (4-byte) column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function unsignedInteger($column)
    {
        return $this->integer($column);
    }

    /**
     * Create a new float column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function float($column)
    {
        return $this->addColumn('FLOAT', $column);
    }

    /**
     * Create a new double column on the table.
     *
     * @param string $column
     * @param int|null $total
     * @param int|null $places
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function double($column)
    {
        return $this->float($column);
    }

    /**
     * Create a new decimal column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function decimal($column)
    {
        return $this->addColumn('STRING', $column);
    }

    /**
     * Create a new unsigned decimal column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function unsignedDecimal($column)
    {
        return $this->decimal($column);
    }

    /**
     * Create a new boolean column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function boolean($column)
    {
        return $this->addColumn('BOOL', $column);
    }

    /**
     * Create a new set column on the table.
     *
     * @param string $column
     * @param boolean $unsigned
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function set($column, $unsigned = true)
    {
        return $this->addColumn($unsigned ? 'MULTI' : 'MULTI64', $column);
    }

    /**
     * Create a new json column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function json($column)
    {
        return $this->addColumn('JSON', $column);
    }

    /**
     * Create a new jsonb column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function jsonb($column)
    {
        return $this->addColumn('JSON', $column);
    }

    /**
     * Create a new date-time column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function dateTime($column)
    {
        return $this->timestamp($column);
    }

    /**
     * Create a new date-time column (with time zone) on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function dateTimeTz($column)
    {
        return $this->timestamp($column);
    }

    /**
     * Create a new timestamp column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function timestamp($column)
    {
        return $this->addColumn('TIMESTAMP', $column);
    }

    /**
     * Create a new timestamp (with time zone) column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function timestampTz($column)
    {
        return $this->timestamp($column);
    }

    /**
     * Add nullable creation and update timestamps to the table.
     *
     * @return void
     */
    public function timestamps()
    {
        $this->timestamp('created_at');

        $this->timestamp('updated_at');
    }

    /**
     * Add nullable creation and update timestamps to the table.
     *
     * Alias for self::timestamps().
     *
     * @param int $precision
     * @return void
     */
    public function nullableTimestamps()
    {
        $this->timestamps();
    }

    /**
     * Add creation and update timestampTz columns to the table.
     *
     * @param int $precision
     * @return void
     */
    public function timestampsTz($precision = 0)
    {
        $this->timestamps();
    }

    /**
     * Add a "deleted at" timestamp for the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function softDeletes($column = '__soft_deleted')
    {
        return $this->addColumn('BOOL', $column);
    }

    /**
     * Add a "deleted at" timestampTz for the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function softDeletesTz($column = '__soft_deleted')
    {
        return $this->softDeletes($column);
    }

        /**
     * Create a new uuid column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function uuid($column)
    {
        return $this->addColumn('STRING', $column);
    }

    /**
     * Create a new IP address column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function ipAddress($column)
    {
        return $this->addColumn('STRING', $column);
    }

    /**
     * Create a new MAC address column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function macAddress($column)
    {
        return $this->addColumn('STRING', $column);
    }

    /**
     * Create a new geometry column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function geometry($column)
    {
        return $this->addColumn('geometry', $column);
    }

    /**
     * Create a new point column on the table.
     *
     * @param string $column
     * @param int|null $srid
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function point($column, $srid = null)
    {
        return $this->addColumn('point', $column, compact('srid'));
    }

    /**
     * Create a new linestring column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function lineString($column)
    {
        return $this->addColumn('linestring', $column);
    }

    /**
     * Create a new polygon column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function polygon($column)
    {
        return $this->addColumn('polygon', $column);
    }

    /**
     * Create a new geometrycollection column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function geometryCollection($column)
    {
        return $this->addColumn('geometrycollection', $column);
    }

    /**
     * Create a new multipoint column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function multiPoint($column)
    {
        return $this->addColumn('multipoint', $column);
    }

    /**
     * Create a new multilinestring column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function multiLineString($column)
    {
        return $this->addColumn('multilinestring', $column);
    }

    /**
     * Create a new multipolygon column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function multiPolygon($column)
    {
        return $this->addColumn('multipolygon', $column);
    }
    

    /**
     * Add a new column to the blueprint.
     *
     * @param string $type
     * @param string $name
     * @param array $parameters
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function addColumn($type, $name, array $parameters = [])
    {
        $this->columns[] = $column = new ColumnDefinition(
            array_merge(compact('type', 'name'), $parameters)
        );

        return $column;
    }

    /**
     * Remove a column from the schema blueprint.
     *
     * @param string $name
     * @return $this
     */
    public function removeColumn($name)
    {
        $this->columns = array_values(array_filter($this->columns, function ($c) use ($name) {
            return $c['name'] != $name;
        }));

        return $this;
    }

    /**
     * Add a new command to the blueprint.
     *
     * @param string $name
     * @param array $parameters
     * @return \Illuminate\Support\Fluent
     */
    protected function addCommand($name, array $parameters = [])
    {
        $this->commands[] = $command = $this->createCommand($name, $parameters);

        return $command;
    }

    /**
     * Create a new Fluent command.
     *
     * @param string $name
     * @param array $parameters
     * @return \Illuminate\Support\Fluent
     */
    protected function createCommand($name, array $parameters = [])
    {
        return new Fluent(array_merge(compact('name'), $parameters));
    }

    /**
     * Get the table the blueprint describes.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get the columns on the blueprint.
     *
     * @return \Illuminate\Database\Schema\ColumnDefinition[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Get the commands on the blueprint.
     *
     * @return \Illuminate\Support\Fluent[]
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Get the columns on the blueprint that should be added.
     *
     * @return \Illuminate\Database\Schema\ColumnDefinition[]
     */
    public function getAddedColumns()
    {
        return array_filter($this->columns, function ($column) {
            return !$column->change;
        });
    }

    /**
     * Get the columns on the blueprint that should be changed.
     *
     * @return \Illuminate\Database\Schema\ColumnDefinition[]
     */
    public function getChangedColumns()
    {
        return array_filter($this->columns, function ($column) {
            return (bool)$column->change;
        });
    }
}
