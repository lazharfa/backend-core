<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;

class DonationExtraTemplate implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return new Collection([
            ['Nama Donatur', 'Kode Campaign', 'Tanggal', 'Jam','Jumlah Donasi', 'Channel'],
        ]);
    }
}
