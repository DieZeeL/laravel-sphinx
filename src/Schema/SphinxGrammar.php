<?php

namespace DieZeeL\Database\SphinxConnection\Schema;

use DieZeeL\Database\SphinxConnection\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;
use RuntimeException;
use Illuminate\Support\Fluent;
use Illuminate\Database\Connection;

class SphinxGrammar extends Grammar
{
    /**
     * Compile the query to determine the list of columns.
     *
     * @return string
     */
    public function compileColumnListing($table)
    {
        return 'desc '.$table;
    }


    /**
     * Compile an add column command.
     *
     * @param \Illuminate\Database\Schema\Blueprint $blueprint
     * @param \Illuminate\Support\Fluent $command
     * @return string
     */
    public function compileAdd(Blueprint $blueprint, Fluent $command)
    {
        return 'alter table ' . $this->wrapTable($blueprint) . ' add column ' . $this->getCommandColumn($command);
    }

    /**
     * Compile a drop column command.
     *
     * @param \Illuminate\Database\Schema\Blueprint $blueprint
     * @param \Illuminate\Support\Fluent $command
     * @return string
     */
    public function compileDropColumn(Blueprint $blueprint, Fluent $command)
    {
        return 'alter table ' . $this->wrapTable($blueprint) . ' drop column ' . $this->getCommandColumn($command);
    }


    /**
     * Compile the SQL needed to retrieve all table names.
     *
     * @return string
     */
    public function compileGetAllTables()
    {
        return 'SHOW TABLES';
    }


    /**
     * Compile the blueprint's column definitions.
     *
     * @param Blueprint $blueprint
     * @return array
     */
    protected function getCommandColumn($command)
    {
        if($command->column instanceof Fluent) {
            return $this->wrap($command->column) . ' ' . $this->getType($command->column);
        }
        return $this->wrap($command->column);
    }

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param string $value
     * @return string
     */
    protected function wrapValue($value)
    {
        if ($value !== '*') {
            return $value;
        }

        return $value;
    }
    
    /**
     * Create the column definition for a string type.
     *
     * @param \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typeString(Fluent $column)
    {
        return "STRING";
    }

    /**
     * Create the column definition for a text type.
     *
     * @param \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typeText(Fluent $column)
    {
        return 'STRING';
    }

    /**
     * Create the column definition for an integer type.
     *
     * @param \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typeInteger(Fluent $column)
    {
        return 'INTEGER';
    }

    /**
     * Create the column definition for an integer type.
     *
     * @param \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typeBigInteger(Fluent $column)
    {
        return 'BIGINT';
    }

    /**
     * Create the column definition for a float type.
     *
     * @param \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typeFloat(Fluent $column)
    {
        return $this->typeDouble($column);
    }

    /**
     * Create the column definition for a double type.
     *
     * @param \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typeDouble(Fluent $column)
    {
        return 'FLOAT';
    }

    /**
     * Create the column definition for a decimal type.
     *
     * @param \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typeDecimal(Fluent $column)
    {
        return "STRING";
    }

    /**
     * Create the column definition for a boolean type.
     *
     * @param \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typeBoolean(Fluent $column)
    {
        return 'BOOL';
    }

    /**
     * Create the column definition for a set enumeration type.
     *
     * @param \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typeMulti(Fluent $column)
    {
        return "MULTI";
    }

    /**
     * Create the column definition for a set enumeration type.
     *
     * @param \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typeMulti64(Fluent $column)
    {
        return "MULTI64";
    }

    /**
     * Create the column definition for a json type.
     *
     * @param \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typeJson(Fluent $column)
    {
        return 'JSON';
    }


    /**
     * Create the column definition for a timestamp type.
     *
     * @param \Illuminate\Support\Fluent $column
     * @return string
     */
    protected function typeTimestamp(Fluent $column)
    {
        return "TIMESTAMP";
    }

}
