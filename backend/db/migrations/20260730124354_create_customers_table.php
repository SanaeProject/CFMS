<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCustomersTable extends AbstractMigration
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
        $table = $this->table('customers');
        $table->addColumn('user_id', 'biginteger', ['null' => false])
            ->addColumn('paid', 'boolean', ['null' => false, 'default' => false])
            ->addColumn('disabled', 'boolean', ['null' => false, 'default' => false])
            ->addIndex(['user_id'], ['unique' => true])
            ->create();
    }
}
