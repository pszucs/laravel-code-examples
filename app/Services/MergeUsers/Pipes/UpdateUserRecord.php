<?php

namespace App\Sercies\MergeUsers\Pipes;

use App\Sercies\MergeUsers\DataTransferObjects\MergeUsersPayload;
use App\Models\Address;
use Closure;

class UpdateUserRecord implements MergeUsersPipe
{
    public function handle(MergeUsersPayload $payload, Closure $next)
    {
        $keepUser = $payload->keepUser();
        $removeUser = $payload->removeUser();
        $dto = $payload->dto();
        
        $address = ($keepUser->address ?? new Address())->fill($dto->address()->toArray());
        $address->save();
        $keepUser->address()->associate($address);
        
        $keepUser->fill([
            'title' => $dto->title(),
            'forename' => $dto->forename(),
            'surname' => $dto->surname(),
            'email' => $dto->email(),
            'dob' => $dto->dob(),
            'address' => $dto->address(),
            'contact_number_1' => $dto->contactNumber1(),
            'contact_number_2' => $dto->contactNumber2(),
            'gender_id' => $dto->gender(),
            'disability_id' => $dto->disability(),
            'ethnicity_id' => $dto->ethnicity(),
            'sta_online_account' => $removeUser->sta_online_account,
            'sta_online_password' => $removeUser->sta_online_password,
            'email_verified_at' => $removeUser->email_verified_at ? now() : null,
        ])->save();
        
        return $next($payload);
    }
}
