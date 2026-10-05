<?php

use yii\db\Migration;

class m260930_212534_create_table_cat_registries extends Migration
{
    private string $tableName;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->tableName = \CatRegistry\applications\entities\CatRegistry::tableName();
    }

    public function up()
    {
        $this->createTable(
            $this->tableName,
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(40)->notNull(),
                'gender' => $this->string(10)->notNull(),
                'age' => $this->integer()->notNull()->defaultValue(0),
                'mother_id' => $this->integer()->null(),
                'created_at' => $this->dateTime()->notNull(),
                'updated_at' => $this->dateTime()->null(),
                'deleted_at' => $this->dateTime()->null(),
            ]
        );

        $this->createIndex('idx-cat-gender', $this->tableName, 'gender');
        $this->createIndex('idx-cat-age', $this->tableName, 'age');
    }

    public function down()
    {
        $this->dropIndex('idx-cat-gender', $this->tableName);
        $this->dropIndex('idx-cat-age', $this->tableName);

        $this->dropTable($this->tableName);
    }
}
