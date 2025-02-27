<?php
/**
 * Model.php file
 *
 * @author     Dmitriy Tyurin <fobia3d@gmail.com>
 * @copyright  Copyright (c) 2016 Dmitriy Tyurin
 */

namespace DieZeeL\Database\SphinxConnection\Eloquent;

use DieZeeL\Database\SphinxConnection\Eloquent\Query\Builder as QueryBuilder;
use DieZeeL\Database\SphinxConnection\Eloquent\Query\Grammar as QueryGrammar;

/**
 * App\Lib\Database\Eloquent\Model
 *
 * @method static \DieZeeL\Database\SphinxConnection\Eloquent\Query\Builder match($column, $value = null, $half = false)   Созвучный поиск.
 * @method static \DieZeeL\Database\SphinxConnection\Eloquent\Query\Builder withinGroupOrderBy($column, $asc = 'ASC')  Конструкция [WITHIN GROUP ORDER BY].
 * @method static \DieZeeL\Database\SphinxConnection\Eloquent\Query\Builder whereMulti($column, $operator, $values) равенство в список.
 * @method static \DieZeeL\Database\SphinxConnection\Eloquent\Query\Builder option($name, $value) Опции запроса [OPTION].
 * @method static \DieZeeL\Database\SphinxConnection\Eloquent\Query\Builder facet($callback) Конструкция запроса [FACET].
 *
 * @author     Dmitriy Tyurin <fobia3d@gmail.com>
 * @copyright  Copyright (c) 2016 Dmitriy Tyurin
 */
class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * База данных
     *
     * @var string
     */
    protected $connection = 'sphinx';

    protected $perPage = 15;
    public $timestamps = false;
    public $incrementing = false;

    /*
     * ===================
     * Main scopes
     * ===================
     */

    /**
     * Масив преобразуется в список целых числе, null и пустые строки игнорятся
     *
     * Example:
     *     filterParamsUint([1,2,null,4])  => [1,2,3]
     *     filterParamsUint([1,[2,[null,4]]])  => [1,2,3]
     *
     * @param $args
     * @return array|bool
     *
     * @deprecated
     */
    protected function filterParamsUint($args)
    {

        $args = array_flatten((array) $args);
        $args = array_filter((array) $args, function ($v) {
            return (($v !== null) && ($v !== ''));
        });
        if (!count($args)) {
            return false;
        }
        $ids = array_map('intval', $args);
        return array_unique(array_values($ids));
    }

    /**
     * Конвертирует тип поля MVA в список
     *
     * @param string $name название поля
     * @return array
     */
    protected function getMvaAttribute($name)
    {
        if (isset($this->attributes[$name]) && $this->attributes[$name] != '') {
            $value = $this->asMva($this->attributes[$name]);
            return $value;
        }
        return [];
    }

    protected function asMva($value)
    {
        if (is_string($value) || is_numeric($value)) {
            $value = preg_replace('/[\(\)\s]/', '', $value);
            if (strlen($value)) {
                $value = explode(',', $value);
                $value = array_map('intval', $value);
            } else {
                $value = [];
            }
        }
        return $value;
    }

    /*
     * ===================
     * Override methods
     * ===================
     */

    protected function castAttribute($key, $value)
    {
        if (is_null($value)) {
            return $value;
        }

        switch ($this->getCastType($key)) {
            case 'mva':
                return $this->asMva($value);
            default:
                return parent::castAttribute($key, $value);
        }
    }


    /**
     * Get a new query builder that doesn't have any global scopes.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newQueryWithoutScopes()
    {
        $builder = $this->newEloquentBuilder($this->newBaseQueryBuilder());
        return $builder->setModel($this)->with($this->with);
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();
        $grammar = new QueryGrammar;

        return new QueryBuilder($conn, $grammar, $conn->getPostProcessor());
    }

    protected function getKeyForSaveQuery()
    {
        return (int) parent::getKeyForSaveQuery();
    }

    public function getConnection()
    {
        return static::resolveConnection($this->getConnectionName());
    }
}
