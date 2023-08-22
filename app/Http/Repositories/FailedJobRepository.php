<?php

namespace App\Http\Repositories;

use App\Models\FailedJob;

class FailedJobRepository
{
    /**
     * Find all the failed jobs
     *
     * @param  String  $orderBy
     * @param  String  $order
     * @return \Illuminate\Database\Eloquent\Collection
     *
     */
    public function all($orderBy = "id", $order = "ASC")
    {
        return FailedJob::orderBy($orderBy, $order)->get();
    }

    /**
     * Find all the failed jobs paginated
     *
     * @param  String  $query
     * @param  String  $orderBy
     * @param  String  $order
     * @param  int  $limit
     * @return App\Models\FailedJob
     *
     */
    public function allPaginated($query, $orderBy, $order, $limit)
    {
        if (!$query) {
            return FailedJob::orderBy($orderBy, $order)
                ->paginate($limit);
        }
        return FailedJob::where('payload', 'LIKE', '%' . $query . '%')
            ->orWhere('exception', 'LIKE', '%' . $query . '%')
            ->orderBy($orderBy, $order)
            ->paginate($limit);
    }

    /**
     * Find a failed job by id
     *
     * @param  int  $id
     * @return App\Models\FailedJob
     *
     */
    public function find($id)
    {
        return FailedJob::find($id);
    }

    /**
     * Delete a failed job from the database
     *
     * @param  App\Models\FailedJob $failedJob
     * @return Response
     *
     */
    public function delete($failedJob)
    {
        return $failedJob->delete();
    }
}
