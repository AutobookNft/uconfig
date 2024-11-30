use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUConfigVersionsTable extends Migration
{
    public function up()
    {
        Schema::create('uconfig_versions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('uconfig_id');
            $table->integer('version');
            $table->longText('value');
            $table->timestamps();

            // Chiave esterna
            $table->foreign('uconfig_id')->references('id')->on('uconfig')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('uconfig_versions');
    }
} 