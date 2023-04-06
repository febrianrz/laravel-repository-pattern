<?php
namespace Febrianrz\RepositoryPattern\Service;

use Febrianrz\RepositoryPattern\Contract\RestServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MainServiceEloquent implements RestServiceInterface
{
    protected Model $model;
    protected array $with = [];

    public function __construct(Model $model) {
        $this->model = $model;
    }

    public function list (Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $_model =  ($this->model)::query();
        if(count($this->getIndexFields()) > 0){
            $_model->select($this->getIndexFields());
        }
        if(count($this->with) > 0) {
            $_model->with($this->with);
        }
        if($request->has('search') && is_array($request->get('search'))){
            $searchs = $request->get('search');
            $_modelObj = new $this->model;
            foreach($searchs as $field => $value){
                if(Schema::hasColumn($_modelObj->getTable(),$field)){
                    $_model->where($field,'like',"%{$value}%");
                }
            }
        }
        return $_model;
    }

    /**
     * @throws \Exception
     */
    public function create ($fields)
    {

        try {
            DB::beginTransaction();
            $_model = new $this->model;

            $_model->fill($fields);
            if(Schema::hasColumn($_model->getTable(),'created_by')){
                $_model->created_by = Auth::id();
            }
            $this->beforeStore($_model,\request());
            $_model->save();
            DB::commit();
            return $_model;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function detail (string $id, array $relationship = [])
    {
        try {
            $_model = $this->model::findOrFail($id);
            return $_model;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function update (string $id, $fields)
    {
        try {
            DB::beginTransaction();
            $_model = $this->model::findOrFail($id);
            $_model->fill($fields);
            if(Schema::hasColumn($_model->getTable(),'updated_by')) {
                $_model->updated_by = Auth::id();
            }
            $_model->save();
            DB::commit();
            return $_model;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function delete (string $id)
    {
        try {
            DB::beginTransaction();
            $_model = $this->model::findOrFail($id);
            $_model->delete();
            DB::commit();
            return $_model;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function restore (string $id)
    {
        // TODO: Implement restore() method.
    }

    public function beforeStore (Model $model, Request $request)
    {
        // TODO: Implement beforeStore() method.
    }

    public function afterStore (Model $model, Request $request)
    {
        // TODO: Implement afterStore() method.
    }

    public function beforeUpdate (Model $model, Request $request)
    {
        // TODO: Implement beforeUpdate() method.
    }

    public function afterUpdate (Model $model, Request $request)
    {
        // TODO: Implement afterUpdate() method.
    }

    public function beforeDelete (Model $model, Request $request)
    {
        // TODO: Implement beforeDelete() method.
    }

    public function afterDelete (Model $model, Request $request)
    {
        // TODO: Implement afterDelete() method.
    }

    public function getIndexFields(): array
    {
        return [];
    }
}
