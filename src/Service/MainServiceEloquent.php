<?php
namespace Febrianrz\RepositoryPattern\Service;

use Febrianrz\RepositoryPattern\Contract\RestServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MainServiceEloquent implements RestServiceInterface
{
    protected Model $model;
    protected array $with = [];

    protected array $allowedSearch = [];

    public function __construct(Model $model) {
        $this->model = $model;
    }

    public function list (Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $_model =  ($this->model)::query();
        $searchs = $request->get('search');
        if(count($this->getIndexFields()) > 0){
            $_model->select($this->getIndexFields());
        }
        if(count($this->with) > 0) {
            $_model->with($this->with);
        }
        $_modelObj = new $this->model;
        if($request->has('search') && is_array($request->get('search'))){

            foreach($searchs as $field => $value){
                if($field === 'quicksearch') continue;
                if(Schema::hasColumn($_modelObj->getTable(),$field)){
                    $_model->where($field,'like',"%{$value}%");
                }
            }
        }
        if($request->has('as') && is_array($request->get('as'))){
            $as = $request->get('as');
            foreach($as as $key => $value){
                if(Schema::hasColumn($_modelObj->getTable(),$key)){
                    $_model->where($key,'like',"%{$value}%");
                }
            }
        }
        $this->queryIndex($_model);
        if(isset($searchs['quicksearch'])) {
            $this->quickSearch($searchs['quicksearch'],$_model);
        }
        return $_model;
    }

    protected function quickSearch(string $search, $query){
        $fields = $this->allowedSearch;
        $query->where(function($query) use($fields,$search){
            foreach($fields as $f){
                $query->orWhere($f,"like","%{$search}%");
            }
        });
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
            $_model =  ($this->model)::query();
            if(count($this->with) > 0) {
                $_model->with($this->with);
            }
            $_model = $_model->where('id',$id)->firstOrFail();
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

    public function queryIndex(Builder &$model) {

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
