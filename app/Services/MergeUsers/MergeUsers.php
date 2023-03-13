<?php

namespace App\Services\Merge;

use App\Services\Merge\DataTransferObjects\MergeUsersPayload;
use App\Services\Merge\Pipes as Pipes;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class MergeUsers
{
    private array $pipes = [
        Pipes\CheckUserStatuses::class,
        Pipes\CheckUserToBeRemoved::class,
        Pipes\UpdateUserRecord::class,
        Pipes\TransferNotes::class,
        Pipes\DeleteDuplicateUserModel::class,
    ];

    public function handle(MergeUsersPayload $payload): MergeUsersPayload
    {
        return DB::transaction(function () use ($payload) {
            return app(Pipeline::class)
                ->send($payload)
                ->through($this->pipes)
                ->then(function ($result) {
                    return $result;
                });
        });
    }
}
