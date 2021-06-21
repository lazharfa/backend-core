<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportPayments implements FromCollection, WithHeadings
{
    protected $startDate;
    protected $endDate;
    protected $keys;
    protected $paymentReceived;

    public function __construct($startDate, $endDate)
    {

        $this->startDate = $startDate;
        $this->endDate = $endDate;

        $this->paymentReceived = DB::table('payment_received')
            ->orWhere(function ($query) {

                $query->where('payment_at', '>=', $this->startDate)
                    ->where('payment_at', '<=', $this->endDate);

            })->orWhere(function ($query) {
                $query->where('date_donation', '>=', $this->startDate)
                    ->where('date_donation', '<=', $this->endDate);
            })
            ->orderBy('payment_at')
            ->get();

        $this->keys = collect($this->paymentReceived->first())->keys()->all();


    }

    /**
     * @return Collection
     */
    public function collection()
    {

        return collect($this->paymentReceived);

    }

    public function headings(): array
    {

        return $this->keys;

    }

}
