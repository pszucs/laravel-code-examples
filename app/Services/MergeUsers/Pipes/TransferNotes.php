<?php

namespace App\Services\MergeUsers\Pipes;

use App\Services\MergeUsers\DataTransferObjects\MergeUsersPayload;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;

class TransferNotes implements MergeUsersPipe
{
    public function handle(MergeUsersPayload $payload, Closure $next)
    {
        DB::table('notables')
            ->where([
                'notable_id' => $payload->removeUser()->id,
                'notable_type' => User::class,
            ])
            ->update([
                'notable_id' => $payload->keepUser()->id,
                'updated_at' => now(),
            ]);
        
        return $next($payload);
    }
}
