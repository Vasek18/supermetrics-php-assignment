<?php

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

/**
 * @package Statistics\Calculator
 */
class AveragePostsNumberPerUserPerMonth extends AbstractCalculator
{
    protected const UNITS = 'posts';

    /**
     * @var int
     */
    private int $postCount = 0;

    /**
     * @var array
     */
    protected array $userIDs = [];

    /**
     * @param SocialPostTo $postTo
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $this->postCount++;

        $authorID = $postTo->getAuthorId();
        if ($authorID && empty($this->userIDs[$authorID])) {
            $this->userIDs[$authorID] = 1;
        }
    }

    /**
     * @return StatisticsTo
     */
    protected function doCalculate(): StatisticsTo
    {
        if (empty($this->userIDs) || !$this->postCount) {
            return (new StatisticsTo())->setValue(0);
        }

        $value = $this->postCount / count($this->userIDs);

        return (new StatisticsTo())->setValue(round($value, 2));
    }
}
