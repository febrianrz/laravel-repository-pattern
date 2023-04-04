<?php

namespace Febrianrz\RepositoryPattern\Command;

use Illuminate\Console\Command;

class MakeServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:pattern
        {modelName : Name of model}
        {--path= : where path on controllers and services example: Admin}
        {--no-controller : create without controller}
        {--no-request : create without request}
        {--no-resource : create without resource}
        {--no-model : create without model}
        {--no-service : create without service}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create repository pattern';

    protected string $modelName;
    private bool $isCreateController = true;
    private bool $isCreateRequest = true;
    private bool $isCreateResource = true;
    private bool $isCreateModel = true;
    private bool $isCreateService = true;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->modelName = $this->argument('modelName');
        $this->info("Model Name: ".$this->modelName);
        $this->isCreateController = !$this->option('no-controller');
        $this->isCreateRequest = !$this->option('no-request');
        $this->isCreateModel = !$this->option('no-model');
        $this->isCreateService = !$this->option('no-service');
        $this->isCreateResource = !$this->option('no-resource');

        $this->createController();
        $this->createRequest();
        $this->createResource();
        $this->createModel();
        $this->createService();

        return Command::SUCCESS;
    }

    private function createController(){
        if($this->isCreateController){
            $stub = $this->getStub('Controller');
            $replacement = [
                '{ModelName}' => $this->modelName,
                '{lineRequest}'=> $this->isCreateRequest ? '$this->request'." = \App\Http\Requests\\".$this->modelName."Request::class;" : '',
                '{lineResource}' => $this->isCreateResource ? '$this->resource'." = \App\Http\Resources\\".$this->modelName."Resource::class;" : ''
            ];
            $stub = $this->replaceStub($stub,$replacement);
            $newFileName = "{$this->modelName}Controller.php";
            $path = app_path()."/Http/Controllers/".$newFileName;
            file_put_contents($path, $stub);
            $this->info("Success create controller at {$path}");
        }
    }

    private function createRequest(){
        if($this->isCreateRequest){
            $stub = $this->getStub('Request');
            $replacement = [
                '{ModelName}' => $this->modelName
            ];
            $stub = $this->replaceStub($stub,$replacement);
            $newFileName = "{$this->modelName}Request.php";
            $path = app_path()."/Http/Requests/".$newFileName;
            file_put_contents($path, $stub);
            $this->info("Success create request at {$path}");
        }
    }

    private function createResource(){
        if($this->isCreateResource){
            $stub = $this->getStub('Resource');
            $replacement = [
                '{ModelName}' => $this->modelName
            ];
            $stub = $this->replaceStub($stub,$replacement);
            $newFileName = "{$this->modelName}Resource.php";
            $path = app_path()."/Http/Resources/".$newFileName;
            file_put_contents($path, $stub);
            $this->info("Success create resource at {$path}");
        }
    }

    private function createModel(){
        if($this->isCreateModel){
            $stub = $this->getStub('Model');
            $replacement = [
                '{ModelName}' => $this->modelName
            ];
            $stub = $this->replaceStub($stub,$replacement);
            $newFileName = "{$this->modelName}.php";
            $path = app_path()."/Models/".$newFileName;
            file_put_contents($path, $stub);
            $this->info("Success create model at {$path}");
        }
    }

    private function createService(){
        if($this->isCreateService){
            $stub = $this->getStub('Service');
            $replacement = [
                '{ModelName}' => $this->modelName
            ];
            $stub = $this->replaceStub($stub,$replacement);
            $newFileName = "{$this->modelName}Service.php";
            $path = app_path()."/Services/".$newFileName;
            file_put_contents($path, $stub);
            $this->info("Success create service at {$path}");
        }
    }

    private function getStub($file){
        return file_get_contents(__DIR__ . "/../../stubs/${file}.stub");
    }

    private function replaceStub($stub,$replacements){
        $stub = str_replace(array_keys($replacements), array_values($replacements), $stub);
        return $stub;
    }
}
