<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Services\AudioFetcher\AudioFetcher;
use App\Services\AudioUnify\AudioUnify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private AudioFetcher $audioFetcher,
        private AudioUnify $audioUnify,
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(SearchType::class);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $reciter = $form->get('reciter')->getData();
            $surah = $form->get('surah')->getData();
            $verseFrom = $form->get('verse_from')->getData();
            $verseTo = $form->get('verse_to')->getData();

            $audios = $this->audioFetcher->fetchAudioVerses($reciter, $surah, $verseFrom, $verseTo);
            $audioUnified = $this->audioUnify->unifier($audios);
            
            if ($audioUnified) {
                try {
                    return $this->json(
                        fopen($audioUnified, 'r', false),
                        200,
                        [
                            'Content-Type' => 'audio/mpeg',
                        ]
                    );
                    //code...
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
        }

        return $this->render('home/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
