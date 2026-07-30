<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateHistoriesTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('histories');
        $table->addColumn('customer_user_id', 'biginteger', ['null' => false])
            ->addColumn('store_id', 'integer', ['null' => false, 'signed'=>false])
            ->addColumn('score', 'integer', ['null' => false])
            ->addColumn('time', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('customer_user_id', 'customers', 'user_id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
            ->addForeignKey('store_id', 'stores', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
            ->addIndex(['customer_user_id', 'time'])
            ->addIndex(['store_id', 'time'])
            ->create();
    }
}
