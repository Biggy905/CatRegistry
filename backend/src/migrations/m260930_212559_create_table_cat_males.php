<?php

use yii\db\Migration;

class m260930_212559_create_table_cat_males extends Migration
{
    private string $tableName;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->tableName = \CatRegistry\applications\entities\CatMale::tableName();
    }

    public function up(): void
    {
        $this->createTable(
            $this->tableName,
            [
                'id' => $this->primaryKey(),
                'cat_id' => $this->integer()->notNull(),
                'father_id' => $this->integer()->notNull(),
            ]
        );
    }

    public function down(): void
    {
        $this->dropTable($this->tableName);
    }
}
