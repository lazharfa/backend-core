<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateBlog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:blog';

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

        $blogs = DB::connection('mysql')->table('page')
            ->select(
                'page_content.title as news_title',
                'page_menu.guid as news_slug',
                'page_content.body as news_content',
                'page_image.file_name as news_image',
                'date_created as news_date',
                'date_created as sent_at',
                'date_created as created_at',
                'date_created as updated_at'
            )
            ->leftJoin('page_content', 'page.page_id', '=', 'page_content.page_id')
            ->leftJoin('page_category_join', 'page.page_id', '=', 'page_category_join.page_id')
            ->leftJoin('page_category', 'page_category_join.page_category_id', '=', 'page_category.page_category_id')
            ->leftJoin('page_image', 'page.page_id', '=', 'page_image.page_id')
            ->leftJoin('page_menu', 'page.page_id', '=', 'page_menu.page_id')
            ->where('page_content.donate_need', '0')
            ->where('page.is_menu', false)
            ->where('page_content.title', '<>', 'test')
            ->orderBy('created_at')
            ->get();


        foreach ($blogs as $blog) {


            DB::connection('pgsql')
                ->table('campaign_news')
                ->insert([
                    'member_id' => 'insanbumimandiri.org',
                    'news_title' => $blog->news_title,
                    'news_title_en' => $blog->news_title,
                    'news_content' => $blog->news_content,
                    'news_content_en' => $blog->news_content,
                    'news_date' => $blog->news_date,
                    'news_slug' => $blog->news_slug,
                    'news_slug_en' => $blog->news_slug,
                    'category_id' => 11,
                    'news_image' => $blog->news_image,
                    'creator_id' => 1,
                    'sent_at' => $blog->sent_at,
                    'created_at' => $blog->created_at,
                    'updated_at' => $blog->created_at,
                ]);


        }

        DB::commit();

        return $blogs->count();

    }
}
