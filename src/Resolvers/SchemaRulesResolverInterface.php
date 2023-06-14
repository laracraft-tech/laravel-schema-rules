<?php

namespace LaracraftTech\LaravelSchemaRules\Resolvers;

interface SchemaRulesResolverInterface
{
    /**
     * @param string $table
     * @param array $columns
     * @return array
     */
    public function __construct(string $table, array $columns = []);

    /**
     * Generate the rules of the provided tables schema.
     *
     * @return array
     */
    public function generate(): array;
}
