<?php
/**
 * SphinxConnection.php file
 *
 * @author     Dmitriy Tyurin <fobia3d@gmail.com>
 * @copyright  Copyright (c) 2016 Dmitriy Tyurin
 */

namespace DieZeeL\Database\SphinxConnection;

use Closure;
use Foolz\SphinxQL\Facet;
use Foolz\SphinxQL\Helper;
use Foolz\SphinxQL\SphinxQL;
use Illuminate\Database\MySqlConnection;

/**
 * Class SphinxConnection
 *
 * @author     Dmitriy Tyurin <fobia3d@gmail.com>
 * @copyright  Copyright (c) 2016 Dmitriy Tyurin
 */
class SphinxConnection extends MySqlConnection
{
    /**
     * @var \DieZeeL\Database\SphinxConnection\SphinxQLDriversConnection
     */
    protected $sphinxQLConnection;

    /**
     * @return \DieZeeL\Database\SphinxConnection\SphinxQLDriversConnection
     */
    public function getSphinxQLDriversConnection()
    {
        if (null === $this->sphinxQLConnection) {
            $this->sphinxQLConnection = new SphinxQLDriversConnection($this->getPdo());
        }
        return $this->sphinxQLConnection;
    }

    /**
     * @return \Foolz\SphinxQL\Helper
     */
    public function getSphinxQLHelper()
    {
        return new Helper($this->getSphinxQLDriversConnection());
    }

    /**
     * @return \Foolz\SphinxQL\SphinxQL
     */
    public function createSphinxQL()
    {
        return new SphinxQL($this->getSphinxQLDriversConnection());
    }

    /**
     * @return \Foolz\SphinxQL\Facet
     */
    public function createFacet()
    {
        return new Facet($this->getSphinxQLDriversConnection());
    }

    /**
     * Run an insert or replace statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return bool
     */
    public function replace($query, $bindings = [])
    {
        return $this->statement($query, $bindings);
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \DieZeeL\Database\SphinxConnection\Eloquent\Query\Grammar
     */
    protected function getDefaultQueryGrammar()
    {
        return new \DieZeeL\Database\SphinxConnection\Eloquent\Query\Grammar();
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \DieZeeL\Database\SphinxConnection\Schema\SphinxBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new \DieZeeL\Database\SphinxConnection\Schema\SphinxBuilder($this);
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \DieZeeL\Database\SphinxConnection\Schema\SphinxGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new \DieZeeL\Database\SphinxConnection\Schema\SphinxGrammar);
    }

    /**
     * Get the default post processor instance.
     *
     * @return \Illuminate\Database\Query\Processors\MySqlProcessor
     */
    protected function getDefaultPostProcessor()
    {
        return new \DieZeeL\Database\SphinxConnection\Query\SphinxProcessor;
    }

    /**
     * Get a new query builder instance.
     *
     * @return \DieZeeL\Database\SphinxConnection\Eloquent\Query\Builder
     */
    public function query()
    {
        return new \DieZeeL\Database\SphinxConnection\Eloquent\Query\Builder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }

    /**
     * Set the table prefix and return the grammar.
     *
     * @param  \DieZeeL\Database\SphinxConnection\Schema\SphinxGrammar  $grammar
     * @return \DieZeeL\Database\SphinxConnection\Schema\SphinxGrammar
     */
    public function withTablePrefix($grammar)
    {
        $grammar->setTablePrefix($this->tablePrefix);

        return $grammar;
    }
}
