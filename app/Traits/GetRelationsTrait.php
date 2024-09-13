<?php

namespace App\Traits;

trait GetRelationsTrait
{
    private static string $relationsPath = 'Illuminate\Database\Eloquent\Relations';

    /**
     * Summary of getDefinedRelationsNames
     * @param mixed $path parameter to simplify tests
     * @return array
     */
    public static function getDefinedRelationsNames(): array
    {
        $reflector = new \ReflectionClass(get_called_class());
        // $j = $reflector->getMethods();
        $result = collect($reflector->getMethods())
            ->filter(
                fn($method) => !empty ($method->getReturnType()) &&
                str_contains(
                    $method->getReturnType(),
                    self::$relationsPath
                )
            )
            ->pluck('name')
            ->all();

        return $result;
    }

    public function getRelationsPath(): string
    {
        return self::$relationsPath;
    }
}
