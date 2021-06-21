<?php


namespace App\Imports;


use App\Models\WhatsappAttachment;
use App\Models\WhatsappJob;
use App\Models\WhatsappMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class BroadcastImport implements ToCollection
{
    protected $worker;
    protected $attachmentName;

    public function __construct($worker, $attachmentName)
    {
        $this->worker = $worker;
        $this->attachmentName = $attachmentName;
    }

    public function collection(Collection $contacts)
    {
        foreach ($contacts as $contact) {

            $messageText = str_replace("{name}", $contact[0], $contact[3]);
            $messageText = str_replace("{total_donation}", $contact[2], $messageText);

            $messages = WhatsappMessage::create([
                'message' => $messageText
            ]);

            WhatsappAttachment::create([
                'whatsapp_message_id' => $messages->id,
                'file_name' => $this->attachmentName
            ]);

            WhatsappJob::firstOrCreate(
                [
                    'job_type' => 'Regular',
                    'whatsapp_message_id' => $messages->id,
                    'whatsapp_number' => $contact[1],
                    'worker' => $this->worker
                ],
                [
                    'job_status' => 'On Queue',
                    'priority' => 4,
                    'whatsapp_name' => $contact[0],
                ]
            );
        }
    }
}
