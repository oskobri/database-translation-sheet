<?php

namespace Oskobri\DatabaseTranslationSheet\Translations;

class Model
{
    private \Illuminate\Database\Eloquent\Model $model;

    public function __construct($model)
    {
        $this->model = $model;
        $this->locales = config('database-translation-sheet.locales');
    }

    public function getSheetRows(): array
    {
        return array_merge(
            [$this->prepareSheetHeader()],
            $this->prepareSheetData(),
        );
    }

    public function prepareSheetData(): array
    {
        return $this->model
            ->query()
            ->get(array_merge(
                [$this->model->getKeyName()],
                $this->model->getTranslatableAttributes()
            ))
            ->map(function ($model) {
                $translations = [];

                foreach ($model->getTranslatableAttributes() as $translatableAttribute) {
                     foreach ($this->locales as $locale) {
                        $translations[] = $model->getTranslation($translatableAttribute, $locale, false);
                    }
                }

                return array_merge([$model->getKey()], $translations);
            })
            ->toArray();
    }

    public function prepareSheetHeader(): array
    {
        $header = [$this->model->getKeyName()];

        foreach ($this->model->getTranslatableAttributes() as $translatableAttribute) {
            foreach ($this->locales as $locale) {
                $header[] = ucfirst($translatableAttribute) . ' (' . $locale . ')';
            }
        }

        return $header;
    }
}
