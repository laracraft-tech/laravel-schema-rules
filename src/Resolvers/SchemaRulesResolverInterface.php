<?php

namespace LaracraftTech\LaravelSchemaRules\Resolvers;

interface SchemaRulesResolverInterface
{
    /**
     * @param string $table
     * @param array $fields
     * @return array
     */
    public function __construct(string $table, array $fields = []);

    /**
     * Generate the rules of the provided tables schema.
     *
     * @return array
     */
    public function generate(): array;
}
