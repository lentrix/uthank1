<?php

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LendingDemoSeeder extends Seeder
{
    private const DEBTOR_COUNT = 1_600;

    private const FIRST_EMAIL = 'debtor-0001@lending.example';

    private const LOCATIONS = [
        ['Poblacion', 'Minglanilla', 'Cebu'],
        ['Talisay', 'Daanbantayan', 'Cebu'],
        ['Poblacion', 'Carcar', 'Cebu'],
        ['Poblacion', 'Dauis', 'Bohol'],
        ['Poblacion', 'Jagna', 'Bohol'],
        ['Poblacion', 'Valencia', 'Negros Oriental'],
        ['Poblacion', 'Sibulan', 'Negros Oriental'],
        ['Poblacion', 'San Jose', 'Negros Oriental'],
    ];

    private const FIRST_NAMES = [
        'Adrian', 'Althea', 'Ana', 'Angela', 'Antonio', 'Carlo', 'Cecilia', 'Daniel',
        'Elena', 'Enrico', 'Francis', 'Gabriel', 'Grace', 'Isabel', 'Jose', 'Joy',
        'Katrina', 'Leo', 'Liza', 'Manuel', 'Maria', 'Maricel', 'Miguel', 'Ramon',
        'Rhea', 'Rio', 'Rosa', 'Teresa', 'Vicente', 'Vilma',
    ];

    private const LAST_NAMES = [
        'Abad', 'Aquino', 'Bautista', 'Cabrera', 'Castillo', 'Cruz', 'Cunanan',
        'Dela Cruz', 'Domingo', 'Fernandez', 'Flores', 'Garcia', 'Gonzales', 'Lim',
        'Mendoza', 'Morales', 'Navarro', 'Ocampo', 'Pascual', 'Ramos', 'Reyes',
        'Rivera', 'Santos', 'Torres', 'Villanueva',
    ];

    private const OCCUPATIONS = [
        'Market vendor', 'Tricycle driver', 'Sari-sari store owner', 'Dressmaker',
        'Farm worker', 'Fisher', 'Sales clerk', 'Teacher', 'Bookkeeper',
        'Food stall owner', 'Delivery rider', 'Construction worker',
    ];

    private const PRODUCTS = [
        'daily' => ['terms' => [45, 90], 'principal' => [3_000, 12_000], 'elapsedDays' => 0],
        'weekly' => ['terms' => [20, 48], 'principal' => [8_000, 40_000], 'elapsedDays' => 5],
        'monthly' => ['terms' => [12, 24], 'principal' => [15_000, 80_000], 'elapsedDays' => 20],
    ];

    private Generator $faker;

    private CarbonImmutable $asOf;

    private int $receiptNumber;

    public function run(): void
    {
        $seeded = DB::transaction(function (): bool {
            if (DB::table('debtors')->where('email', self::FIRST_EMAIL)->exists()) {
                return false;
            }

            $this->faker = Factory::create();
            $this->faker->seed(20261123);
            $this->asOf = CarbonImmutable::yesterday('Asia/Manila');
            $this->receiptNumber = max(800_000_000, (int) DB::table('payments')->max('receipt_no'));

            for ($number = 1; $number <= self::DEBTOR_COUNT; $number++) {
                $this->seedDebtor($number);
            }

            return true;
        });

        $this->command?->info($seeded
            ? 'Seeded lending classroom data through '.$this->asOf->toDateString().'.'
            : 'Lending classroom data is already present.');
    }

    private function seedDebtor(int $number): void
    {
        $firstName = $this->faker->randomElement(self::FIRST_NAMES);
        $lastName = $this->faker->randomElement(self::LAST_NAMES);
        $payor = $firstName.' '.$lastName;
        [$barangay, $town, $province] = $this->faker->randomElement(self::LOCATIONS);
        $creditLimit = $this->faker->numberBetween(6, 20) * 5_000;
        $type = $this->faker->randomElement(array_keys(self::PRODUCTS));
        $terms = $this->faker->numberBetween(...self::PRODUCTS[$type]['terms']);
        $history = $this->faker->numberBetween(1, 100);
        $matured = $history <= 35;
        $allowMisses = $history > 25 && $history <= 55;
        $elapsedTerms = $matured ? $terms : $this->faker->numberBetween(5, $terms - 2);
        $elapsedDays = $matured
            ? $this->faker->numberBetween(10, 150)
            : $this->faker->numberBetween(0, self::PRODUCTS[$type]['elapsedDays']);
        $startDate = $this->installmentDate($this->asOf->subDays($elapsedDays), $type, -$elapsedTerms);

        $previousLoan = $this->faker->boolean(55);
        $previousDate = $startDate;

        if ($previousLoan) {
            $previousType = $this->faker->randomElement(array_diff(array_keys(self::PRODUCTS), [$type]));
            $previousTerms = $this->faker->numberBetween(...self::PRODUCTS[$previousType]['terms']);
            $previousEnd = $startDate->subDays($this->faker->numberBetween(14, 120));
            $previousDate = $this->installmentDate($previousEnd, $previousType, -$previousTerms);
        }

        $createdAt = $previousDate->subDays($this->faker->numberBetween(2, 30))->toDateTimeString();
        $debtorId = DB::table('debtors')->insertGetId([
            'fname' => $firstName,
            'lname' => $lastName,
            'barangay' => $barangay,
            'town' => $town,
            'province' => $province,
            'email' => sprintf('debtor-%04d@lending.example', $number),
            'cell_no' => sprintf('0900000%04d', $number),
            'reference' => $this->faker->randomElement(self::FIRST_NAMES).' '.$lastName,
            'reference_no' => sprintf('0900001%04d', $number),
            'occupation' => $this->faker->randomElement(self::OCCUPATIONS),
            'credit_limit' => $creditLimit,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        if ($previousLoan) {
            $this->seedLoan($debtorId, $payor, $creditLimit, $previousType, $previousTerms, $previousDate, false);
        }

        $this->seedLoan($debtorId, $payor, $creditLimit, $type, $terms, $startDate, $allowMisses);
    }

    private function seedLoan(
        int $debtorId,
        string $payor,
        int $creditLimit,
        string $type,
        int $terms,
        CarbonImmutable $startDate,
        bool $allowMisses,
    ): void {
        [$minimum, $maximum] = self::PRODUCTS[$type]['principal'];
        $principal = 50_000 * $this->faker->numberBetween(
            intdiv($minimum, 500),
            intdiv(min($maximum, $creditLimit), 500),
        );
        $interestRate = $this->faker->randomElement([12, 15, 18, 21, 24]);
        $endDate = $this->installmentDate($startDate, $type, $terms);
        $durationDays = (int) $startDate->diffInDays($endDate);

        // Flat annual interest is prorated over the actual term. All calculations use centavos.
        $interest = intdiv($principal * $interestRate * $durationDays + 18_250, 36_500);
        $dueDates = [];
        $dueTerms = [];

        for ($term = 1; $term <= $terms; $term++) {
            $dueDates[$term] = $this->installmentDate($startDate, $type, $term)->toDateString();

            if ($dueDates[$term] <= $this->asOf->toDateString()) {
                $dueTerms[] = $term;
            }
        }

        // Round down to whole installments. With at least ten due, this misses 5–10% of the due terms.
        $missedTerms = $allowMisses && count($dueTerms) >= 10
            ? array_slice($this->faker->shuffleArray($dueTerms), 0, intdiv(count($dueTerms), 10))
            : [];
        $paidTerms = array_values(array_diff($dueTerms, $missedTerms));
        $timestamp = $startDate->toDateTimeString();

        $loanId = DB::table('loans')->insertGetId([
            'debtor_id' => $debtorId,
            'type' => $type,
            'principal' => $this->decimal($principal),
            'terms' => $terms,
            'interest_rate' => $interestRate,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'status' => count($paidTerms) === $terms ? 'closed' : 'approved',
            'release_date' => $startDate->toDateString(),
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        // The primary-key bound keeps each lookup small on databases without an index on loan_id.
        $lastScheduleId = (int) DB::table('payment_schedules')->max('id');
        $paidTermLookup = array_fill_keys($paidTerms, true);
        $installments = [];
        $schedules = [];

        foreach ($dueDates as $term => $dueDate) {
            $principalDue = intdiv($principal, $terms) + (int) ($term <= $principal % $terms);
            $interestDue = intdiv($interest, $terms) + (int) ($term <= $interest % $terms);
            $payable = $principalDue + $interestDue;
            $installments[$term] = [
                'date' => $dueDate,
                'principal' => $principalDue,
                'interest' => $interestDue,
            ];

            $schedules[] = [
                'loan_id' => $loanId,
                'date' => $dueDate,
                'payable' => $this->decimal($payable),
                'principal' => $this->decimal($principalDue),
                'interest' => $this->decimal($interestDue),
                'balance' => isset($paidTermLookup[$term]) ? '0.00' : $this->decimal($payable),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::table('payment_schedules')->insert($schedules);
        $scheduleIds = DB::table('payment_schedules')
            ->where('id', '>', $lastScheduleId)
            ->where('loan_id', $loanId)
            ->pluck('id', 'date')
            ->all();

        $this->seedPayments($payor, $installments, $paidTerms, $scheduleIds);
    }

    /**
     * @param  array<int, array{date: string, principal: int, interest: int}>  $installments
     * @param  list<int>  $paidTerms
     * @param  array<string, int>  $scheduleIds
     */
    private function seedPayments(string $payor, array $installments, array $paidTerms, array $scheduleIds): void
    {
        $receiptGroups = array_map(fn (int $term): array => [$term], $paidTerms);

        // A catch-up receipt can cover two installments, so receipt totals must not be summed per allocation.
        if (count($paidTerms) >= 2 && $this->faker->boolean(38)) {
            $first = $this->faker->numberBetween(0, count($paidTerms) - 2);
            array_splice($receiptGroups, $first, 2, [[$paidTerms[$first], $paidTerms[$first + 1]]]);
        }

        $receipts = [];
        $allocations = [];

        foreach ($receiptGroups as $group) {
            $receipt = ++$this->receiptNumber;
            $lastDue = CarbonImmutable::parse($installments[max($group)]['date'], $this->asOf->timezone);
            $delay = $this->faker->boolean(82) ? 0 : $this->faker->numberBetween(1, 3);
            $paidAt = $lastDue->addDays($delay)->min($this->asOf)
                ->setTime($this->faker->numberBetween(8, 18), $this->faker->numberBetween(0, 59))
                ->toDateTimeString();
            $amount = 0;

            foreach ($group as $term) {
                $installment = $installments[$term];
                $allocatedAmount = $installment['principal'] + $installment['interest'];
                $amount += $allocatedAmount;

                $allocations[$receipt][] = [
                    'payment_schedule_id' => $scheduleIds[$installment['date']],
                    'amount' => $this->decimal($allocatedAmount),
                    'principal' => $this->decimal($installment['principal']),
                    'interest' => $this->decimal($installment['interest']),
                    'created_at' => $paidAt,
                    'updated_at' => $paidAt,
                ];
            }

            $receipts[] = [
                'receipt_no' => $receipt,
                'paid_at' => $paidAt,
                'method' => $this->faker->randomElement(['cash', 'cash', 'cash', 'gcash', 'gcash', 'maya', 'bank transfer']),
                'amount' => $this->decimal($amount),
                'payor' => $payor,
                'created_at' => $paidAt,
                'updated_at' => $paidAt,
            ];
        }

        DB::table('payments')->insert($receipts);
        $paymentIds = DB::table('payments')->whereIn('receipt_no', array_keys($allocations))->pluck('id', 'receipt_no');
        $rows = [];

        foreach ($allocations as $receipt => $items) {
            foreach ($items as $item) {
                $rows[] = ['payment_id' => $paymentIds[$receipt], ...$item];
            }
        }

        DB::table('paid_payment_schedules')->insert($rows);
    }

    private function installmentDate(CarbonImmutable $date, string $type, int $offset): CarbonImmutable
    {
        return match ($type) {
            'daily' => $date->addDays($offset),
            'weekly' => $date->addWeeks($offset),
            'monthly' => $date->addMonthsNoOverflow($offset),
        };
    }

    private function decimal(int $centavos): string
    {
        return sprintf('%d.%02d', intdiv($centavos, 100), $centavos % 100);
    }
}
