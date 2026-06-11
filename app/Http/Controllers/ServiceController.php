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

    public function edit(ServiceModel $service)
    {
        return Inertia::render('Services/Edit', [
            'service' => $service,
        ]);
    }

    public function update(UpdateServiceRequest $request, ServiceModel $service, ServiceService $svc)
    {
        $svc->update($service, $request->validated());

        return redirect()->route('services.index');
    }

    public function destroy(ServiceModel $service)
    {
        $service->delete();

        return redirect()->route('services.index');
    }
}
