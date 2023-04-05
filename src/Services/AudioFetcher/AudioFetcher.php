<?php

namespace App\Services\AudioFetcher;

use App\Services\AudioFetcher\Resources\Surah;
use App\Services\AudioFetcher\Resources\Reciter;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AudioFetcher
{
    public function __construct(
        private HttpClientInterface $client,
        private ResourceBuilder $resourceBuilder,
        private KernelInterface $kernelInterface,
    ) {
        $store = new Store($kernelInterface->getProjectDir() . '/var/cache/data');
        $this->client = new CachingHttpClient($client, $store);
    }

    public function fetchSuwar(): array
    {
        $suwar = [];

        $response = $this->client->request(
            'GET',
            'http://api.alquran.cloud/v1/surah'
        );

        $content = $response->toArray();

        foreach ($content['data'] as $surah) {
            array_push($suwar, $this->resourceBuilder->createResource(Surah::class, $surah));
        }
        return $suwar;
    }

    public function fetchReciters(): array
    {
        $reciters = [];

        $response = $this->client->request(
            'GET',
            'http://api.alquran.cloud/v1/edition?',
            [
                'query' => [
                    'format' => 'audio',
                    'language' => 'ar',
                    'type' => 'versebyverse',
                ]
            ]
        );

        $content = $response->toArray();

        foreach ($content['data'] as $reciter) {
            array_push($reciters, $this->resourceBuilder->createResource(Reciter::class, $reciter));
        }

        return $reciters;
    }

    public function fetchAudioVerses(Reciter $reciter, Surah $surah, int $fromVerse, int $toVerse): array
    {
        $audios = [];

        for ($i = $fromVerse; $i <= $toVerse; $i++) {
            $filename = $this->pad($surah->getNumber()) . $this->pad($i) . '.mp3';
            $path = $this->kernelInterface->getProjectDir() . '/var/mp3/' . $filename;
            $response = $this->client->request(
                'GET',
                'http://api.alquran.cloud/v1/ayah/'. $surah->getNumber() . ':' . $i . '/' . $reciter->getIdentifier(),
            );

            $audioUrl = $response->toArray();
            $audioUrl = $audioUrl['data']['audio'];

            $response = $this->client->request(
                'GET',
                $audioUrl,
                [
                    'headers' => [
                        'Content-Type' => 'audio/mpeg',
                    ],
                ]
            );

            $audio = $response->getContent();

            try {
                $stream = fopen($path, 'w+', false);
                file_put_contents($path, $audio);
                fclose($stream);
                $audios[] = $path;
            } catch (\Throwable $th) {
                throw $th;
            }
        }

        return $audios;
    }

    private function pad($n)
    {
        $n = explode('.', (string)$n);

        if (1 === count($n)) {
            return sprintf("%03d", $n[0]);
        } else if (2 === count($n)) {
            return sprintf("%02d", $n[0]);
        } else {
            implode('.', $n);
            return sprintf("%02d", $n[0]);
        }
    }
}
