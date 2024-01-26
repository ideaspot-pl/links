<?php

namespace App\Command;

use App\Service\LinkService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'link:shorten',
    description: 'Shortens a long URL to a short URL',
)]
class LinkShortenCommand extends Command
{
    private string $linkBaseUrl;

    public function __construct(
        private readonly LinkService $linkService,
        private readonly ParameterBagInterface $params,
    )
    {
        $this->linkBaseUrl = $this->params->get('app.link_base_url');

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('longUrl', InputArgument::REQUIRED, 'URL to shorten')
            ->addArgument('shortUrl', InputArgument::OPTIONAL, 'Optional short URL')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $longUrl = $input->getArgument('longUrl');
        $shortUrl = $input->getArgument('shortUrl');

        $link = $this->linkService->shorten($longUrl, $shortUrl);

        $io->success(sprintf('Short URL: %s/%s', $this->linkBaseUrl, $link));

        return Command::SUCCESS;
    }
}
