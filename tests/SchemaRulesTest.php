<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

it('generates rules based on the schema', function () {
    Schema::create('test', function (Blueprint $table) {
        $table->id();
        $table->boolean('is_locked');
        $table->string('title', 100);
        $table->string('first_name');
        $table->char('last_name');
        $table->text('description');
        $table->tinyInteger('priceXS');
        $table->tinyInteger('priceXS_nullable')->nullable();
        $table->unsignedTinyInteger('priceXS_unsigned');
        $table->smallInteger('priceS');
        $table->unsignedSmallInteger('priceS_unsigned');
        $table->mediumInteger('priceM');
        $table->unsignedMediumInteger('priceM_unsigned');
        $table->integer('priceL');
        $table->unsignedInteger('priceL_unsigned');
        $table->bigInteger('priceXL');
        $table->unsignedBigInteger('priceXL_unsigned');
        $table->float('cents');
        $table->unsignedFloat('cents_unsigned');
        $table->double('micro');
        $table->unsignedDouble('micro_unsigned');
        $table->decimal('nano');
        $table->unsignedDecimal('nano_unsigned');
        $table->enum('types_enum', ['a', 'b', 'c']);
        //            $table->set('types_set', ['a', 'b', 'c']);
        $table->date('startDate')->useCurrent();
        $table->year('startYear')->useCurrent();
        $table->json('json_data');
        $table->time('time_x');
        $table->timestamps();
    });

    Artisan::call('schema:generate-rules test');
    dd(Artisan::output());
});
