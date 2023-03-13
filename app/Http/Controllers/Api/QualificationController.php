<?php

namespace App\Http\Controllers\Api;

use App\Domain\TrainingCentre\Models\TrainingCentre;
use App\Domain\Qualification\Models\Qualification;
use App\Domain\Qualification\Models\Regulator;
use App\Domain\User\Models\UserQualification;
use App\Http\Resources\Api\QualificationResource;
use App\Http\Resources\Api\UserQualificationResource;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class QualificationController extends BaseController
{
    public function index(TrainingCentre $trainingCentre)
    {
        $query = Qualification::query()
            ->enabled()
            ->active()
            ->activeInTrainingCentre($trainingCentre);

        $qualifications = QueryBuilder::for($query)
            ->allowedFilters([
                AllowedFilter::exact('category_id', 'category.id'),
                AllowedFilter::exact('supported_qualification_id', 'supportedQualifications.uuid'),
            ])->get();

        return QualificationResource::collection($qualifications);
    }

    public function show(string $qualificationUuid, TrainingCentre $trainingCentre)
    {
        return QualificationResource::make(
            $this->qualification($trainingCentre, $qualificationUuid)
        );
    }

    protected function trainingCentre(Request $request, string $trainingCentreUuid): TrainingCentre
    {
        return TrainingCentre::forUuid($trainingCentreUuid)
            ->forManagingUser($request->user())
            ->where('type_regulated', true)
            ->firstOrFail();
    }

    protected function qualification(TrainingCentre $trainingCentre, string $qualificationUuid): Qualification
    {
        return Qualification::forUuid($qualificationUuid)
            ->enabled()
            ->linkedToTrainingCentre($trainingCentre)
            ->firstOrfail();
    }
}
