<?php

namespace LaracraftTech\LaravelSchemaRules\Contracts;

interface SchemaRulesResolverInterface
{
    /**
     * @return array
     */
    public function __construct(string $table, array $columns = []);

    /**
     * Generate the rules of the provided tables schema.
     */
    public function generate(): array;
}
