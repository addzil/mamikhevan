<?php

namespace App\Observers;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use WeakMap;

class ModelActivityObserver
{
    /** @var WeakMap<Model, array<string, mixed>>|null */
    private static ?WeakMap $originalAttributes = null;

    public function __construct(private readonly ActivityLogger $activityLogger) {}

    public function created(Model $model): void
    {
        $this->activityLogger->record(
            $this->actionFor($model, 'created'),
            $this->descriptionFor($model, 'created'),
            $model,
            ['attributes' => $model->getAttributes()],
        );
    }

    public function updating(Model $model): void
    {
        self::$originalAttributes ??= new WeakMap;
        self::$originalAttributes[$model] = $model->getRawOriginal();
    }

    public function updated(Model $model): void
    {
        $originalAttributes = [];

        if (self::$originalAttributes !== null && isset(self::$originalAttributes[$model])) {
            $originalAttributes = self::$originalAttributes[$model];
            unset(self::$originalAttributes[$model]);
        }

        $changes = [];

        foreach ($model->getChanges() as $attribute => $value) {
            if (in_array($attribute, ['created_at', 'updated_at'], true)) {
                continue;
            }

            $changes[$attribute] = [
                'before' => $originalAttributes[$attribute] ?? null,
                'after' => $value,
            ];
        }

        if ($changes === []) {
            return;
        }

        $this->activityLogger->record(
            $this->actionFor($model, 'updated'),
            $this->descriptionFor($model, 'updated'),
            $model,
            ['changes' => $changes],
        );
    }

    public function deleted(Model $model): void
    {
        $this->activityLogger->record(
            $this->actionFor($model, 'deleted'),
            $this->descriptionFor($model, 'deleted'),
            $model,
            ['attributes' => $model->getAttributes()],
        );
    }

    private function actionFor(Model $model, string $event): string
    {
        return str(class_basename($model))->snake()->toString().'.'.$event;
    }

    private function descriptionFor(Model $model, string $event): string
    {
        return class_basename($model).' '.$event.'.';
    }
}
