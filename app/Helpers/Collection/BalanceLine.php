<?php
/**
 * BalanceLine.php
 * Copyright (C) 2016 thegrumpydictator@gmail.com
 *
 * This software may be modified and distributed under the terms of the
 * Creative Commons Attribution-ShareAlike 4.0 International License.
 *
 * See the LICENSE file for details.
 */

declare(strict_types=1);

namespace FireflyIII\Helpers\Collection;

use Carbon\Carbon;
use FireflyIII\Models\Budget as BudgetModel;
use FireflyIII\Models\BudgetLimit;
use Illuminate\Support\Collection;

/**
 *
 * Class BalanceLine
 *
 * @package FireflyIII\Helpers\Collection
 */
class BalanceLine
{

    const ROLE_DEFAULTROLE = 1;
    const ROLE_TAGROLE     = 2;
    const ROLE_DIFFROLE    = 3;

    /** @var  Collection */
    protected $balanceEntries;

    /** @var BudgetModel */
    protected $budget;
    /** @var  BudgetLimit */
    protected $budgetLimit;
    /** @var int */
    protected $role = self::ROLE_DEFAULTROLE;

    /**
     *
     */
    public function __construct()
    {
        $this->balanceEntries = new Collection;

    }

    /**
     * @param BalanceEntry $balanceEntry
     */
    public function addBalanceEntry(BalanceEntry $balanceEntry)
    {
        $this->balanceEntries->push($balanceEntry);
    }

    /**
     * @return Collection
     */
    public function getBalanceEntries(): Collection
    {
        return $this->balanceEntries;
    }

    /**
     * @param Collection $balanceEntries
     */
    public function setBalanceEntries(Collection $balanceEntries)
    {
        $this->balanceEntries = $balanceEntries;
    }

    /**
     * @return BudgetModel
     */
    public function getBudget(): BudgetModel
    {
        return $this->budget ?? new BudgetModel;
    }

    /**
     * @param BudgetModel $budget
     */
    public function setBudget(BudgetModel $budget)
    {
        $this->budget = $budget;
    }

    /**
     * @return BudgetLimit
     */
    public function getBudgetLimit(): BudgetLimit
    {
        return $this->budgetLimit;
    }

    /**
     * @param BudgetLimit $budgetLimit
     */
    public function setBudgetLimit(BudgetLimit $budgetLimit)
    {
        $this->budgetLimit = $budgetLimit;
    }

    /**
     * @return Carbon
     */
    public function getEndDate()
    {
        return $this->budgetLimit->end_date ?? new Carbon;
    }

    /**
     * @return int
     */
    public function getRole(): int
    {
        return $this->role;
    }

    /**
     * @param int $role
     */
    public function setRole(int $role)
    {
        $this->role = $role;
    }

    /**
     * @return Carbon
     */
    public function getStartDate()
    {
        return $this->budgetLimit->start_date ?? new Carbon;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return string
     */
    public function getTitle(): string
    {
        if ($this->getBudget() instanceof BudgetModel && !is_null($this->getBudget()->id)) {
            return $this->getBudget()->name;
        }
        if ($this->getRole() == self::ROLE_DEFAULTROLE) {
            return strval(trans('firefly.no_budget'));
        }
        if ($this->getRole() == self::ROLE_TAGROLE) {
            return strval(trans('firefly.coveredWithTags'));
        }
        if ($this->getRole() == self::ROLE_DIFFROLE) {
            return strval(trans('firefly.leftUnbalanced'));
        }

        return '';
    }

    /**
     * If a BalanceLine has a budget/repetition, each BalanceEntry in this BalanceLine
     * should have a "spent" value, which is the amount of money that has been spent
     * on the given budget/repetition. If you subtract all those amounts from the budget/repetition's
     * total amount, this is returned:
     *
     * @return string
     */
    public function leftOfRepetition(): string
    {
        $start = $this->budgetLimit->amount ?? '0';
        /** @var BalanceEntry $balanceEntry */
        foreach ($this->getBalanceEntries() as $balanceEntry) {
            $start = bcadd($balanceEntry->getSpent(), $start);
        }

        return $start;
    }
}
