<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;

class DonationTemplate implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return new Collection([
            ['Nama Donatur', 'Email', 'No Telpon', 'Jumlah Donasi', 'Campaign'],
        ]);
    }
}
