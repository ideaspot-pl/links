<?php

namespace App\Controller;

use App\Entity\Link;
use App\Form\AnonymousLinkType;
use App\Repository\LinkRepository;
use App\Service\LinkService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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

    #[Route('/create')]
    public function create(
        Request $request,
        LinkService $linkService,
    ): Response
    {
        $link = new Link();
        $form = $this->createForm(AnonymousLinkType::class, $link, [
            'validation_groups' => ['create'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $link = $linkService->shorten($link->getLongUrl(), $link->getShortUrl());
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', $e->getMessage());

                return $this->render('link/create.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $this->addFlash('success', "Your link has been successfully shortened!");

            return $this->redirectToRoute('app_link_share', ['shortUrl' => $link->getShortUrl()]);
        }

        return $this->render('link/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/share/{shortUrl}', requirements: ['shortUrl' => '^[a-zA-Z0-9-_]+$'])]
    public function share(
        Link $link,
    ): Response
    {
        return $this->render('link/share.html.twig', [
            'link' => $link,
        ]);
    }

    #[Route('/')]
    public function home(
        ParameterBagInterface $params,
    ): Response
    {
        if ($params->get('app.anonymous_links')) {
            return $this->redirectToRoute('app_link_create');
        }

        $homeRedirect = $params->get('app.home_redirect');
        if ($homeRedirect) {
            return $this->redirect($homeRedirect);
        }

        throw new AccessDeniedHttpException('You are not allowed to access this page');
    }
}
