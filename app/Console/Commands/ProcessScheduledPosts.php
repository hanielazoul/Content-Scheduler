<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class ProcessScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled posts that are due for publication';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $posts = Post::where('status', 'scheduled')
            ->where('scheduled_time', '<=', now())
            ->with('platforms')
            ->get();

        foreach ($posts as $post) {
            $this->info("Processing post: {$post->title}");

            // Mock publishing to each platform
            foreach ($post->platforms as $platform) {
                $this->mockPublishToPlatform($post, $platform);
            }

            $post->update(['status' => 'published']);
            $this->info("Post {$post->title} has been published");
        }

        $this->info('All scheduled posts have been processed');
    }

    protected function mockPublishToPlatform($post, $platform)
    {
        // Simulate platform-specific validation
        switch ($platform->type) {
            case 'twitter':
                if (strlen($post->content) > 280) {
                    $this->error("Post too long for Twitter: {$post->title}");
                    return false;
                }
                break;
            case 'instagram':
                if (!$post->image_url) {
                    $this->error("Post requires image for Instagram: {$post->title}");
                    return false;
                }
                break;
        }

        // Simulate successful publishing
        $post->platforms()->updateExistingPivot($platform->id, [
            'platform_status' => 'published'
        ]);

        $this->info("Published to {$platform->name}");
        return true;
    }
}
