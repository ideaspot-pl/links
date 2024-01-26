<?php

namespace App\Controller;

use App\Repository\LinkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinkController extends AbstractController
{
    #[Route('/{shortUrl}', requirements: ['longUrl' => '^[a-zA-Z0-9-_]+$'], priority: -1)]
    public function redirectAction(
        $shortUrl,
        LinkRepository $linkRepository,
    ): Response
    {
        $link = $linkRepository->findOneBy(['shortUrl' => $shortUrl]);
        if (!$link) {
            throw $this->createNotFoundException('The link does not exist');
        }

        return $this->redirect($link->getLongUrl());
    }
}
