<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Link;
use App\Enum\LinkStatusEnum;
use App\Repository\LinkRepository;

class LinkService
{
    const CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    // https://www.calculatorsoup.com/calculators/discretemathematics/permutations.php
    const MAX_LINKS_FOR_LENGTH_3 = 226000;
    const MAX_LINKS_FOR_LENGTH_4 = 13380000;
    const MAX_LINKS_FOR_LENGTH_5 = 776520000;
    const MAX_LINKS_FOR_LENGTH_6 = 44261650000;
    const MAX_LINKS_FOR_LENGTH_7 = 2478652606080;

    private int $shortUrlLength;

    public function __construct(
        private readonly LinkRepository $linkRepository,
    )
    {
        $this->shortUrlLength = $this->decideShortUrlLength();
    }

    public function shorten(string $longUrl, ?string $shortUrl = null): Link
    {
        if (!$shortUrl) {
            $shortUrl = $this->generateShortUrl();
        }

        $link = $this->linkRepository->findOneBy(['shortUrl' => $shortUrl]);
        if ($link) {
            throw new \InvalidArgumentException('The short URL already exists');
        }

        $link = new Link();
        $link->setLongUrl($longUrl);
        $link->setShortUrl($shortUrl);
        $link->setStatus(LinkStatusEnum::STATUS_ACTIVE->value);
        $link->setCreatedAt(new \DateTimeImmutable());

        $this->linkRepository->save($link, true);

        return $link;
    }

    private function decideShortUrlLength(): int
    {
        $shortUrlLength = 6;
        $linksCount = $this->linkRepository->count([]);
        if ($linksCount < self::MAX_LINKS_FOR_LENGTH_3) {
            $shortUrlLength = 3;
        } elseif ($linksCount < self::MAX_LINKS_FOR_LENGTH_4) {
            $shortUrlLength = 4;
        } elseif ($linksCount < self::MAX_LINKS_FOR_LENGTH_5) {
            $shortUrlLength = 5;
        } elseif ($linksCount < self::MAX_LINKS_FOR_LENGTH_6) {
            $shortUrlLength = 6;
        } elseif ($linksCount < self::MAX_LINKS_FOR_LENGTH_7) {
            $shortUrlLength = 7;
        } else {
            $shortUrlLength = 8;
        }

        return $shortUrlLength;
    }

    private function generateShortUrl(): string
    {
        do {
            $shortUrl = $this->generateRandomString($this->shortUrlLength);
        } while ($this->linkRepository->findOneBy(['shortUrl' => $shortUrl]));

        return $shortUrl;
    }

    private function generateRandomString(int $length): string
    {
        $charactersLength = strlen(static::CHARACTERS);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= static::CHARACTERS[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
