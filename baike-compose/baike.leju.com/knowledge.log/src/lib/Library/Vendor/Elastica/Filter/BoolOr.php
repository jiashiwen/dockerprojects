<?php
namespace Elastica\Filter;

/**
 * Or Filter.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-or-filter.html
 */
class BoolOr extends AbstractMulti
{
    /**
     * @return string
     */
    protected function _getBaseName()
    {
        return 'or';
    }
}
