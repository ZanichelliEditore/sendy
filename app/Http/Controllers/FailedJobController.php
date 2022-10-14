<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Http\Resources\FailedJobResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Repositories\FailedJobRepository;

class FailedJobController extends Controller
{
    protected $failedJobRepository;

    public function __construct(FailedJobRepository $failedJobRepository)
    {
        $this->failedJobRepository = $failedJobRepository;
    }

    /**
     * @OA\Get(
     *      path="/api/failedJobs",
     *      summary="List of all the failed jobs",
     *      tags={"jobs"},
     *      description="Use to get the list of all failed jobs",
     *      @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="values to filter returned data (payload values)",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="maximum number of results to return",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32",
     *             minimum=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="type of order: ASC, DESC",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="orderBy",
     *         in="query",
     *         description="field to order: id - name(default) - created_at - updated_at",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          ref="#/components/responses/Success200"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          ref="#/components/responses/Error500",
     *      )
     *
     * )
     */
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

    /**
     * @OA\Get(
     *      path="/api/failedJobs/retry/{id}",
     *      summary="Retry a failed jobs",
     *      tags={"jobs"},
     *      description="Use to retry a failed job",
     *     operationId="failedJobController.retryJob",
     *     @OA\Parameter(
     *        in="path",
     *        required=true,
     *        description="id of the job that you want to retry",
     *        name="id",
     *        @OA\Schema(
     *            type="integer",
     *            minimum=1
     *        )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         ref="#/components/responses/Error500"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         ref="#/components/responses/Success200"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         ref="#/components/responses/Error404"
     *     )
     * )
     */
    public function retryJob($id)
    {
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

    /**
     * @OA\Delete(
     *      path="/api/failedJobs/{id}",
     *      summary="Delete the failed Job",
     *      tags={"jobs"},
     *      description="Insert the failed job id that you want to delete",
     *      operationId="FailedJobController.destroy",
     *      @OA\Parameter(
     *        in="path",
     *        required=true,
     *        description="id of the job that you want to delete",
     *        name="id",
     *        @OA\Schema(
     *            type="integer",
     *            minimum=1
     *        )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          ref="#/components/responses/Error500"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          ref="#/components/responses/Error404"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          ref="#/components/responses/Success200"
     *      )
     * )
     */
    public function destroy(string $id)
    {
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

    /**
     * @OA\Delete(
     *      path="/api/failedJobs/all",
     *      summary="Delete all the failed Jobs",
     *      tags={"jobs"},
     *      description="Delete all jobs in failed_jobs table",
     *      operationId="FailedJobController.destroyAll",
     *      @OA\Response(
     *          response=500,
     *          ref="#/components/responses/Error500"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          ref="#/components/responses/Success200"
     *      )
     * )
     */
    public function destroyAll()
    {
        $result = Artisan::call('queue:flush');
        if ($result != 0) {
            return response()->error500(__('messages.DeleteError'));
        }
        return response()->success200(__('messages.DeleteSuccess'));
    }

    /**
     * @OA\Get(
     *      path="/api/failedJobs/retry/all",
     *      summary="Retry all the failed Jobs",
     *      tags={"jobs"},
     *      description="Retry all jobs in failed_jobs table",
     *      operationId="FailedJobController.retryAll",
     *      @OA\Response(
     *          response=500,
     *          ref="#/components/responses/Error500"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          ref="#/components/responses/Success200"
     *      )
     * )
     */
    public function retryAll()
    {
        $result = Artisan::call('queue:retry', ['id' => 'all']);
        if ($result != 0) {
            return response()->error500(__('messages.RetryError') . $result);
        }
        return response()->success200(__('messages.RetrySuccess'));
    }
}
