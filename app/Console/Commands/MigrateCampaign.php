<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateCampaign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:campaign';

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

        $campaigns = DB::connection('mysql')->table('page')
            ->select(
                'page_content.title as campaign_title',
                'page_menu.guid as campaign_slug',
                'page_content.donate_need as target_donation',
                'page_content.end_donate as expired_at',
                'page.publish as campaign_status',
                'page_content.description as invitation_message',
                'page_content.body as descriptions',
                'page_category.category as category_name',
                'page_image.file_name as campaign_image',
                'date_created as created_at'
            )
            ->leftJoin('page_content', 'page.page_id', '=', 'page_content.page_id')
            ->leftJoin('page_category_join', 'page.page_id', '=', 'page_category_join.page_id')
            ->leftJoin('page_category', 'page_category_join.page_category_id', '=', 'page_category.page_category_id')
            ->leftJoin('page_image', 'page.page_id', '=', 'page_image.page_id')
            ->leftJoin('page_menu', 'page.page_id', '=', 'page_menu.page_id')
            ->where('page_content.donate_need', '<>', '0')
            ->where('page.is_menu', false)
            ->get();

        foreach ($campaigns as $campaign) {

            $categoryId = null;
            if ($campaign->category_name) {

                $categoryId = DB::connection('pgsql')
                    ->table('categories')
                    ->select('id')
                    ->where('category_name', $campaign->category_name)
                    ->get()->toArray();


                $categoryId = $categoryId[0]->id;

            }

            $campaignStatus = 'Draft';

            if ($campaign->campaign_status) {

                $campaignStatus = 'Publish';

            }

            DB::connection('pgsql')
                ->table('campaigns')
                ->insert([
                    'member_id' => 'insanbumimandiri.org',
                    'category_id' => $categoryId,
                    'creator_id' => 1,
                    'campaign_title' => $campaign->campaign_title,
                    'campaign_title_en' => $campaign->campaign_title,
                    'campaign_slug' => $campaign->campaign_slug,
                    'campaign_slug_en' => $campaign->campaign_slug,
                    'campaign_status' => $campaignStatus,
                    'campaign_image' => $campaign->campaign_image,
                    'target_donation' => $campaign->target_donation,
                    'expired_at' => $campaign->expired_at,
                    'invitation_message' => $campaign->invitation_message,
                    'invitation_message_en' => $campaign->invitation_message,
                    'descriptions' => $campaign->descriptions,
                    'descriptions_en' => $campaign->descriptions,
                    'created_at' => $campaign->created_at,
                    'updated_at' => $campaign->created_at,
                ]);

        }

        DB::commit();

        return $campaigns->count();

    }
}
