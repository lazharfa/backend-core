<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\FaqTopic;
use App\Models\Faq;
use Illuminate\Support\Str;

class FaqImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        foreach ($collection as $key => $value) {
            if ($key > 0) {
                $topic = FaqTopic::firstOrNew([
                    'slug'  => Str::slug($value[0]),
                    'name'  => $value[0]
                ]);
                if ($value[1]) {
                    $topic->description = $value[1];
                }
                $topic->save();

                Faq::updateOrCreate([
                    'faq_topic_id'  => $topic->id,
                    'question'      => $value[2]
                ],[
                    'answer'        => $value[3]
                ]);
            }
        }
    }
}
