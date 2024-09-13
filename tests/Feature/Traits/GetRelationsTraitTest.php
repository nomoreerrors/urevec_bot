<?php

namespace Feature\Traits;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GetRelationsTrait;

class GetRelationsTraitTest extends TestCase
{
    use GetRelationsTrait;
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @method  GetDefinedRelationsName
     * @return void
     */
    public function testBase()
    {
        $result = TestModel::getDefinedRelationsNames();

        $this->assertCount(4, $result);
        $this->assertContains('firstOne', $result);
        $this->assertContains('secondOne', $result);
        $this->assertContains('thirdOne', $result);
        $this->assertContains('fourthOne', $result);
    }

    public function testGetDefinedRelationsNamesReturnType()
    {
        $result = GetRelationsTrait::getDefinedRelationsNames();
        $this->assertIsArray($result);
    }

    public function testReturnsEmptyArrayIfNoRelations()
    {
        $testClass = new class {
            use GetRelationsTrait;
        };

        $result = $testClass::getDefinedRelationsNames();
        $this->assertEmpty($result);
    }

    public function testRelationsPathIsCorrect()
    {
        $this->assertEquals('Illuminate\Database\Eloquent\Relations', $this->getRelationsPath());
    }

    public function testGetDefinedRelationsNamesIgnoresNonRelations()
    {
        $testClass = new class {
            use GetRelationsTrait;

            public function relation1(): BelongsTo
            {
            }

            public function nonRelationMethod(): string
            {
            }
        };
        $result = $testClass::getDefinedRelationsNames();
        $this->assertEquals(['relation1'], $result);
    }


    public function testGetDefinedRelationsNamesHandlesInheritance()
    {
        $testClass = new class extends TestModel {
            public function relation2(): HasMany
            {
                // ...
            }
        };
        $result = $testClass::getDefinedRelationsNames();
        $this->assertEquals(['relation2', 'firstOne', 'secondOne', 'thirdOne', 'fourthOne'], $result);
    }





}


class TestModel extends Model
{
    use GetRelationsTrait;

    public function firstOne(): BelongsToMany
    {
        return $this->createMock(BelongsToMany::class);
    }

    public function secondOne(): BelongsTo
    {
        return $this->createMock(BelongsToMany::class);
    }

    public function thirdOne(): BelongsTo
    {
        return $this->createMock(BelongsToMany::class);
    }

    public function fourthOne(): HasMany
    {
        return $this->createMock(HasMany::class);
    }
}
