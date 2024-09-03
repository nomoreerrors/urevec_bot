<?php

namespace Feature\TraitsTests;

use App\Services\TelegramBotService;
use stdClass;
use App\Models\Chat;
use Tests\Feature\Traits\MockBotService;
use Tests\TestCase;

class ToggleColumnTest extends TestCase
{
    use MockBotService;

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * ToggleColumn method should set isMenuRefreshFlag to false if it's true
     * and stop the code execution
     * @return void
     */
    public function testToggleColumnSetsFlagToFalseAndReturn()
    {
        $this->mockMenuCreate();
        $this->mockMenu->expects($this->once())
            ->method("getIsMenuRefresh")
            ->willReturn(true);

        $this->mockMenu->expects($this->once())
            ->method("setIsMenuRefresh")
            ->with(false);

        $this->mockBotCreate();
        $this->mockBotService->expects($this->exactly(2))
            ->method('menu')
            ->willReturn($this->mockMenu);


        (new ClassWIthToggleColumnTrait($this->mockBotService))->callToggleColumn('test');
    }


    public function testUpdatesColumnToOppositeValueZero()
    {
        $this->skipMenuRefreshTestsAndMockMenu();
        $this->mockBotCreate();
        $this->mockBotMenuCreate();

        $fakeModel = $this->createMock(FakeModel::class);
        // update value is set to 1
        $fakeModel->expects($this->once())
            ->method("update")
            ->with(["someColumn" => 0]);


        $classWithToggleColumnTrait = $this->getMockBuilder(ClassWIthToggleColumnTrait::class)
            ->setConstructorArgs([$this->mockBotService, $fakeModel])
            ->onlyMethods(['sendToggleMessage', 'refreshMenu'])
            ->getMock();

        $classWithToggleColumnTrait->callToggleColumn("someColumn");
    }

    public function testUpdatesColumnToOppositeValueOne()
    {
        $this->skipMenuRefreshTestsAndMockMenu();
        $this->mockBotCreate();

        $fakeModel = $this->createMock(FakeModel::class);
        $fakeModel->someColumn = 0;

        $fakeModel->expects($this->once())
            ->method("update")
            ->with(["someColumn" => 1]);

        $this->mockBotMenuCreate();

        $classWithToggleColumnTrait = $this->getMockBuilder(ClassWIthToggleColumnTrait::class)
            ->setConstructorArgs([$this->mockBotService, $fakeModel])
            ->onlyMethods(['sendToggleMessage', 'refreshMenu'])
            ->getMock();

        $classWithToggleColumnTrait->callToggleColumn("someColumn");
    }

    public function testReplyMessageAndRefreshMenu()
    {
        $this->skipMenuRefreshTestsAndMockMenu();
        $this->mockBotCreate();

        $fakeModel = $this->createMock(FakeModel::class);

        $this->mockBotService->expects($this->once())
            ->method('sendMessage')
            ->with(FakeEnum::FAKE_ENABLE->replyMessage());

        $this->mockMenu->expects($this->exactly(1))
            ->method('refresh');
        $this->mockBotMenuCreate();

        $classWithToggleColumnTrait = $this->getMockBuilder(ClassWIthToggleColumnTrait::class)
            ->setConstructorArgs([$this->mockBotService, $fakeModel])
            ->onlyMethods(['sendToggleMessage', 'refreshMenu'])
            ->getMock();

        $classWithToggleColumnTrait->callToggleColumn("someColumn");
    }

    private function skipMenuRefreshTestsAndMockMenu()
    {
        $this->mockMenuCreate();
        $this->mockMenu->expects($this->once())
            ->method("getIsMenuRefresh")
            ->willReturn(false);
    }

}

class ClassWIthToggleColumnTrait
{
    use \App\Traits\ToggleColumn;

    private string $enum;

    public function __construct(private TelegramBotService $botService, protected $model = null)
    {
        $this->model = $model;
        $this->enum = FakeEnum::class;
        $this->command = 'abracadabra';
    }
    public function callToggleColumn(string $column)
    {
        $this->toggleColumn($column);
    }

}

class FakeModel
{
    public $someColumn = 1;
    public function update(array $data)
    {
        return null;
    }
}

enum FakeEnum: string
{
    case FAKE_ENABLE = 'abracadabra';

    public function replyMessage()
    {
        return match ($this) {
            self::FAKE_ENABLE => 'Включено',
        };
    }
}



