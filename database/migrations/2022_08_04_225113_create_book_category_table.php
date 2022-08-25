<!-- migration untuk relationship ke tabel categories  -->
<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateBookCategoryTable extends Migration
{
 /**
 * Run the migrations.
 *
 * @return void
 */
    public function up()
        {
        Schema::create('book_category', function (Blueprint $table) {
            $table->id();
            // 2 foreign key pastikan menggunakan method insigned()
            $table->unsignedBigInteger('book_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();

            $table->timestamps();
            $table->foreign('book_id')->references('id')->on('books');
            $table->foreign('category_id')->references('id')->on('categories');
            });
        }
/**
* Reverse the migrations.
*
* @return void
*/
public function down()
    {
    Schema::dropIfExists('book_category');
    }
}