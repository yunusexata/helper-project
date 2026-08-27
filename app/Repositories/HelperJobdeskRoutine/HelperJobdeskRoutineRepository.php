<?php

namespace App\Repositories\HelperJobdeskRoutine;

use App\Models\HelperJobdeskRoutine;
use App\Repositories\MasterDataRepository;

class HelperJobdeskRoutineRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return HelperJobdeskRoutine::class;
    }

    public static function datatable()
    {
        return HelperJobdeskRoutine::query();
    }
}
