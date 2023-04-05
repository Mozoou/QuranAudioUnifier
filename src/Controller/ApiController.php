<?php

namespace App\Controller;

use App\Services\AudioFetcher\AudioFetcher;
use App\Services\AudioFetcher\Resources\Surah;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    public function __construct(private AudioFetcher $audioFetcher)
    {
    }

    #[Route('api/fetch/surah/{id}', name: 'api_fetch_surah')]
    public function fetchSurah(int $id): Response
    {
        $data = [];
        $suwar = $this->audioFetcher->fetchSuwar();
        // Get surah here

        foreach ($suwar as $surah) {
            /** @var Surah $surah */
            if ($surah->getNumber() === $id) {
                $data = [
                    'type' => 'success',
                    'surah' => $surah
                ];
                break;
            }
        }

        return $this->json($data);
    }

    #[Route('api/fetch/editions/{language}', name: 'api_fetch_surah')]
    public function fetchEditions(string $language): Response
    {
        $data = [
            'type' => 'success',
            'editions' => $this->audioFetcher->fetchEditions($language)
        ];
        return $this->json($data);
    }
}
