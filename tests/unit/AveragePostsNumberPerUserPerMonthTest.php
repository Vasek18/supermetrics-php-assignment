<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use SocialPost\Client\SocialClientInterface;
use SocialPost\Driver\FictionalDriver;
use SocialPost\Dto\FetchParamsTo;
use SocialPost\Hydrator\FictionalPostHydrator;
use SocialPost\Service\SocialPostService;
use Statistics\Calculator\AveragePostsNumberPerUserPerMonth;
use Statistics\Dto\ParamsTo;
use Statistics\Enum\StatsEnum;

/**
 * @package Tests\unit
 */
class AveragePostsNumberPerUserPerMonthTest extends TestCase
{
    /**
     * @dataProvider postsResponseProvider
     * @test
     */
    public function testCalculation(string $responseFile, float $count): void
    {
        $socialClient = $this->createMock(SocialClientInterface::class);
        $socialClient->expects($this->once())->method('get')->willReturn(
            file_get_contents(__DIR__ . '/../data/' . $responseFile)
        );
        $socialClient->expects($this->any())->method('authRequest')->willReturn(
            file_get_contents(__DIR__ . '/../data/auth-token-response.json')
        );

        $driver = new FictionalDriver($socialClient);
        $hydrator = new FictionalPostHydrator();
        $socialPostService = new SocialPostService($driver, $hydrator);
        $fetchParams = new FetchParamsTo(1, 1);
        $posts = $socialPostService->fetchPosts($fetchParams);

        $calculator = new AveragePostsNumberPerUserPerMonth();
        $param = (new ParamsTo())
            ->setStatName(StatsEnum::AVERAGE_POST_NUMBER_PER_USER)
            ->setStartDate((new \DateTime('2018-08-01T00:00:00+00:00')))
            ->setEndDate((new \DateTime('2018-08-31T00:00:00+00:00')));
        $calculator->setParameters($param);

        foreach ($posts as $post) {
            $calculator->accumulateData($post);
        }

        $stats = $calculator->calculate();

        $this->assertEquals($count, $stats->getValue());
    }

    public function postsResponseProvider(): array
    {
        return [
            ['social-posts-response.json', 1.25],
            ['social-posts-response-empty.json', 0],
            ['social-posts-response-no-author.json', 0],
        ];
    }
}
