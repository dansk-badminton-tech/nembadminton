<?php


namespace FlyCompany\BadmintonPlayerAPI\Models;

use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use FlyCompany\TeamFight\Models\SerializerHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class PlayerRankingIterator implements \Iterator
{

    private int   $offset    = 0;

    private int   $chunkSize = 5000;

    private array $items     = [];

    private int   $position  = 0;

    private bool $overrideCache = false;

    private const CACHE_TTL = 129600;

    public function __construct(
        private readonly ?Client $client,
        private readonly ?RankingPeriodType $periodType,
        private readonly ?Repository $cache
    ) {
    }

    public function current() : mixed
    {
        return $this->items[$this->position];
    }

    /**
     * @throws ExceptionInterface
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function next() : void
    {
        if ($this->position === ($this->offset + $this->chunkSize) - 1) {
            $this->offset += $this->chunkSize;
            $this->updateItems($this->offset);
        }
        ++$this->position;
    }

    public function key() : mixed
    {
        return $this->position;
    }

    public function valid() : bool
    {
        return isset($this->items[$this->position]);
    }

    /**
     * @throws ExceptionInterface
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function rewind() : void
    {
        $this->updateItems($this->offset);

        $this->position = 0;
    }

    /**
     * @param int $start
     *
     * @return void
     * @throws ExceptionInterface
     */
    public function updateItems(int $start) : void
    {
        $end = $this->offset + $this->chunkSize;
        Log::info("Requesting player-ranking {$this->periodType->name} from $start to $end");
        $date = Carbon::now()->format('Y-m-d');
        $cacheKey = md5("{$this->periodType->name}-$start-$this->chunkSize-$date");
        $contents = Cache::lock("$cacheKey-lock", 600)->block(600, function () use ($start, $cacheKey) {
            $content = $this->cache->get($cacheKey);
            if ($content === null || $this->overrideCache) {
                Log::info("Player-ranking {$this->periodType->value} from $start not found in cache");
                $response = $this->client->post('Player/ranking', [
                    'query' => [
                        'rankingType' => $this->periodType->value,
                        'start'       => $start,
                        'stop'        => $this->chunkSize,
                    ],
                    'json'  => [1, 6, 7],
                ]);
                $content = $response->getBody()->getContents();
                $this->cache->put($cacheKey, $content, static::CACHE_TTL);
            }
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        });

        if ($this->periodType === RankingPeriodType::CURRENT) {
            $players = $contents["current"]["playerRankings"];
        } else {
            $players = $contents["previous"]["playerRankings"];
        }
        $serializer = SerializerHelper::getSerializer();
        $items = $serializer->denormalize($players, PlayerRanking::class . '[]');
        $fillerArr = array_fill(0, $start, null);
        $arr = array_merge($fillerArr, $items);
        $this->items = $arr;
    }

    /**
     * @param bool $overrideCache
     */
    public function setOverrideCache(bool $overrideCache) : void
    {
        $this->overrideCache = $overrideCache;
    }
}
