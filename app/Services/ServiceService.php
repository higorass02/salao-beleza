<?php

namespace App\Services;

use App\Models\Service as ServiceModel;

class ServiceService
{
    public function list()
    {
        return ServiceModel::orderBy('name')->get();
    }

    public function create(array $data)
    {
        return ServiceModel::create($data);
    }

    public function update(ServiceModel $service, array $data)
    {
        $service->update($data);

        return $service;
    }
}
