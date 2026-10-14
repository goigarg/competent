<?php

namespace App\Livewire\Concerns;

use App\Enums\Language;
use Illuminate\Support\Facades\App;

/**
 * Keeps Laravel's locale in sync with the component's $locale on every Livewire request.
 *
 * @property string $locale
 */
trait AppliesLanguage
{
    public function hydrateAppliesLanguage(): void
    {
        $this->applyLanguage();
    }

    public function language(): Language
    {
        return Language::fromLocale($this->locale);
    }

    protected function applyLanguage(): void
    {
        App::setLocale($this->language()->value);
    }
}
