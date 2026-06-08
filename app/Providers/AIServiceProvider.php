<?php

namespace App\Providers;

use App\Services\AI\AIManager;
use App\Services\AI\AIRouter;
use App\Services\AI\BlueprintService;
use App\Services\AI\CodeGeneratorService;
use App\Services\AI\ComponentRegistry;
use App\Services\AI\DesignVariantService;
use App\Services\AI\KnowledgeBaseService;
use App\Services\AI\MetadataCrudGenerator;
use App\Services\AI\MultilingualNormalizer;
use App\Services\AI\Pipeline\PipelineOrchestrator;
use App\Services\AI\Pipeline\BuildValidator;
use App\Services\AI\SeniorDevKnowledgeBase;
use App\Services\AI\WisdomEngine;
use App\Services\Module\ModuleInstaller;
use App\Services\Module\ModuleRegistry;
use Illuminate\Support\ServiceProvider;

class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AIManager::class, fn() => new AIManager());
        $this->app->singleton(AIRouter::class, fn() => new AIRouter());
        $this->app->singleton(MultilingualNormalizer::class, fn() => new MultilingualNormalizer());
        $this->app->singleton(ModuleRegistry::class, fn() => new ModuleRegistry());

        $this->app->singleton(SeniorDevKnowledgeBase::class, fn() => new SeniorDevKnowledgeBase());
        $this->app->singleton(WisdomEngine::class, fn() => new WisdomEngine());
        $this->app->singleton(KnowledgeBaseService::class, fn() => new KnowledgeBaseService());
        $this->app->singleton(DesignVariantService::class, fn() => new DesignVariantService());
        $this->app->singleton(ComponentRegistry::class, fn() => new ComponentRegistry());

        $this->app->singleton(ModuleInstaller::class, fn($app) => new ModuleInstaller(
            $app->make(ModuleRegistry::class)
        ));

        $this->app->singleton(CodeGeneratorService::class, fn($app) => new CodeGeneratorService(
            $app->make(AIManager::class),
            $app->make(BlueprintService::class),
            $app->make(MetadataCrudGenerator::class),
            $app->make(AIRouter::class),
            $app->make(MultilingualNormalizer::class),
            $app->make(DesignVariantService::class),
            $app->make(KnowledgeBaseService::class),
            $app->make(SeniorDevKnowledgeBase::class),
            $app->make(WisdomEngine::class),
            $app->make(ComponentRegistry::class),
        ));

        $this->app->singleton(PipelineOrchestrator::class, fn($app) => new PipelineOrchestrator(
            $app->make(AIManager::class),
            $app->make(CodeGeneratorService::class),
            $app->make(BuildValidator::class),
            $app->make(KnowledgeBaseService::class),
            $app->make(SeniorDevKnowledgeBase::class),
            $app->make(WisdomEngine::class),
        ));

        $this->app->alias(AIManager::class, 'ai');
    }

    public function boot(): void {}
}
