<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use function foo\func;

class DonationsExport implements FromCollection, WithHeadings
{
    protected $donations;
    protected $keys;

    public function __construct($donations, $keys)
    {
        $this->donations = $donations;
        $this->keys = $keys;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return collect($this->donations);
    }

    public function headings(): array
    {

        return $this->keys;

    }
}
