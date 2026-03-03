<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Review;
use App\Policies\ReviewPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route; // <--- EZT KELL HOZZĂADNI
use Laravel\Sanctum\Http\Middleware\CheckAbilities; // <--- EZT KELL HOZZĂADNI
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        // Az alapĂ©rtelmezett string hossza 191 karakterre csĂ¶kkentĂ©se
        Schema::defaultStringLength(191);
        //Middleware regisztrĂˇciĂł
        Route::aliasMiddleware('ability', CheckAbilities::class);

        //2. KIVĂ‰TELKEZELĂ‰S REGISZTRĂCIĂ“JA
        Exceptions::renderable(function (AccessDeniedHttpException $e, $request) {
            // Csak API kĂ©rĂ©sekre fusson le
            if ($request->is('api/*')) {
                $message = $e->getMessage() ?? 'Access denied.';

                if (str_contains($message, 'Invalid ability provided.')) {
                    $message = 'Access denied.';
                }

                return response()->json([
                    'message' => $message
                ], 403);
            }
        });

        // Exceptions::renderable(function (\Throwable $e, $request) {
        //     if ($request->is('api/*')) {
        //         // Csak a hiba idejĂ©re, hogy lĂˇsd a valĂłdi kivĂ©telt
        //         if ($e->getMessage() === 'Invalid ability provided.') {
        //             // ĂŤrd ki a konzolra (vagy logba) a teljes hibaĂĽzenetet Ă©s stack trace-t
        //             Log::error('Sanctum Ability Hiba:', ['exception' => $e]);

        //             // KĂĽldd vissza a rĂ©szletes hibaĂĽzenetet
        //             return response()->json([
        //                 'message' => 'Hiba tĂ¶rtĂ©nt a kĂ©pessĂ©gek ellenĹ‘rzĂ©sekor.',
        //                 'error_details' => $e->getMessage(),
        //                 'file' => $e->getFile(),
        //                 'line' => $e->getLine(),
        //             ], 500); // HasznĂˇljunk 500-at technikai hibĂˇra
        //         }
        //     }
        // });


        //Policy regisztrĂˇciĂł
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);
    }
}
