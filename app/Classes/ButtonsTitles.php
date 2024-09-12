<?php

namespace App\Classes;

use App\Enums\CommandEnums\FiltersSettingsEnum;
use App\Enums\CommandEnums\LinksFilterEnum;
use App\Exceptions\EmptyTitlesArrayException;
use App\Exceptions\TableColumnNotExistsException;
use Illuminate\Support\Facades\Schema;
use App\Services\BotErrorNotificationService;
use App\Enums\CommandEnums\ModerationSettingsEnum;
use App\Enums\CommandEnums\NewUserRestrictionsEnum;
use Illuminate\Database\Eloquent\Model;
use App\Enums\CommandEnums\UnusualCharsFilterEnum;
use App\Enums\CommandEnums\BadWordsFilterEnum;

class ButtonsTitles
{
    /**
     * Summary of __construct
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param mixed $enum Enum::class
     */
    public function __construct(protected ?Model $model = null, protected $enum = null)
    {
        //
    }

    public function getBadWordsFilterTitles(): array
    {
        $result = $this->getTitlesBasedOnModelStatus($this->enum::getMainMenuCases());
        return $result;
    }

    public function getUnusualCharsFilterTitles(): array
    {
        $result = $this->getTitlesBasedOnModelStatus($this->enum::getMainMenuCases());
        return $result;
    }

    public function getLinksFilterTitles(): array
    {
        $result = $this->getTitlesBasedOnModelStatus($this->enum::getMainMenuCases());
        return $result;
    }

    public function getModerationSettingsTitles(): array
    {
        return ModerationSettingsEnum::getValues();
    }

    public function getFiltersSettingsTitles()
    {
        return FiltersSettingsEnum::getValues();
    }


    public function getEditRestrictionsTitles(): array
    {
        $titles = $this->getTitlesBasedOnModelStatus($this->enum::getRestrictionsCases());
        return $titles;
    }

    /**
     * Enum class should include RestrictionsCases trait
     * @return array
     */
    public function getRestrictionsTimeTitles(): array
    {
        return $this->enum::getRestrictionsTimeCasesValues();
    }

    /**
     * Takes all cases from enum and returns titles based on model status
     * @param array $cases toggled columns cases should have postfix _DISABLE and _ENABLE
     * @return array
     */
    public function getTitlesBasedOnModelStatus(array $cases): array
    {
        $titles = [];
        foreach ($cases as $case) {
            // Skip cases that don't have postfix _DISABLE and _ENABLE and add it to the array
            if (
                strpos($case->name, '_DISABLE') === false &&
                strpos($case->name, '_ENABLE') === false
            ) {
                if (!in_array($case->value, $titles))
                    $titles[] = $case->value;

            } else {
                [$columnName, $snakeCaseColumnName] = $this->extractColumnName($case->name);

                if (!$this->columnExists($snakeCaseColumnName)) {
                    throw new TableColumnNotExistsException($snakeCaseColumnName, $this->model->getTable(), __METHOD__);
                }

                $titles = $this->addToggleCase($titles, $columnName, $snakeCaseColumnName);
            }
        }
        if (!empty($titles)) {
            return $titles;
        }

        throw new EmptyTitlesArrayException(__METHOD__);
    }

    /**
     * Extracts the column name and snake case column name from a case.
     *
     * @param string $case The name of the enum case.
     * @return array
     */
    private function extractColumnName(string $case): array
    {
        $columnName = str_replace(['_DISABLE', '_ENABLE'], '', $case);
        $snakeCaseColumnName = strtolower(str_replace(['_DISABLE', '_ENABLE'], '', $case));

        return [$columnName, $snakeCaseColumnName];
    }

    /**
     * Checks if a column exists and adds the appropriate title to the array.
     *
     * @param string $attributeName
     * @param string $snakeCaseAttributeName
     * @param array $titles
     * @return array
     */
    private function addToggleCase(array $titles, string $attributeName, string $snakeCaseAttributeName): array
    {
        $value = $this->model->{$snakeCaseAttributeName};

        $result = match ($value) {
            1 => $this->enum::{$attributeName . '_DISABLE'}->value,
            0 => $this->enum::{$attributeName . '_ENABLE'}->value,
            default => throw new \LogicException("The value of {$snakeCaseAttributeName} must be 0 or 1"),
        };

        if (!in_array($result, $titles)) {
            $titles[] = $result;
        }

        return $titles;
    }

    /**
     * Checks if a column exists in the model's table.
     *
     * @param string $columnName
     * @return bool
     */
    private function columnExists(string $columnName): bool
    {
        $tableName = $this->model->getTable();
        return Schema::hasColumn($tableName, $columnName);
    }
}
