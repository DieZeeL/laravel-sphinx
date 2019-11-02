<?php

namespace DieZeeL\Database\SphinxConnection\Schema;

use Closure;
use LogicException;
use Illuminate\Database\Schema\Grammars\Grammar;
use DieZeeL\Database\SphinxConnection\SphinxConnection;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Fluent;
use Illuminate\Database\Connection;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Database\SQLiteConnection;

use \Illuminate\Database\Schema\Blueprint as BaseBlueprint;

class Blueprint extends BaseBlueprint
{

    /**
     * Ensure the commands on the blueprint are valid for the connection type.
     *
     * @param \Illuminate\Database\Connection $connection
     * @return void
     *
     * @throws \LogicException
     */
    protected function ensureCommandsAreValid(Connection $connection)
    {
        if ($connection instanceof SphinxConnection) {
            if ($this->commandsNamed(['dropForeign', 'renameColumn'])->count() > 0) {
                throw new LogicException(
                    "Sphinx doesn't support this commands."
                );
            }
        }
    }

    /**
     * Add the commands that are implied by the blueprint's state.
     *
     * @param \Illuminate\Database\Schema\Grammars\Grammar $grammar
     * @return void
     */
    protected function addImpliedCommands(Grammar $grammar)
    {
        if (count($this->getAddedColumns()) > 0) {
            foreach ($this->columns as $column) {
                $this->addCommand('add', compact('column'));
            }
        }
    }

    /**
     * Indicate that the table needs to be created.
     *
     * @return void
     * @throws LogicException
     */
    public function create()
    {
        throw new LogicException("Sphinx doesn't support create table command");
    }

    /**
     * Indicate that the table should be dropped.
     *
     * @return void
     * @throws LogicException
     */
    public function drop()
    {
        throw new LogicException("Sphinx doesn't support drop table command");
    }

    /**
     * Indicate that the table should be dropped if it exists.
     *
     * @return void
     * @throws LogicException
     */
    public function dropIfExists()
    {
        throw new LogicException("Sphinx doesn't support drop table command");
    }

    /**
     * Indicate that the given columns should be dropped.
     *
     * @param string $column
     * @return void
     */
    public function dropColumn($columns)
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        foreach ($columns as $column) {
            $this->addCommand('dropColumn', compact('column'));
        }
    }

    /**
     * Indicate that the given columns should be renamed.
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     * @throws LogicException
     */
    public function renameColumn($from, $to)
    {
        throw new LogicException("Sphinx doesn't support rename column command");
    }

    /**
     * Indicate that the given primary key should be dropped.
     *
     * @param  string|array|null  $index
     * @return void
     * @throws LogicException
     */
    public function dropPrimary($index = null)
    {
        throw new LogicException("Sphinx doesn't support indexes");
    }

    /**
     * Indicate that the given unique key should be dropped.
     *
     * @param  string|array  $index
     * @return void
     * @throws LogicException
     */
    public function dropUnique($index)
    {
        throw new LogicException("Sphinx doesn't support indexes");
    }

    /**
     * Indicate that the given index should be dropped.
     *
     * @param  string|array  $index
     * @return void
     * @throws LogicException
     */
    public function dropIndex($index)
    {
        throw new LogicException("Sphinx doesn't support indexes");
    }

    /**
     * Indicate that the given spatial index should be dropped.
     *
     * @param  string|array  $index
     * @return void
     * @throws LogicException
     */
    public function dropSpatialIndex($index)
    {
        throw new LogicException("Sphinx doesn't support indexes");
    }

    /**
     * Indicate that the given foreign key should be dropped.
     *
     * @param  string|array  $index
     * @return void
     * @throws LogicException
     */
    public function dropForeign($index)
    {
        throw new LogicException("Sphinx doesn't support indexes");
    }

    /**
     * Indicate that the given indexes should be renamed.
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     * @throws LogicException
     */
    public function renameIndex($from, $to)
    {
        throw new LogicException("Sphinx doesn't support indexes");
    }

    /**
     * Indicate that the timestamp columns should be dropped.
     *
     * @return void
     */
    public function dropTimestamps()
    {
        $this->dropColumn('created_at');
        $this->dropColumn('updated_at');
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
     * Indicate that the polymorphic columns should be dropped.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     * @return void
     * @throws LogicException
     */
    public function dropMorphs($name, $indexName = null)
    {
        throw new LogicException("Sphinx doesn't support morphs");
    }

    /**
     * Rename the table to a given name.
     *
     * @param  string  $to
     * @return void
     * @throws LogicException
     */
    public function rename($to)
    {
        throw new LogicException("Sphinx doesn't support rename tables");
    }

    /**
     * Specify the primary key(s) for the table.
     *
     * @param  string|array  $columns
     * @param  string|null  $name
     * @param  string|null  $algorithm
     * @return void
     * @throws LogicException
     */
    public function primary($columns, $name = null, $algorithm = null)
    {
        throw new LogicException("Sphinx doesn't support indexes");
    }

    /**
     * Specify a unique index for the table.
     *
     * @param  string|array  $columns
     * @param  string|null  $name
     * @param  string|null  $algorithm
     * @return void
     * @throws LogicException
     */
    public function unique($columns, $name = null, $algorithm = null)
    {
        throw new LogicException("Sphinx doesn't support indexes");
    }

    /**
     * Specify an index for the table.
     *
     * @param  string|array  $columns
     * @param  string|null  $name
     * @param  string|null  $algorithm
     * @return void
     * @throws LogicException
     */
    public function index($columns, $name = null, $algorithm = null)
    {
        throw new LogicException("Sphinx doesn't support indexes");
    }

    /**
     * Specify a spatial index for the table.
     *
     * @param  string|array  $columns
     * @param  string|null  $name
     * @return void
     * @throws LogicException
     */
    public function spatialIndex($columns, $name = null)
    {
        throw new LogicException("Sphinx doesn't support indexes");
    }

    /**
     * Specify a foreign key for the table.
     *
     * @param  string|array  $columns
     * @param  string|null  $name
     * @return void
     * @throws LogicException
     */
    public function foreign($columns, $name = null)
    {
        throw new LogicException("Sphinx doesn't support indexes");
    }

    /**
     * Create a new auto-incrementing integer (4-byte) column on the table.
     *
     * @param  string  $column
     * @return void
     * @throws LogicException
     */
    public function increments($column)
    {
        throw new LogicException("Sphinx doesn't support increments");
    }

    /**
     * Create a new auto-incrementing integer (4-byte) column on the table.
     *
     * @param  string  $column
     * @return void
     * @throws LogicException
     */
    public function integerIncrements($column)
    {
        throw new LogicException("Sphinx doesn't support increments");
    }

    /**
     * Create a new auto-incrementing tiny integer (1-byte) column on the table.
     *
     * @param  string  $column
     * @return void
     * @throws LogicException
     */
    public function tinyIncrements($column)
    {
        throw new LogicException("Sphinx doesn't support increments");
    }

    /**
     * Create a new auto-incrementing small integer (2-byte) column on the table.
     *
     * @param  string  $column
     * @return void
     * @throws LogicException
     */
    public function smallIncrements($column)
    {
        throw new LogicException("Sphinx doesn't support increments");
    }

    /**
     * Create a new auto-incrementing medium integer (3-byte) column on the table.
     *
     * @param  string  $column
     * @return void
     * @throws LogicException
     */
    public function mediumIncrements($column)
    {
        throw new LogicException("Sphinx doesn't support increments");
    }

    /**
     * Create a new auto-incrementing big integer (8-byte) column on the table.
     *
     * @param  string  $column
     * @return void
     * @throws LogicException
     *
     */
    public function bigIncrements($column)
    {
        throw new LogicException("Sphinx doesn't support increments");
    }

    /**
     * Create a new char column on the table.
     *
     * @param string $column
     * @return void
     * @throws LogicException
     */
    public function char($column, $length = null)
    {
        throw new LogicException("Sphinx doesn't support char column");
    }

    /**
     * Create a new string column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function string($column, $length = null)
    {
        return $this->addColumn('string', $column);
    }

    /**
     * Create a new text column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function text($column)
    {
        return $this->addColumn('text', $column);
    }

    /**
     * Create a new medium text column on the table.
     *
     * @param  string  $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function mediumText($column)
    {
        return $this->text($column);
    }

    /**
     * Create a new long text column on the table.
     *
     * @param  string  $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function longText($column)
    {
        return $this->text($column);
    }

    /**
     * Create a new integer (4-byte) column on the table.
     *
     * @param string $column
     * @param bool $autoIncrement
     * @param bool $unsigned
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function integer($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('integer', $column);
    }

    /**
     * Create a new tiny integer (1-byte) column on the table.
     *
     * @param  string  $column
     * @param  bool  $autoIncrement
     * @param  bool  $unsigned
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function tinyInteger($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->integer($column);
    }

    /**
     * Create a new small integer (2-byte) column on the table.
     *
     * @param  string  $column
     * @param  bool  $autoIncrement
     * @param  bool  $unsigned
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function smallInteger($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->integer($column);
    }

    /**
     * Create a new medium integer (3-byte) column on the table.
     *
     * @param  string  $column
     * @param  bool  $autoIncrement
     * @param  bool  $unsigned
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function mediumInteger($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->integer($column);
    }

    /**
     * Create a new big integer (8-byte) column on the table.
     *
     * @param  string  $column
     * @param  bool  $autoIncrement
     * @param  bool  $unsigned
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function bigInteger($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('bigInteger', $column);
    }

    /**
     * Create a new unsigned integer (4-byte) column on the table.
     *
     * @param  string  $column
     * @param  bool  $autoIncrement
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function unsignedInteger($column, $autoIncrement = false)
    {
        return $this->integer($column);
    }

    /**
     * Create a new unsigned tiny integer (1-byte) column on the table.
     *
     * @param  string  $column
     * @param  bool  $autoIncrement
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function unsignedTinyInteger($column, $autoIncrement = false)
    {
        return $this->integer($column);
    }

    /**
     * Create a new unsigned small integer (2-byte) column on the table.
     *
     * @param  string  $column
     * @param  bool  $autoIncrement
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function unsignedSmallInteger($column, $autoIncrement = false)
    {
        return $this->integer($column);
    }

    /**
     * Create a new unsigned medium integer (3-byte) column on the table.
     *
     * @param  string  $column
     * @param  bool  $autoIncrement
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function unsignedMediumInteger($column, $autoIncrement = false)
    {
        return $this->integer($column);
    }

    /**
     * Create a new unsigned big integer (8-byte) column on the table.
     *
     * @param  string  $column
     * @param  bool  $autoIncrement
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function unsignedBigInteger($column, $autoIncrement = false)
    {
        return $this->integer($column);
    }

    /**
     * Create a new float column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function float($column, $total = 8, $places = 2)
    {
        return $this->addColumn('float', $column);
    }

    /**
     * Create a new double column on the table.
     *
     * @param string $column
     * @param int|null $total
     * @param int|null $places
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function double($column, $total = null, $places = null)
    {
        return $this->float($column);
    }

    /**
     * Create a new decimal column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function decimal($column, $total = 8, $places = 2)
    {
        return $this->addColumn('decimal', $column);
    }

    /**
     * Create a new unsigned decimal column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function unsignedDecimal($column, $total = 8, $places = 2)
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
        return $this->addColumn('boolean', $column);
    }

    /**
     * Create a new enum column on the table.
     *
     * @param  string  $column
     * @param  array  $allowed
     * @return void
     * @throws LogicException
     */
    public function enum($column, array $allowed)
    {
        throw new LogicException("Sphinx doesn't support enum column");
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
        return $this->addColumn($unsigned ? 'multi' : 'multi64', $column);
    }

    /**
     * Create a new json column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function json($column)
    {
        return $this->addColumn('json', $column);
    }

    /**
     * Create a new jsonb column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function jsonb($column)
    {
        return $this->json($column);
    }

    /**
     * Create a new date column on the table.
     *
     * @param  string  $column
     * @return void
     * @throws LogicException
     */
    public function date($column)
    {
        throw new LogicException("Sphinx doesn't support date column");
    }

    /**
     * Create a new date-time column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function dateTime($column, $precision = 0)
    {
        return $this->timestamp($column);
    }

    /**
     * Create a new date-time column (with time zone) on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function dateTimeTz($column, $precision = 0)
    {
        return $this->timestamp($column);
    }

    /**
     * Create a new time column on the table.
     *
     * @param  string  $column
     * @param  int  $precision
     * @return void
     * @throws LogicException
     */
    public function time($column, $precision = 0)
    {
        throw new LogicException("Sphinx doesn't support time column");
    }

    /**
     * Create a new time column (with time zone) on the table.
     *
     * @param  string  $column
     * @param  int  $precision
     * @return void
     * @throws LogicException
     */
    public function timeTz($column, $precision = 0)
    {
        throw new LogicException("Sphinx doesn't support time column");
    }

    /**
     * Create a new timestamp column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function timestamp($column, $precision = 0)
    {
        return $this->addColumn('timestamp', $column);
    }

    /**
     * Create a new timestamp (with time zone) column on the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function timestampTz($column, $precision = 0)
    {
        return $this->timestamp($column);
    }

    /**
     * Add nullable creation and update timestamps to the table.
     *
     * @return void
     */
    public function timestamps($precision = 0)
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
    public function nullableTimestamps($precision = 0)
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
    public function softDeletes($column = '__soft_deleted', $precision = 0)
    {
        return $this->addColumn('boolean', $column);
    }

    /**
     * Add a "deleted at" timestampTz for the table.
     *
     * @param string $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function softDeletesTz($column = '__soft_deleted', $precision = 0)
    {
        return $this->softDeletes($column);
    }

    /**
     * Create a new year column on the table.
     *
     * @param  string  $column
     * @return void
     * @throws LogicException
     */
    public function year($column)
    {
        throw new LogicException("Sphinx doesn't support year column");
    }

    /**
     * Create a new binary column on the table.
     *
     * @param  string  $column
     * @return void
     * @throws LogicException
     */
    public function binary($column)
    {
        throw new LogicException("Sphinx doesn't support binary column");
    }

    /**
     * Create a new uuid column on the table.
     *
     * @param string $column
     * @return void
     * @throws LogicException
     */
    public function uuid($column)
    {
        throw new LogicException("Sphinx doesn't support uuid column");
    }

    /**
     * Create a new IP address column on the table.
     *
     * @param string $column
     * @return void
     * @throws LogicException
     */
    public function ipAddress($column)
    {
        throw new LogicException("Sphinx doesn't support ip address column");
    }

    /**
     * Create a new MAC address column on the table.
     *
     * @param string $column
     * @return void
     * @throws LogicException
     */
    public function macAddress($column)
    {
        throw new LogicException("Sphinx doesn't support mac address column");
    }

    /**
     * Create a new geometry column on the table.
     *
     * @param string $column
     * @return void
     * @throws LogicException
     */
    public function geometry($column)
    {
        throw new LogicException("Sphinx doesn't support geometry column");
    }

    /**
     * Create a new point column on the table.
     *
     * @param string $column
     * @param int|null $srid
     * @return void
     * @throws LogicException
     */
    public function point($column, $srid = null)
    {
        throw new LogicException("Sphinx doesn't support point column");
    }

    /**
     * Create a new linestring column on the table.
     *
     * @param string $column
     * @return void
     * @throws LogicException
     */
    public function lineString($column)
    {
        throw new LogicException("Sphinx doesn't support line column");
    }

    /**
     * Create a new polygon column on the table.
     *
     * @param string $column
     * @return void
     * @throws LogicException
     */
    public function polygon($column)
    {
        throw new LogicException("Sphinx doesn't support polygon column");
    }

    /**
     * Create a new geometrycollection column on the table.
     *
     * @param string $column
     * @return void
     * @throws LogicException
     */
    public function geometryCollection($column)
    {
        throw new LogicException("Sphinx doesn't support geometry collection column");
    }

    /**
     * Create a new multipoint column on the table.
     *
     * @param string $column
     * @return void
     * @throws LogicException
     */
    public function multiPoint($column)
    {
        throw new LogicException("Sphinx doesn't support multi point column");
    }

    /**
     * Create a new multilinestring column on the table.
     *
     * @param string $column
     * @return void
     * @throws LogicException
     */
    public function multiLineString($column)
    {
        throw new LogicException("Sphinx doesn't support multi line string column");
    }

    /**
     * Create a new multipolygon column on the table.
     *
     * @param string $column
     * @return void
     * @throws LogicException
     */
    public function multiPolygon($column)
    {
        throw new LogicException("Sphinx doesn't support multi polygon column");
    }

    /**
     * Create a new generated, computed column on the table.
     *
     * @param  string  $column
     * @param  string  $expression
     * @return void
     * @throws LogicException
     */
    public function computed($column, $expression)
    {
        throw new LogicException("Sphinx doesn't support computed column");
    }

    /**
     * Add the proper columns for a polymorphic table.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     * @return void
     * @throws LogicException
     */
    public function morphs($name, $indexName = null)
    {
        throw new LogicException("Sphinx doesn't support morphs column");
    }

    /**
     * Add nullable columns for a polymorphic table.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     * @return void
     * @throws LogicException
     */
    public function nullableMorphs($name, $indexName = null)
    {
        throw new LogicException("Sphinx doesn't support morphs column");
    }

    /**
     * Add the proper columns for a polymorphic table using UUIDs.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     * @return void
     * @throws LogicException
     */
    public function uuidMorphs($name, $indexName = null)
    {
        throw new LogicException("Sphinx doesn't support morphs column");
    }

    /**
     * Add nullable columns for a polymorphic table using UUIDs.
     *
     * @param  string  $name
     * @param  string|null  $indexName
     * @return void
     * @throws LogicException
     */
    public function nullableUuidMorphs($name, $indexName = null)
    {
        throw new LogicException("Sphinx doesn't support morphs column");
    }

    /**
     * Adds the `remember_token` column to the table.
     *
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function rememberToken()
    {
        return $this->string('remember_token', 100);
    }

    /**
     * Add a new index command to the blueprint.
     *
     * @param  string  $type
     * @param  string|array  $columns
     * @param  string  $index
     * @param  string|null  $algorithm
     * @return void
     * @throws LogicException
     */
    protected function indexCommand($type, $columns, $index, $algorithm = null)
    {
        throw new LogicException("Sphinx doesn't support indexes");
    }

    /**
     * Create a new drop index command on the blueprint.
     *
     * @param  string  $command
     * @param  string  $type
     * @param  string|array  $index
     * @return void
     * @throws LogicException
     */
    protected function dropIndexCommand($command, $type, $index)
    {
        throw new LogicException("Sphinx doesn't support indexes");
    }

    /**
     * Create a default index name for the table.
     *
     * @param  string  $type
     * @param  array  $columns
     * @return void
     * @throws LogicException
     */
    protected function createIndexName($type, array $columns)
    {
        throw new LogicException("Sphinx doesn't support indexes");
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

}
