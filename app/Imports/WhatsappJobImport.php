<?php

namespace App\Imports;

use App\Models\WhatsappJob;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class WhatsappJobImport implements ToCollection
{
    protected $whatsappMessageId = null;
    protected $worker = null;
    protected $priority = null;

    public function __construct($worker, $priority, $whatsappMessageId)
    {
        $this->worker = $worker;
        $this->priority = $priority;
        $this->whatsappMessageId = $whatsappMessageId;
    }

    public function collection(Collection $contacts)
    {

        foreach ($contacts as $contact) {

            $contact = collect($contact);

            $whatsappNumber = preg_replace('~\D~', '', preg_replace('/^0/', '62', $contact[1]));
            $whatsappNumber = preg_replace('/^620/', '62', $whatsappNumber);

            WhatsappJob::firstOrCreate(
                [
                    'job_type' => 'Regular',
                    'whatsapp_message_id' => $this->whatsappMessageId,
                    'whatsapp_number' => $whatsappNumber,
                    'worker' => $this->worker
                ],
                [
                    'job_status' => 'On Queue',
                    'priority' => $this->priority,
                    'whatsapp_name' => $contact[0],
                ]
            );

        }
    }
}
