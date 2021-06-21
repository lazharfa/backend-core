<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateNewsCampaign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:newsCampaign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        DB::beginTransaction();

        $newsPrograms = DB::connection('mysql')->table('donate_progress')
            ->select(
                'page_content.title as campaign_title',
                'donate_progress.title as news_title',
                'donate_progress.description as news_content',
                'donate_progress.image as news_image',
                'donate_progress.date as news_date',
                'donate_progress.date as sent_at',
                'donate_progress.date as created_at',
                'donate_progress.date as updated_at'
            )
            ->leftJoin('page', 'donate_progress.donate_id', '=', 'page.page_id')
            ->leftJoin('page_content', 'page.page_id', '=', 'page_content.page_id')
            ->where('page_content.donate_need', '<>', '0')
            ->orderBy('donate_progress.progress_id')
            ->get();

        foreach ($newsPrograms as $newsProgram) {

            $campaignId = null;

            if ($newsProgram->campaign_title) {

                $campaignId = DB::connection('pgsql')
                    ->table('campaigns')
                    ->select('id')
                    ->where('campaign_title', $newsProgram->campaign_title)
                    ->get()->toArray();


                $campaignId = $campaignId[0]->id;

            }

            DB::connection('pgsql')
                ->table('campaign_news')
                ->insert([
                    'member_id' => 'insanbumimandiri.org',
                    'campaign_id' => $campaignId,
                    'news_title' => $newsProgram->news_title,
                    'news_title_en' => $newsProgram->news_title,
                    'news_content' => $newsProgram->news_content,
                    'news_content_en' => $newsProgram->news_content,
                    'news_date' => $newsProgram->news_date,
                    'category_id' => 8,
                    'news_image' => $newsProgram->news_image,
                    'creator_id' => 1,
                    'sent_at' => $newsProgram->sent_at,
                    'created_at' => $newsProgram->created_at,
                    'updated_at' => $newsProgram->created_at,
                ]);


        }

        DB::commit();

        return $newsPrograms->count();

    }
}
