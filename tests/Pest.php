<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaracraftTech\LaravelSchemaRules\Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->beforeEach(function () {
    ray()->clearAll();
})->in(__DIR__);
