<?php
namespace Febrianrz\RepositoryPattern\Controller;

use App\Contract\ImportInterface;
use Febrianrz\RepositoryPattern\Contract\RestServiceInterface;
use Febrianrz\RepositoryPattern\Resource\DefaultResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RESTController extends \App\Http\Controllers\Controller
{
    /**
     * @var RestServiceInterface
     */
    protected RestServiceInterface $service;
    protected ImportInterface $import;

    protected string $request;
    protected string $resource = DefaultResource::class;
    protected array $makeHidden = [
    ];

    /**
     * MasterDataController constructor.
     *
     * @param RestServiceInterface $service
     */
    public function __construct (RestServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function index (Request $request): JsonResponse
    {
        $result = $this->service->list($request);
        $resource = $this->resource;
        $hidden = array_merge(config('repository-pattern.makeHidden',[]),$this->makeHidden);
        $perPage = $request->input('perPage') ?? 25;
        $totalData = $result->count();
        $data = DataTables::collection($result->paginate($perPage)->getCollection())
            ->setTransformer(function($item) use ($resource){
                return $resource::make($item)->resolve();
            })
            ->addColumn('recordsTotal',function($query){
                return 1000;
            })
            ->makeHidden($hidden)
            ->make(true);
        $data = $data->original;
        $data['recordsAll'] = $totalData;
        $data['currentPage'] = (int)$request->input('page') ?? 1;
        $data['totalPage'] = (int)($data['recordsAll'] > $perPage) ? ceil($data['recordsAll'] / $perPage) : 1;
        $data['isLastPage'] = ($data['currentPage'] >= $data['totalPage']);
        return response()->json($data);
    }

    public function store ()
    {
        $_request = app($this->request);
        $_validated = $_request->validated();
        try {
            $result = $this->service->create($_validated);
            return $this->responseSuccess("Successfully created", $result, 201);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }

    }

    public function update ($id)
    {
        $_request = app($this->request);
        $_validated = $_request->validated();
        try {
            $result = $this->service->update($id, $_validated);
            return $this->responseSuccess("Successfully updated", $result, 200);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }

    }

    public function show ($id)
    {
        try {
            $result = $this->service->detail($id);
            $resource = new $this->resource($result);
            return $this->responseSuccess("Successfully show data", $resource, 200);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }

    }

    public function destroy ($id)
    {
        try {
            $result = $this->service->delete($id);
            return $this->responseSuccess("Successfully deleted", $result, 200);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }

    }

    private function responseSuccess ($message, $data, $status = 200): JsonResponse
    {
        return response()->json([
            'code'    => $status,
            'data'    => $data,
            'message' => $message
        ], $status);
    }

    private function responseError ($message, $status = 400): JsonResponse
    {
        return response()->json([
            'code'    => $status,
            'message' => $message
        ], $status);
    }
}
