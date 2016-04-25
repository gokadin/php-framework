<?php

namespace Library\Testing;

use Library\Facades\DB;

trait DatabaseTransactions
{
    public function beginDatabaseTransaction()
    {
        DB::beginTransaction();

        $this->addTearDownCallBack(function() {
            DB::rollBack();
        });
    }
}