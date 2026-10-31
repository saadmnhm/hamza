<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        Builder::macro('getList', function (
            array $columns = ['*'],
            array $where = [],
            int $perPage = 0
        ) {
            /** @var Builder $this */
            try {
                $query = $this->select($columns);
                $table = $this->getModel()->getTable();

                // 1. Auto-set DeletedAt = null ONLY if the column exists in the database table
                if (! array_key_exists('DeletedAt', $where) && ! array_key_exists('deleted_at', $where)) {
                    if (Schema::hasColumn($table, 'DeletedAt')) {
                        $where['DeletedAt'] = null;
                    } elseif (Schema::hasColumn($table, 'deleted_at')) {
                        $where['deleted_at'] = null;
                    }
                }

                if (! empty($where)) {
                    foreach ($where as $key => $value) {
                        if (is_string($key)) {
                            if (Schema::hasColumn($table, $key)) {
                                if (is_null($value)) {
                                    $query->whereNull($key);
                                } else {
                                    $query->where($key, $value);
                                }
                            }
                        } else {
                            $query->where([$value]);
                        }
                    }
                }

                $data = $perPage > 0 ? $query->paginate($perPage) : $query->get();

                return $data->toArray();

            } catch (\Throwable $e) {
                return [
                    'success' => false,
                    'message' => 'Failed to retrieve data.',
                    'error' => $e->getMessage(),
                ];
            }
        });

        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
            }
        });

        $this->configureDefaults();
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
