<?php

namespace DieZeeL\Database\SphinxConnection\Schema;

use Closure;
use Illuminate\Database\Schema\Builder;
use LogicException;

class SphinxBuilder extends Builder
{
    

    /**
     * Determine if the given table exists.
     *
     * @param  string  $table
     * @return bool
     */
    public function hasTable($table)
    {
        $table = $this->connection->getTablePrefix().$table;

        return count($this->connection->select(
            $this->grammar->compileTableExists(), [$this->connection->getDatabaseName(), $table]
        )) > 0;
    }



    /**
     * Get the data type for the given column name.
     *
     * @param  string  $table
     * @param  string  $column
     * @return string
     */
    public function getColumnType($table, $column)
    {
        $table = $this->connection->getTablePrefix().$table;
        $results = $this->connection->select(
            $this->grammar->compileColumnListing($table)
        );
        return $this->connection->getPostProcessor()->processColumnType($results, $column);
    }

    /**
     * Get the column listing for a given table.
     *
     * @param  string  $table
     * @return array
     */
    public function getColumnListing($table)
    {
        $table = $this->connection->getTablePrefix().$table;

        $results = $this->connection->select(
            $this->grammar->compileColumnListing($table)
        );

        return $this->connection->getPostProcessor()->processColumnListing($results);
    }

    /**
     * Modify a table on the schema.
     *
     * @param  string    $table
     * @param  \Closure  $callback
     * @return void
     */
    public function table($table, Closure $callback)
    {
        $this->build($this->createBlueprint($table, $callback));
    }

    /**
     * Create a new table on the schema.
     *
     * @param  string    $table
     * @param  \Closure  $callback
     * @return void
     * @throws \LogicException
     */
    public function create($table, Closure $callback)
    {
        throw new LogicException('This database driver does not support create tables.');
    }

    /**
     * Drop a table from the schema.
     *
     * @param  string  $table
     * @return void
     * @throws \LogicException
     */
    public function drop($table)
    {
        throw new LogicException('This database driver does not support dropping tables.');
    }

    /**
     * Drop a table from the schema if it exists.
     *
     * @param  string  $table
     * @return void
     * @throws \LogicException
     */
    public function dropIfExists($table)
    {
        throw new LogicException('This database driver does not support dropping tables.');
    }

    /**
     * Drop all tables from the database.
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function dropAllTables()
    {
        throw new LogicException('This database driver does not support dropping all tables.');
    }

    /**
     * Drop all views from the database.
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function dropAllViews()
    {
        throw new LogicException('This database driver does not support dropping all views.');
    }

    /**
     * Drop all types from the database.
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function dropAllTypes()
    {
        throw new LogicException('This database driver does not support dropping all types.');
    }

    /**
     * Rename a table on the schema.
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     * @throws \LogicException
     */
    public function rename($from, $to)
    {
        throw new LogicException('This database driver does not support rename tables.');
    }

    /**
     * Enable foreign key constraints.
     *
     * @return bool
     * @throws \LogicException
     */
    public function enableForeignKeyConstraints()
    {
        throw new LogicException('This database driver does not support enable foreign key constants.');
    }

    /**
     * Disable foreign key constraints.
     *
     * @return bool
     * @throws \LogicException
     */
    public function disableForeignKeyConstraints()
    {
        throw new LogicException('This database driver does not support disable foreign key constants.');
    }
    

    /**
     * Create a new command set with a Closure.
     *
     * @param  string  $table
     * @param  \Closure|null  $callback
     * @return \Illuminate\Database\Schema\Blueprint
     */
    protected function createBlueprint($table, Closure $callback = null)
    {
        $prefix = $this->connection->getConfig('prefix_indexes')
            ? $this->connection->getConfig('prefix')
            : '';

        if (isset($this->resolver)) {
            return call_user_func($this->resolver, $table, $callback, $prefix);
        }

        return new Blueprint($table, $callback, $prefix);
    }


    /**
     * Register a custom Doctrine mapping type.
     *
     * @param  string  $class
     * @param  string  $name
     * @param  string  $type
     * @return void
     *
     * @throws \LogicException
     */
    public function registerCustomDoctrineType($class, $name, $type)
    {
        throw new LogicException('This database driver does not support doctrine.');
    }

    /**
     * Get all of the table names for the database.
     *
     * @return array
     */
    protected function getAllTables()
    {
        return $this->connection->select(
            $this->grammar->compileGetAllTables()
        );
    }

    /**
     * Get all of the view names for the database.
     *
     * @return array
     * @throws \LogicException
     */
    protected function getAllViews()
    {
        throw new LogicException('This database driver does not support views.');
    }




}
