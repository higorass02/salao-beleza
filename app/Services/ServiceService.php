<?php

namespace App\Services;

use App\Models\Service as ServiceModel;

class ServiceService
{
    public function list(int $perPage = 20)
    {
        return ServiceModel::orderBy('name')->paginate($perPage)->withQueryString();
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
