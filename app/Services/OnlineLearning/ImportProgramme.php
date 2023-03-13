<?php

namespace App\Domain\Programme\Actions\Scorm;

use App\Domain\Programme\Enums\ScormFileUploadingStatusEnum;
use App\Domain\Programme\Models\Programme;
use App\Jobs\Scorm as Jobs;
use Illuminate\Support\Facades\URL;
use RusticiSoftware\Cloud\V2\Api\CourseApi;
use RusticiSoftware\Cloud\V2\Model\ImportFetchRequestSchema;

class ImportProgramme
{
    private const MAY_CREATE_NEW_VERSION_OF_COURSE = 'true';

    public function __construct(private CourseApi $courseApi)
    {
    }

    public function handle(Programme $programme): void
    {
        $programme->scorm_file_uploading_status = ScormFileUploadingStatusEnum::running();
        $programme->save();

        dispatch(new Jobs\ImportProgramme($programme));
    }

    public function handleInJob(Programme $programme): void
    {
        $programme->scorm_file_uploading_status = ScormFileUploadingStatusEnum::running();
        $programme->save();

        $importScheme = new ImportFetchRequestSchema();
        $importScheme->setUrl(
            URL::signedRoute(
                'api.programmes.download_archive',
                ['archive' => $programme->scormFile->uuid],
            ),
        );

        try {
            $response = $this->courseApi->createFetchAndImportCourseJob(
                $programme->uuid,
                $importScheme,
                self::MAY_CREATE_NEW_VERSION_OF_COURSE,
                URL::signedRoute('webhooks.scorm.programme_uploading'),
            );

            logs()->info('Scorm import job ID', [
                'programme_uuid' => $programme->uuid,
                'scorm_job_id' => $response->getResult(),
            ]);
        } catch (\Exception $e) {
            logs()->error('Scorm import failed', [
                'programme_uuid' => $programme->uuid,
                'exception' => $e,
            ]);

            $programme->scorm_file_uploading_status = ScormFileUploadingStatusEnum::error();
            $programme->save();
        }
    }
}
