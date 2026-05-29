<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service as ServiceModel;
use App\Services\ServiceService;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function index(ServiceService $service)
    {
        return Inertia::render('Services/Index', [
            'services' => $service->list(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Services/Create');
    }

    public function store(StoreServiceRequest $request, ServiceService $service)
    {
        $service->create($request->validated());

        return redirect()->route('services.index');
    }

    public function edit(ServiceModel $serviceModel)
    {
        return Inertia::render('Services/Edit', [
            'service' => $serviceModel,
        ]);
    }

    public function update(UpdateServiceRequest $request, ServiceModel $serviceModel, ServiceService $service)
    {
        $service->update($serviceModel, $request->validated());

        return redirect()->route('services.index');
    }

    public function destroy(ServiceModel $serviceModel)
    {
        $serviceModel->delete();

        return redirect()->route('services.index');
    }
}
