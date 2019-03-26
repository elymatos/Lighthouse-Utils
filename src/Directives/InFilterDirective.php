<?php

namespace DeInternetJongens\LighthouseUtils\Directives;

use Illuminate\Database\Eloquent\Builder;

class InFilterDirective extends BaseDirective
{
    /**
     * @inheritdoc
     */
    public function handle(string $fieldName, $value, Builder $builder): Builder
    {
        if(!is_array($value)) {
            $v = [];
            foreach ($value->getIterator() as $x) {
                $v[] = $x->value;
            }
        } else {
            $v = $value;
        }
        return $builder->whereIn($fieldName, $v);
    }

    public function name(): string
    {
        return 'in';
    }
}
