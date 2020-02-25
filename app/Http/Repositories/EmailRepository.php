<?php
namespace App\Http\Repositories;

use App\Models\Email;

class EmailRepository
{
    /**
     * Save $model in DB
     *
     * @param Email $model
     * @return boolean
     */
    public function save($model)
    {
        return $model->save();
    }

    /**
     * find Object in DB
     *
     * @param array $query
     * @return boolean
     */
    public function where($query, $value)
    {
        return Email::where($query, $value)->get();
    }

}
