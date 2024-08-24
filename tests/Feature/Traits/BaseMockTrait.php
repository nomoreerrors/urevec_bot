<?php

namespace Tests\Feature\Traits;


trait BaseMockTrait
{
    private function countOrAny(?int $count)
    {
        return $count ? $this->exactly($count) : $this->any();

    }

    private function setupExpectation($method, $returnValue = null, $count = null)
    {
        $this->mockBotService->expects($this->countOrAny($count))
            ->method($method)
            ->willReturn($returnValue);
    }

    private function expectWith($method, mixed $args, ?array $params = [], ?int $count = null)
    {
        if (empty($params)) {
            $this->mockBotService->expects($this->countOrAny($count))
                ->method($method)
                ->with($args);
        } else {

            $this->mockBotService->expects($this->countOrAny($count))
                ->method($method)
                ->with($args, $params);
        }

    }

}