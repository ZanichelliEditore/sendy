<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Http\Resources\FailedJobResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Repositories\FailedJobRepository;
use App\Utils\FailedJobsUtils;

class FailedJobController extends Controller
{
    use FailedJobsUtils;

    protected $failedJobRepository;

    public function __construct(FailedJobRepository $failedJobRepository)
    {
        $this->failedJobRepository = $failedJobRepository;
    }

    public function getList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'sometimes|string',
            'limit' => 'sometimes|integer',
            'order' => 'sometimes|in:asc,desc,ASC,DESC',
            'orderBy' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->error422(null, $validator->errors());
        }
        $query = $request->input('q');
        $limit = (int) $request->input('limit', self::PAGINATION);
        $order = $request->input('order', 'ASC');
        $orderBy = $request->input('orderBy', 'id');
        $retriviedFailedJobs = $this->failedJobRepository->allPaginated($query, $orderBy, $order, $limit);
        return FailedJobResource::collection($retriviedFailedJobs);
    }

    public function retryJob($id)
    {
        $this->cleanFailedJobsCache();

        $failedJob = $this->failedJobRepository->find($id);
        if (!$failedJob) {
            return response()->error404(__('messages.FailedJob') . $id);
        }
        try {
            $result = Artisan::call('queue:retry', ['id' => $id]);
            if ($result != 0) {
                return response()->error500(__('messages.RetryError') . $failedJob);
            }
        } catch (Exception $e) {
            return response()->error500(__('messages.RetryError') . $failedJob);
        }

        return response()->success200(__('messages.RetrySuccess'), [
            'action' => 'RETRY',
            'object_type' => 'failedJob',
            'object_id' => $id
        ]);
    }

    public function destroy(string $id)
    {
        $this->cleanFailedJobsCache();

        $failedJob = $this->failedJobRepository->find($id);
        if (!$failedJob) {
            return response()->error404(__('messages.FailedJob') . $id);
        }

        try {
            $result = Artisan::call('queue:forget', ['id' => $id]);
            if ($result != 0) {
                return response()->error500(__('messages.DeleteError') . $failedJob);
            }
        } catch (Exception $e) {
            return response()->error500(__('messages.DeleteError') . $failedJob);
        }

        return response()->success200(__('messages.DeleteSuccess'), [
            'action' => 'DELETE',
            'object_type' => 'failedJob',
            'object_id' => $failedJob->id
        ]);
    }

    public function destroyAll()
    {
        $this->cleanFailedJobsCache();

        $result = Artisan::call('queue:flush');
        if ($result != 0) {
            return response()->error500(__('messages.DeleteError'));
        }
        return response()->success200(__('messages.DeleteSuccess'));
    }

    public function retryAll()
    {
        $result = Artisan::call('queue:lazy-retry');
        if ($result != 0) {
            return response()->error500(__('messages.RetryError') . $result);
        }
        return response()->success200(__('messages.RetrySuccess'));
    }
}
