<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Lets a paginated Livewire screen print every filtered row with <x-print.button all />.
 *
 * The button calls startPrinting(), the next render returns all rows on one page,
 * the browser prints them in the shared A4 design, then stopPrinting() restores the normal page.
 */
trait WithPrintMode
{
    /** Rows printed at most, so a huge table can't hang the browser. */
    public const PRINT_ROW_LIMIT = 5000;

    public bool $printing = false;

    public function startPrinting(): void
    {
        $this->printing = true;
    }

    public function stopPrinting(): void
    {
        $this->printing = false;
    }

    /**
     * Paginate normally, or return every row (from the first row) while printing.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Relations\Relation  $query
     */
    protected function paginateForPrint($query, int|string|null $perPage = null, string $pageName = 'page'): LengthAwarePaginator
    {
        if ($this->printing) {
            return $query->paginate(self::PRINT_ROW_LIMIT, ['*'], $pageName, 1);
        }

        return $query->paginate((int) ($perPage ?? $this->perPage), ['*'], $pageName);
    }
}
