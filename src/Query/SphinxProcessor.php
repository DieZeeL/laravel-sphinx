<?php

namespace DieZeeL\Database\SphinxConnection\Query;

use Illuminate\Database\Query\Processors\Processor;

class SphinxProcessor extends Processor
{
    /**
     * Process the results of a column listing query.
     *
     * @param array $results
     * @return array
     */
    public function processColumnListing($results)
    {
        return array_map(function ($result) {
            return ((object)$result)->Field;
        }, $results);
    }

    /**
     * Process the results of a column listing query.
     *
     * @param array $results
     * @return array
     */
    public function processColumnType($results, $column)
    {
        $types = [
            'bigint' => 'bigInteger',
            'uint' => 'integer',
            'bool' => 'boolean',
            'mva' => 'set',
            'mva64' => 'set',
        ];
        foreach ($results as $result) {
            if ($result->Field != $column)
                continue;
            return isset($types[$result->Type]) ? $types[$result->Type] : $result->Type;
        }
        return false;
    }
}
