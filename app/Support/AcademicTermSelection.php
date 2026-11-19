<?php

namespace App\Support;

use Illuminate\Http\Request;

final class AcademicTermSelection
{
    /**
     * @param  list<string>  $terms
     */
    public static function fromRequest(Request $request, array $terms): ?int
    {
        $term = $request->query('term', 'all');

        abort_unless(is_string($term), 404);

        if ($term === 'all') {
            return null;
        }

        abort_unless(ctype_digit($term) && array_key_exists((int) $term, $terms), 404);

        return (int) $term;
    }

    public static function value(?int $index): string
    {
        return $index === null ? 'all' : (string) $index;
    }
}
