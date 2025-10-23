<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Loops\Exceptions\APIError;
use Loops\Exceptions\RateLimitExceededError;
use Loops\LoopsClient;

class LoopsUpdateContact implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private User $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(LoopsClient $client): void
    {

        $clubhouse = $this->user->clubhouse;
        $clubName = '';
        if($clubhouse !== null){
            $clubName = $clubhouse->name;
        }
        try {
            $properties = [
                'name'       => $this->user->name,
                'firstName'  => $this->user->name,
                'lastName'   => '',
                'createdAt'  => $this->user->created_at,
                'source'     => 'api',
                'subscribed' => true,
                'userGroup'  => 'coache',
                'userId'     => $this->user->id,
                'clubName'  => $clubName
            ];
            $email = $this->user->email;
            if(app()->environment('production')) {
                $result = $client->contacts->update(email: $email, properties: $properties);
            }else{
                Log::debug('Update contact in loops.so');
                Log::debug($email, $properties);
            }
        } catch (RateLimitExceededError $e) {
            // Handle rate limiting
            echo "Rate limit hit. Limit: " . $e->getLimit() . ", requests remaining: " . $e->getRemaining();
        } catch (APIError $e) {
            // Handle API errors (400, 401, 403, etc)
            echo $e->getMessage();
        } catch (\Exception $e) {
            // Handle any other unexpected errors
            echo "Unexpected error: " . $e->getMessage();
        }
    }
}
