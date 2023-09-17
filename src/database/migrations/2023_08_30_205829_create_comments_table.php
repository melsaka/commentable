    <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    protected $tableName;
    
    public function __construct()
    {
        $this->tableName = config('commentable.table', 'comments');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->defaultTableSchema();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropDefaultTable();
    }

    public function createTableSchema($tableName)
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable');
            $table->morphs('owner');
            
            $table->text('body');
            $table->boolean('accepted')->default(true);
            $table->double('rate', 15, 8)->nullable();
            $table->bigInteger('parent_id')->nullable();

            $table->timestamps();
        });
    }

    public function defaultTableSchema()
    {
        $this->createTableSchema($this->tableName);
    }

    public function dropDefaultTable()
    {
        Schema::dropIfExists($this->tableName);
    }
}
