<?php

namespace Modules\PrivateQuestions\Providers;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Gate;
use App\Providers\NovaServiceProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Modules\PrivateQuestions\Models\PrivateQuestion;
use Modules\PrivateQuestions\Policies\PrivateQuestionPolicy;
use Modules\PrivateQuestions\Console\ReleaseUnansweredQuestionCommand;

class PrivateQuestionsServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected $moduleName = 'PrivateQuestions';

    /**
     * @var string
     */
    protected $moduleNameLower = 'privatequestions';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerNova();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        Gate::policy(PrivateQuestion::class, PrivateQuestionPolicy::class);

        $this->commands([
            ReleaseUnansweredQuestionCommand::class
        ]);
    }

    public function registerNova(): void
    {
        Nova::serving(function (ServingNova $event) {
            NovaServiceProvider::resourcesInModule(base_path('Modules/PrivateQuestions/Nova'));
        });
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower.'.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }
}
