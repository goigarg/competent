<?php

namespace App\Providers;

use App\Enums\Language;
use App\Services\Ai\Contracts\AiAssistantInterface;
use App\Services\Ai\OpenRouterClient;
use App\Services\AustrianGov\IntentMatcher;
use App\Services\Citations\Contracts\CitationResolver;
use App\Services\Citations\TopicCitationResolver;
use App\Services\Content\ContentRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AiAssistantInterface::class, function () {
            return new OpenRouterClient;
        });

        // Swap for a verification backend later; it sets `verified` on the sources an answer cites.
        $this->app->bind(CitationResolver::class, TopicCitationResolver::class);

        // Content files and intent keywords are read once per request.
        $this->app->singleton(ContentRepository::class, fn () => new ContentRepository);
        $this->app->singleton(IntentMatcher::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureLanguages();
    }

    /**
     * Missing UI strings follow the same fallback chain as content (e.g. tr → en → de, Leichte Sprache → de).
     */
    protected function configureLanguages(): void
    {
        Lang::determineLocalesUsing(function (array $locales): array {
            $language = Language::tryFrom((string) ($locales[0] ?? ''));

            if ($language === null) {
                return $locales;
            }

            $chain = array_map(fn (Language $l) => $l->value, $language->contentChain());

            return array_values(array_unique([...$chain, ...array_slice($locales, 1)]));
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
