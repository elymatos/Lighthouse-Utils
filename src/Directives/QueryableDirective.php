<?php

namespace DeInternetJongens\LighthouseUtils\Directives;

use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Schema\Values\ArgumentValue;
use Nuwave\Lighthouse\Support\Contracts\ArgMiddleware;
use Nuwave\Lighthouse\Support\Traits\HandlesQueryFilter;
use Nuwave\Lighthouse\Schema\DirectiveRegistry;

class QueryableDirective extends \Nuwave\Lighthouse\Schema\Directives\BaseDirective implements ArgMiddleware
{
    use HandlesQueryFilter;

    /**
     * Apply transformations on the ArgumentValue.
     *
     * @param ArgumentValue $argument
     * @param \Closure $next
     *
     * @return ArgumentValue
     */
    public function handleArgument(ArgumentValue $argument, \Closure $next)
    {
        $argument = $this->injectFilter(
            $argument,
            function (Builder $builder, string $key, array $arguments): Builder {

                return $this->handle($key, $arguments, $builder);
            }
        );

        return $next($argument);
    }
    /**
     * @inheritdoc
     */
    public function handle(string $fieldName, array $arguments, Builder $builder): Builder
    {
        $directives = resolve(DirectiveRegistry::class);
        $directive = $directives->get($arguments['operator']);
        return $directive->handle($fieldName, $arguments['value'], $builder);
        /*

        if ($arguments['operator'] == 'IN') {
            $directive = $directives->get('in');
            $v = [];
            foreach($arguments['value']->getIterator() as $x) {
                $v[] = $x->value;
            }
            //return $builder->whereIn($fieldName, $v);
            return $directive->handle($fieldName, $v, $builder);
        } else {
            return $builder->where($fieldName, $arguments['operator'], $arguments['value']);
        }
        */

    }

    public function name(): string
    {
        return 'queryable';
    }

}
