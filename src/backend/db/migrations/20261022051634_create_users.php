<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsers extends AbstractMigration
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
        $table = $this->table('users');

        $table
            ->addColumn('token', 'string', [
                'limit' => 255,
            ])
            ->addColumn('passcode', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
            ])
            ->addColumn('finished_at', 'datetime', [
                'null' => true,
            ])
            ->addColumn('last_heartbeat_at', 'datetime', [
                'null' => true,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addIndex(['token'], [
                'unique' => true,
            ])
            ->create();
    }
}
