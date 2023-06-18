<?php

use LaracraftTech\LaravelSchemaRules\Tests\TestCase;

uses(TestCase::class)->beforeEach(function () {
    ray()->clearAll();
})->in(__DIR__);
