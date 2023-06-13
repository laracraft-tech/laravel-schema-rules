<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

it('generates rules based on the schema', function () {
    Schema::create('test', function (Blueprint $table) {
        $table->id();
        $table->boolean('bool');
        $table->string('string');
        $table->char('char');
        $table->text('text');
        $table->unsignedTinyInteger('tinyint');
        $table->unsignedSmallInteger('smallint');
        $table->unsignedMediumInteger('mediumint');
        $table->unsignedInteger('integer');
        $table->unsignedBigInteger('bigint');
        $table->float('float');
        $table->double('double');
        $table->decimal('decimal');
        $table->enum('enum', ['a', 'b', 'c']);
//            $table->set('set', ['a', 'b', 'c']);
        $table->date('date')->useCurrent();
        $table->year('year')->useCurrent();
        $table->json('json');
        $table->timestamps();
    });

    Artisan::call('schema:generate-rules test');
});
