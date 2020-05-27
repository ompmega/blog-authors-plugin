<?php namespace Ompmega\BlogAuthors\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateAuthorsTable extends Migration
{
    public function up()
    {
        Schema::create('ompmega_blogauthors_authors', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->string('slug')->index();
            $table->text('photo_url')->nullable();
            $table->text('bio')->nullable();
            $table->text('socials')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ompmega_blogauthors_authors');
    }
}
