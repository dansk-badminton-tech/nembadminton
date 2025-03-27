<?php

namespace App\Console\Commands;

use FlyCompany\Notification\Enum\SubscriptionField;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Nuwave\Lighthouse\Subscriptions\Contracts\StoresSubscriptions;

class SubscriptionClean extends Command
{
    // The name and signature of the console command
    protected $signature = 'lighthouse:clean-up-subscribers';

    // The console command description
    protected $description = 'Removes expired subscribers from topic';

    // Execute the console command
    public function handle(
        StoresSubscriptions $subscriptionStorage
    ) : int
    {
        $this->info('Cleaning up subscribers in topics...');

        foreach (SubscriptionField::cases() as $subscriptionField) {
            $topic = Cache::store('database')->get('graphql.topic.'.strtoupper($subscriptionField->value));
            $existingSubscribers = $subscriptionStorage->subscribersByTopic(strtoupper($subscriptionField->value));

            $filtered = $topic->filter(function ($subscriber) use ($existingSubscribers) {
                return $existingSubscribers->value($subscriber) === null;
            });

            $filtered->each(function ($subscriber) use ($subscriptionStorage) {
                dump("Removing subscriber $subscriber");
                $subscriptionStorage->deleteSubscriber($subscriber);
            });
        }

        return 0;
    }
}

