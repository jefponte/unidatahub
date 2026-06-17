<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BasicCrudController;
use App\Http\Resources\BondResource;
use App\Models\Bond;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use EloquentFilter\Filterable;
use ReflectionClass;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BondController extends BasicCrudController
{
    private $rules = [];

    public function index(Request $request)
    {
        $userId = Auth::id();
        $perPage = (int) $request->get('per_page', $this->defaultPerPage);
        $hasFilter = in_array(Filterable::class, class_uses($this->model()));

        $query = $this->queryBuilder();

        if ($hasFilter) {
            $query = $query->filter($request->all());
        }

        $data = $request->has('all') || ! $this->defaultPerPage
            ? $query->get()
            : $query->paginate($perPage);

        $resourceCollectionClass = $this->resourceCollection();
        $refClass = new ReflectionClass($resourceCollectionClass);

        return $refClass->isSubclassOf(ResourceCollection::class)
            ? new $resourceCollectionClass($data)
            : $resourceCollectionClass::collection($data);
    }

    public function store(Request $request)
    {
        throw new NotFoundHttpException('Endpoint not available.');
    }


    public function show($id)
    {
        $obj = $this->queryBuilder()
            ->where((new Bond)->getRouteKeyName(), $id)
            ->firstOrFail();

        $resource = $this->resource();
        return new $resource($obj);
    }

    protected function model()
    {
        return Bond::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }

    protected function resourceCollection()
    {
        return BondResource::class;
    }

    protected function resource()
    {
        return BondResource::class;
    }
}
