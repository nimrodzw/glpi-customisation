<?php

/**
 * FrexCore demo seeder.
 *
 * Builds a believable organisation inside a tenant: branches, staff, a named
 * server estate, categories carrying the regulation behind them, and a year
 * of ticket history with real shape.
 *
 * WHY A CONSOLE COMMAND RATHER THAN SQL
 * The obvious approach is a .sql file of INSERTs, and it is the wrong one.
 * Tickets are not a single table: actors live in glpi_tickets_users, and the
 * application maintains history and derived fields alongside. Hand-written
 * SQL that misses those produces a demo where every ticket has no requester,
 * which is noticed in the first thirty seconds of a presentation. Running
 * inside the console means the application's own object layer does the work
 * and all of that stays correct.
 *
 * SAFETY
 * This command writes data. Run against a live tenant it would put invented
 * policyholders into a real client's system. It therefore refuses to touch
 * any instance that already holds tickets unless explicitly forced, supports
 * --dry-run, and tags everything it creates so --purge removes exactly what
 * it made and nothing a person added by hand.
 *
 * @copyright Copyright (C) 2026 FrexCore and contributors.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace GlpiPlugin\Frexcore\Console;

use Glpi\Console\AbstractCommand;
use GlpiPlugin\Frexcore\Demo\DemoData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SeedDemoCommand extends AbstractCommand
{
    private bool $dry = false;
    private int $entity = 0;
    private array $made = [];

    protected function configure(): void
    {
        parent::configure();
        $this->setName('plugins:frexcore:seed-demo');
        $this->setDescription('Load the FrexCore demonstration dataset into this instance');

        $this->addOption('org', null, InputOption::VALUE_REQUIRED,
            'Organisation name shown throughout the demo', 'Shiloh Financial Group');
        $this->addOption('tickets', null, InputOption::VALUE_REQUIRED,
            'How many tickets to generate across the last 12 months', '420');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE,
            'Report what would be created and write nothing');
        $this->addOption('purge', null, InputOption::VALUE_NONE,
            'Remove previously seeded demo records and stop');
        $this->addOption('force', null, InputOption::VALUE_NONE,
            'Proceed even though this instance already holds tickets');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        $this->dry = (bool) $input->getOption('dry-run');
        $org       = (string) $input->getOption('org');
        $target    = max(20, (int) $input->getOption('tickets'));

        if ($input->getOption('purge')) {
            return $this->purge($output);
        }

        // Guard. Seeded data in a client's live instance is not an
        // inconvenience, it is invented people in real records.
        $existing = (int) ($this->db->request([
            'COUNT' => 'c', 'FROM' => 'glpi_tickets', 'WHERE' => ['is_deleted' => 0],
        ])->current()['c'] ?? 0);

        if ($existing > 0 && !$input->getOption('force') && !$this->dry) {
            $output->writeln("<error>This instance already holds {$existing} ticket(s).</error>");
            $output->writeln('Seeding would mix demonstration records into real data.');
            $output->writeln('Use --dry-run to preview, or --force if this really is a demo instance.');
            return self::FAILURE;
        }

        // Seeding creates hundreds of records. With notifications live that
        // is hundreds of emails to people who do not exist.
        $CFG_GLPI['use_notifications'] = 0;
        $CFG_GLPI['notifications_mailing'] = 0;

        $output->writeln('');
        $output->writeln("  <info>FrexCore demonstration data</info>");
        $output->writeln("  organisation   {$org}");
        $output->writeln("  tickets        {$target} across the last 12 months");
        $output->writeln("  mode           " . ($this->dry ? 'DRY RUN, nothing written' : 'writing'));
        $output->writeln('');

        $locations  = $this->seedLocations($output);
        $groups     = $this->seedGroups($output);
        $users      = $this->seedUsers($output, $locations, $groups);
        $categories = $this->seedCategories($output);

        // Each phase is guarded. Half an estate is still a usable demo;
        // a run that dies on racks and leaves no tickets is not.
        $ref = [];
        $this->step($output, 'reference data', function () use (&$ref) { $ref = $this->seedReference(); });
        $this->step($output, 'server estate',  fn() => $this->seedServers($locations, $ref));
        $this->step($output, 'workstations and screens', fn() => $this->seedWorkstations($locations, $users, $ref));
        $this->step($output, 'printers, network and phones', fn() => $this->seedPeripherals($locations, $ref));
        $this->step($output, 'software and licensing', fn() => $this->seedSoftware($ref));
        $this->step($output, 'suppliers and contracts', fn() => $this->seedContracts());
        $this->step($output, 'service level targets', fn() => $this->seedSla());
        $this->step($output, 'knowledge base', fn() => $this->seedKnowledge());
        $this->step($output, 'problems and changes', fn() => $this->seedProblemsChanges($categories));

        $this->seedTickets($output, $target, $users, $categories);

        $output->writeln('');
        foreach ($this->made as $what => $n) {
            $output->writeln(sprintf('  %-26s %d', $what, $n));
        }
        $output->writeln('');
        $output->writeln($this->dry
            ? '  <comment>Dry run. Nothing was written. Re-run without --dry-run to apply.</comment>'
            : '  <info>Done. Remove it again with --purge.</info>');
        $output->writeln('');

        return self::SUCCESS;
    }

    // ----------------------------------------------------------------- bits

    private function tally(string $what, int $n = 1): void
    {
        $this->made[$what] = ($this->made[$what] ?? 0) + $n;
    }

    /**
     * Create a record unless it already exists, so a re-run tops up rather
     * than duplicating. Everything carries the marker in its comment so the
     * purge can find it later.
     */
    private function ensure(string $class, array $fields, array $unique): int
    {
        $table = getTableForItemType($class);

        // The caller names the columns that identify a record. Deriving the
        // lookup from a fixed set instead would break on glpi_users, which
        // does not carry entities_id the way the other tables do.
        $where = array_intersect_key($fields, array_flip($unique));

        $found = $this->db->request(['SELECT' => 'id', 'FROM' => $table, 'WHERE' => $where])->current();
        if ($found) {
            return (int) $found['id'];
        }
        if ($this->dry) {
            return -1;
        }

        $item = new $class();
        $id = $item->add($fields);
        return $id === false ? -1 : (int) $id;
    }

    // -------------------------------------------------------------- seeding

    private function seedLocations(OutputInterface $o): array
    {
        $ids = [];
        foreach (DemoData::LOCATIONS as $loc) {
            $ids[$loc['name']] = $this->ensure(\Location::class, [
                'name'        => $loc['name'],
                'country'     => $loc['country'],
                'entities_id' => $this->entity,
                'comment'     => DemoData::MARKER,
            ], ['name', 'entities_id']);
            $this->tally('locations');
        }
        $o->writeln('  locations');
        return $ids;
    }

    private function seedGroups(OutputInterface $o): array
    {
        $ids = [];
        foreach (DemoData::DEPARTMENTS as $dept) {
            $ids[$dept] = $this->ensure(\Group::class, [
                'name'        => $dept,
                'entities_id' => $this->entity,
                'is_recursive'=> 1,
                'comment'     => DemoData::MARKER,
            ], ['name', 'entities_id']);
            $this->tally('departments');
        }
        $o->writeln('  departments');
        return $ids;
    }

    private function seedUsers(OutputInterface $o, array $locations, array $groups): array
    {
        $locNames = array_keys($locations);
        // The named twenty, then enough additional staff that the asset
        // register is plausible. Twenty people cannot own ninety devices
        // across eight branches, and the prospect who notices that is
        // exactly the one worth convincing.
        $roster = DemoData::PEOPLE;
        $depts  = DemoData::DEPARTMENTS;
        $fn = DemoData::MORE_FIRST;
        $ln = DemoData::MORE_LAST;
        for ($n = 0; $n < 25; $n++) {
            $roster[] = [
                $fn[$n % count($fn)],
                $ln[($n * 7 + 3) % count($ln)],
                $depts[$n % count($depts)],
                false,
            ];
        }

        $ids = [];
        foreach ($roster as $i => [$first, $last, $dept, $isAgent]) {
            $login = strtolower($first . '.' . $last);
            $loc   = $locNames[$i % count($locNames)];

            $id = $this->ensure(\User::class, [
                'name'         => $login,
                'realname'     => $last,
                'firstname'    => $first,
                'locations_id' => max(0, $locations[$loc] ?? 0),
                'entities_id'  => $this->entity,
                'is_active'    => 1,
                'comment'      => DemoData::MARKER,
            ], ['name']);

            if ($id > 0 && !$this->dry) {
                // Email lives in its own table, and a user without one looks
                // unfinished the moment a ticket is opened on their behalf.
                $email = new \UserEmail();
                if (!$this->db->request([
                        'FROM' => 'glpi_useremails', 'WHERE' => ['users_id' => $id],
                    ])->current()) {
                    $email->add([
                        'users_id'   => $id,
                        'is_default' => 1,
                        'email'      => $login . '@shilohgroup.example',
                    ]);
                }
                if (($groups[$dept] ?? 0) > 0 && !$this->db->request([
                        'FROM' => 'glpi_groups_users',
                        'WHERE' => ['users_id' => $id, 'groups_id' => $groups[$dept]],
                    ])->current()) {
                    (new \Group_User())->add(['users_id' => $id, 'groups_id' => $groups[$dept]]);
                }
            }

            $ids[] = ['id' => $id, 'agent' => $isAgent];
            $this->tally('people');
        }
        $o->writeln('  people and departments linked');
        return $ids;
    }

    private function seedCategories(OutputInterface $o): array
    {
        $ids = [];
        foreach (DemoData::CATEGORIES as [$name, $group, $basis]) {
            // The obligation goes in the comment rather than the name, so the
            // queue stays readable while the reference is one hover away.
            $ids[$name] = $this->ensure(\ITILCategory::class, [
                'name'        => $name,
                'comment'     => $basis . ' ' . DemoData::MARKER,
                'entities_id' => $this->entity,
                'is_recursive'=> 1,
            ], ['name', 'entities_id']);
            $this->tally('categories');
        }
        $o->writeln('  categories, each carrying its regulatory basis');
        return $ids;
    }



    /**
     * Tickets, in two deliberate phases.
     *
     * A single probabilistic loop cannot produce both a year of history and
     * a credible queue as it stands today. Tuned to give realistic history
     * it leaves almost nothing open; tuned to leave a working queue it
     * scatters year-old open tickets through the record. Both were tried.
     *
     * So history and the live queue are generated separately, which is also
     * how the real thing arrives: a closed record accumulated over months,
     * and a handful of items currently in flight.
     */
    private function seedTickets(OutputInterface $o, int $target, array $users, array $categories): void
    {
        $agents = array_values(array_filter($users, fn($u) => $u['agent'] && $u['id'] > 0));
        $staff  = array_values(array_filter($users, fn($u) => $u['id'] > 0));

        if (!$this->dry && (empty($agents) || empty($staff))) {
            $o->writeln('  <error>no usable people, skipping tickets</error>');
            return;
        }

        // Enough open work to fill the queue card without looking swamped.
        $openWanted = max(14, (int) round($target * 0.08));
        $histWanted = max(1, $target - $openWanted);
        $made = 0;

        // Phase one: the closed record, weighted towards recent months so the
        // trend rises. No real desk has ever had a flat year.
        for ($i = 0; $i < $histWanted; $i++) {
            $skew    = ($i / max(1, $histWanted)) ** 0.7;
            $daysAgo = (int) round(350 * (1 - $skew)) + random_int(14, 21);
            $made   += $this->makeTicket($i, $daysAgo, true, $staff, $agents, $categories);
        }

        // Phase two: the queue as it stands this morning.
        for ($i = 0; $i < $openWanted; $i++) {
            $daysAgo = (int) floor(((1 - ($i / max(1, $openWanted))) ** 1.6) * 9);
            $made   += $this->makeTicket($histWanted + $i, $daysAgo, false, $staff, $agents, $categories);
        }

        $this->tally('tickets', $made);
        $o->writeln('  tickets: twelve months of history, plus a live queue');
    }

    /** Returns 1 if a ticket was created, 0 otherwise. */
    private function makeTicket(int $seq, int $daysAgo, bool $closed, array $staff,
                                array $agents, array $categories): int
    {
        $templates = DemoData::TICKETS;
        [$cat, $title, $body, $urgency, $isRequest] = $templates[$seq % count($templates)];

        if ($this->dry) {
            return 1;
        }

        $now    = time();
        $opened = $now - ($daysAgo * 86400) - random_int(3600, 43200);

        if ($closed) {
            $status = \CommonITILObject::CLOSED;
        } elseif ($daysAgo >= 4) {
            // Work open several days is waiting on somebody. Saying so is the
            // difference between a queue that reads as managed and one that
            // reads as ignored.
            $status = \CommonITILObject::WAITING;
        } else {
            $status = [\CommonITILObject::INCOMING, \CommonITILObject::ASSIGNED,
                       \CommonITILObject::PLANNED][random_int(0, 2)];
        }

        $ticket = new \Ticket();
        $id = $ticket->add([
            'name'                => $title,
            'content'             => $body,
            'entities_id'         => $this->entity,
            'type'                => $isRequest ? \Ticket::DEMAND_TYPE : \Ticket::INCIDENT_TYPE,
            'itilcategories_id'   => max(0, $categories[$cat] ?? 0),
            'status'              => $status,
            'urgency'             => $urgency,
            'impact'              => max(1, min(5, $urgency - random_int(0, 1))),
            'priority'            => $urgency,
            '_users_id_requester' => $staff[random_int(0, count($staff) - 1)]['id'],
            '_users_id_assign'    => $agents[random_int(0, count($agents) - 1)]['id'],
            '_auto_import'        => true,
        ]);
        if ($id === false) {
            return 0;
        }

        // Dates are written after creation: the application stamps its own on
        // insert, so backdating has to be a second step. These are plain
        // columns, so writing them directly is safe.
        $open = date('Y-m-d H:i:s', $opened);
        $upd  = ['date' => $open, 'date_creation' => $open, 'date_mod' => $open];

        if ($closed) {
            $solveIn = random_int(1800, 3 * 86400);
            $upd['solvedate'] = date('Y-m-d H:i:s', $opened + $solveIn);
            $upd['closedate'] = date('Y-m-d H:i:s', $opened + $solveIn + random_int(600, 86400));
            $upd['date_mod']  = $upd['closedate'];
        } else {
            // A board showing no breaches fails to demonstrate the one thing a
            // service desk is bought to prevent; a board showing nothing but
            // breaches describes an organisation nobody wants to copy.
            $upd['time_to_resolve'] = random_int(1, 100) <= 15
                ? date('Y-m-d H:i:s', $now - random_int(3600, 2 * 86400))
                : date('Y-m-d H:i:s', $now + (($urgency >= 4 ? random_int(2, 10) : random_int(12, 72)) * 3600));
        }

        $this->db->update('glpi_tickets', $upd, ['id' => $id]);

        if ($closed) {
            try {
                (new \ITILSolution())->add([
                    'itemtype' => 'Ticket',
                    'items_id' => $id,
                    'content'  => DemoData::RESOLUTIONS[random_int(0, count(DemoData::RESOLUTIONS) - 1)],
                ]);
            } catch (\Throwable $e) {
                // Resolution text is presentation. Losing it is not worth
                // failing an otherwise good seeding run.
            }
        }

        return 1;
    }

    /**
     * Run one phase. A phase that throws reports and the run continues:
     * half an estate is still a usable demo, whereas a run that dies on
     * racks and never reaches tickets is not.
     */
    private function step(OutputInterface $o, string $label, callable $fn): void
    {
        try {
            $fn();
            $o->writeln('  ' . $label);
        } catch (\Throwable $e) {
            $o->writeln(sprintf('  <comment>%s skipped: %s</comment>', $label, $e->getMessage()));
        }
    }

    /** Lookup tables everything else hangs off. */
    private function seedReference(): array
    {
        $ref = ['mfg' => [], 'state' => [], 'os' => [], 'ctype' => [], 'cmodel' => [],
                'pmodel' => [], 'mmodel' => [], 'nmodel' => [], 'phmodel' => []];

        foreach (DemoData::MANUFACTURERS as $m) {
            $ref['mfg'][$m] = $this->ensure(\Manufacturer::class,
                ['name' => $m, 'comment' => DemoData::MARKER], ['name']);
        }
        foreach (DemoData::STATES as $st) {
            $ref['state'][$st] = $this->ensure(\State::class,
                ['name' => $st, 'entities_id' => $this->entity, 'is_recursive' => 1,
                 'comment' => DemoData::MARKER], ['name', 'entities_id']);
        }
        foreach (DemoData::OPERATING_SYSTEMS as $os) {
            $ref['os'][$os] = $this->ensure(\OperatingSystem::class,
                ['name' => $os, 'comment' => DemoData::MARKER], ['name']);
        }
        foreach (['Laptop', 'Desktop', 'Server'] as $t) {
            $ref['ctype'][$t] = $this->ensure(\ComputerType::class,
                ['name' => $t, 'comment' => DemoData::MARKER], ['name']);
        }
        foreach (DemoData::COMPUTER_MODELS as [$model, $mfg, $type]) {
            $ref['cmodel'][$model] = ['id' => $this->ensure(\ComputerModel::class,
                ['name' => $model, 'comment' => DemoData::MARKER], ['name']),
                'mfg' => $mfg, 'type' => $type];
        }
        foreach (DemoData::PRINTER_MODELS as [$model, $mfg, $colour, $duty]) {
            $ref['pmodel'][$model] = ['id' => $this->ensure(\PrinterModel::class,
                ['name' => $model, 'comment' => DemoData::MARKER], ['name']),
                'mfg' => $mfg, 'colour' => $colour];
        }
        foreach (DemoData::MONITOR_MODELS as [$model, $mfg]) {
            $ref['mmodel'][$model] = ['id' => $this->ensure(\MonitorModel::class,
                ['name' => $model, 'comment' => DemoData::MARKER], ['name']), 'mfg' => $mfg];
        }
        foreach (DemoData::NETWORK_MODELS as [$model, $mfg, $role]) {
            $ref['nmodel'][$model] = ['id' => $this->ensure(\NetworkEquipmentModel::class,
                ['name' => $model, 'comment' => DemoData::MARKER], ['name']),
                'mfg' => $mfg, 'role' => $role];
        }
        foreach (DemoData::PHONE_MODELS as [$model, $mfg]) {
            $ref['phmodel'][$model] = ['id' => $this->ensure(\PhoneModel::class,
                ['name' => $model, 'comment' => DemoData::MARKER], ['name']), 'mfg' => $mfg];
        }

        $this->tally('reference records',
            count($ref['mfg']) + count($ref['state']) + count($ref['os']) + count($ref['ctype']));
        return $ref;
    }

    /**
     * An asset without a purchase date, a warranty and a value is a row in a
     * list. With them it is a thing somebody owns, which is the difference
     * between an inventory and an asset register.
     */
    private function addInfocom(string $itemtype, int $id, int $monthsOld, float $value): void
    {
        if ($this->dry || $id <= 0) {
            return;
        }
        try {
            $buy = date('Y-m-d', strtotime("-{$monthsOld} months"));
            (new \Infocom())->add([
                'itemtype'          => $itemtype,
                'items_id'          => $id,
                'entities_id'       => $this->entity,
                'buy_date'          => $buy,
                'use_date'          => $buy,
                'warranty_date'     => $buy,
                'warranty_duration' => 36,
                'value'             => $value,
            ]);
        } catch (\Throwable $e) {
            // Financial detail is depth, not substance.
        }
    }

    private function seedServers(array $locations, array $ref): void
    {
        $dc     = max(0, $locations['Data centre'] ?? 0);
        $models = array_keys(array_filter($ref['cmodel'], fn($m) => $m['type'] === 'Server'));
        $inUse  = max(0, $ref['state']['In use'] ?? 0);
        $i = 0;

        foreach (DemoData::SERVERS as [$host, $role]) {
            $model = $models[$i % max(1, count($models))] ?? null;
            $id = $this->ensure(\Computer::class, [
                'name'                => $host,
                'comment'             => $role . ' ' . DemoData::MARKER,
                'serial'              => 'SRV' . str_pad((string) (1000 + $i), 5, '0', STR_PAD_LEFT),
                'locations_id'        => $dc,
                'states_id'           => $inUse,
                'entities_id'         => $this->entity,
                'computertypes_id'    => max(0, $ref['ctype']['Server'] ?? 0),
                'computermodels_id'   => $model ? max(0, $ref['cmodel'][$model]['id']) : 0,
                'manufacturers_id'    => $model ? max(0, $ref['mfg'][$ref['cmodel'][$model]['mfg']] ?? 0) : 0,
            ], ['name', 'entities_id']);
            $this->addInfocom('Computer', $id, 18 + ($i % 24), 42000 + ($i * 1500));
            $this->tally('servers');
            $i++;
        }
    }

    private function seedWorkstations(array $locations, array $users, array $ref): void
    {
        $locNames = array_keys($locations);
        $clients  = array_keys(array_filter($ref['cmodel'], fn($m) => $m['type'] !== 'Server'));
        $monitors = array_keys($ref['mmodel']);
        $osList   = array_keys($ref['os']);
        $people   = array_values(array_filter($users, fn($u) => $u['id'] > 0));

        // Most people get a machine; a minority of desk-based roles get two.
        $count = max(24, (int) round(count($people) * 1.15));

        for ($i = 0; $i < $count; $i++) {
            $model = $clients[$i % max(1, count($clients))] ?? null;
            if (!$model) {
                return;
            }
            $type  = $ref['cmodel'][$model]['type'];
            $prefix = $type === 'Laptop' ? 'LT' : 'WS';
            $loc   = $locNames[$i % count($locNames)];

            // A fleet entirely in use is a fleet nobody believes. Real estates
            // always carry spares, repairs and kit waiting for disposal.
            $state = match (true) {
                $i % 17 === 0 => 'Under repair',
                $i % 13 === 0 => 'In stock',
                $i % 23 === 0 => 'Awaiting disposal',
                default       => 'In use',
            };

            $id = $this->ensure(\Computer::class, [
                'name'               => $prefix . '-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'serial'             => strtoupper(substr(md5($prefix . $i), 0, 10)),
                'comment'            => DemoData::MARKER,
                'locations_id'       => max(0, $locations[$loc] ?? 0),
                'states_id'          => max(0, $ref['state'][$state] ?? 0),
                'entities_id'        => $this->entity,
                'computertypes_id'   => max(0, $ref['ctype'][$type] ?? 0),
                'computermodels_id'  => max(0, $ref['cmodel'][$model]['id']),
                'manufacturers_id'   => max(0, $ref['mfg'][$ref['cmodel'][$model]['mfg']] ?? 0),
                'users_id'           => $state === 'In use' && $people
                                        ? $people[$i % count($people)]['id'] : 0,
            ], ['name', 'entities_id']);

            if ($id > 0 && !$this->dry && $osList) {
                try {
                    (new \Item_OperatingSystem())->add([
                        'itemtype'            => 'Computer',
                        'items_id'            => $id,
                        'operatingsystems_id' => max(0, $ref['os'][$osList[$i % count($osList)]] ?? 0),
                    ]);
                } catch (\Throwable $e) {
                }
            }
            $this->addInfocom('Computer', $id, 6 + ($i % 40), $type === 'Laptop' ? 18500 : 12000);
            $this->tally('workstations');
        }

        // Screens, roughly one and a bit per desk.
        for ($i = 0; $i < (int) round($count * 0.8); $i++) {
            $model = $monitors[$i % max(1, count($monitors))] ?? null;
            if (!$model) {
                return;
            }
            $loc = $locNames[$i % count($locNames)];
            $id = $this->ensure(\Monitor::class, [
                'name'             => 'MON-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'serial'           => strtoupper(substr(md5('mon' . $i), 0, 10)),
                'comment'          => DemoData::MARKER,
                'locations_id'     => max(0, $locations[$loc] ?? 0),
                'states_id'        => max(0, $ref['state']['In use'] ?? 0),
                'entities_id'      => $this->entity,
                'monitormodels_id' => max(0, $ref['mmodel'][$model]['id']),
                'manufacturers_id' => max(0, $ref['mfg'][$ref['mmodel'][$model]['mfg']] ?? 0),
            ], ['name', 'entities_id']);
            $this->addInfocom('Monitor', $id, 8 + ($i % 36), 3200);
            $this->tally('monitors');
        }
    }

    private function seedPeripherals(array $locations, array $ref): void
    {
        $locNames = array_keys($locations);
        $printers = array_keys($ref['pmodel']);
        $network  = array_keys($ref['nmodel']);
        $phones   = array_keys($ref['phmodel']);
        $inUse    = max(0, $ref['state']['In use'] ?? 0);

        foreach ($locNames as $i => $loc) {
            // Branches get a counter printer and a back office printer.
            foreach ([['CTR', 0], ['BO', 1]] as [$tag, $off]) {
                $model = $printers[($i + $off) % max(1, count($printers))] ?? null;
                if (!$model) {
                    break;
                }
                $id = $this->ensure(\Printer::class, [
                    'name'             => 'PRN-' . strtoupper(substr($loc, 0, 3)) . '-' . $tag,
                    'serial'           => strtoupper(substr(md5('prn' . $loc . $tag), 0, 10)),
                    'comment'          => $ref['pmodel'][$model]['colour'] ? 'Colour multifunction ' . DemoData::MARKER
                                                                          : 'Mono multifunction ' . DemoData::MARKER,
                    'locations_id'     => max(0, $locations[$loc] ?? 0),
                    'states_id'        => $inUse,
                    'entities_id'      => $this->entity,
                    'printermodels_id' => max(0, $ref['pmodel'][$model]['id']),
                    'manufacturers_id' => max(0, $ref['mfg'][$ref['pmodel'][$model]['mfg']] ?? 0),
                    'have_ethernet'    => 1,
                ], ['name', 'entities_id']);
                $this->addInfocom('Printer', $id, 10 + ($i * 2), 28000);
                $this->tally('printers');
            }

            // Firewall, switch and an access point per site.
            foreach ($network as $j => $model) {
                $role = $ref['nmodel'][$model]['role'];
                $isCore = str_contains($role, 'Core');
                if ($isCore && $loc !== 'Data centre') {
                    continue;
                }
                if (!$isCore && $loc === 'Data centre' && str_contains($role, 'Branch')) {
                    continue;
                }
                $id = $this->ensure(\NetworkEquipment::class, [
                    'name'                      => 'NET-' . strtoupper(substr($loc, 0, 3)) . '-' . ($j + 1),
                    'serial'                    => strtoupper(substr(md5('net' . $loc . $j), 0, 10)),
                    'comment'                   => $role . ' ' . DemoData::MARKER,
                    'locations_id'              => max(0, $locations[$loc] ?? 0),
                    'states_id'                 => $inUse,
                    'entities_id'               => $this->entity,
                    'networkequipmentmodels_id' => max(0, $ref['nmodel'][$model]['id']),
                    'manufacturers_id'          => max(0, $ref['mfg'][$ref['nmodel'][$model]['mfg']] ?? 0),
                ], ['name', 'entities_id']);
                $this->addInfocom('NetworkEquipment', $id, 12 + $j, 34000);
                $this->tally('network equipment');
            }

            // Desk phones, a couple per branch.
            for ($k = 0; $k < 2; $k++) {
                $model = $phones[$k % max(1, count($phones))] ?? null;
                if (!$model) {
                    break;
                }
                $id = $this->ensure(\Phone::class, [
                    'name'             => 'TEL-' . strtoupper(substr($loc, 0, 3)) . '-' . ($k + 1),
                    'comment'          => DemoData::MARKER,
                    'locations_id'     => max(0, $locations[$loc] ?? 0),
                    'states_id'        => $inUse,
                    'entities_id'      => $this->entity,
                    'phonemodels_id'   => max(0, $ref['phmodel'][$model]['id']),
                    'manufacturers_id' => max(0, $ref['mfg'][$ref['phmodel'][$model]['mfg']] ?? 0),
                ], ['name', 'entities_id']);
                $this->addInfocom('Phone', $id, 14, 2400);
                $this->tally('phones');
            }
        }
    }

    /**
     * Software with a licensing position rather than a bare list.
     *
     * Seat counts and renewal dates are what make this screen worth looking
     * at: an over-deployed licence and a renewal inside the quarter are two
     * things most buyers cannot answer about their own estate.
     */
    private function seedSoftware(array $ref): void
    {
        foreach (DemoData::SOFTWARE as [$name, $publisher, $version, $type, $seats, $renewIn]) {
            $sid = $this->ensure(\Software::class, [
                'name'             => $name,
                'comment'          => $type . ' ' . DemoData::MARKER,
                'entities_id'      => $this->entity,
                'is_recursive'     => 1,
                'manufacturers_id' => max(0, $ref['mfg'][$publisher] ?? 0),
            ], ['name', 'entities_id']);

            if ($sid <= 0 || $this->dry) {
                $this->tally('software');
                continue;
            }

            $vid = $this->ensure(\SoftwareVersion::class, [
                'name'         => $version,
                'softwares_id' => $sid,
                'entities_id'  => $this->entity,
                'comment'      => DemoData::MARKER,
            ], ['name', 'softwares_id']);

            $expire = $renewIn > 0 ? date('Y-m-d', strtotime("+{$renewIn} months")) : null;
            $this->ensure(\SoftwareLicense::class, [
                'name'                   => $name . ' licence',
                'softwares_id'           => $sid,
                'entities_id'            => $this->entity,
                'number'                 => $seats,
                'softwareversions_id_buy'=> max(0, $vid),
                'expire'                 => $expire,
                'comment'                => ($expire ? 'Renews ' . $expire . '. ' : 'Perpetual. ')
                                            . DemoData::MARKER,
            ], ['name', 'softwares_id']);

            $this->tally('software');
            $this->tally('licences');
        }
    }

    private function seedContracts(): void
    {
        $sup = [];
        foreach (DemoData::SUPPLIERS as [$name, $what]) {
            $sup[$name] = $this->ensure(\Supplier::class, [
                'name'        => $name,
                'comment'     => $what . ' ' . DemoData::MARKER,
                'entities_id' => $this->entity,
                'is_recursive'=> 1,
            ], ['name', 'entities_id']);
            $this->tally('suppliers');
        }

        foreach (DemoData::CONTRACTS as $i => [$name, $supplier, $type, $renewIn, $notice]) {
            $tid = $this->ensure(\ContractType::class,
                ['name' => $type, 'entities_id' => $this->entity, 'comment' => DemoData::MARKER],
                ['name', 'entities_id']);

            // Begin date works backwards from renewal so the remaining term
            // is right, which is the only number anyone reads on this screen.
            $begin = date('Y-m-d', strtotime("-" . (12 - $renewIn) . " months"));
            $cid = $this->ensure(\Contract::class, [
                'name'             => $name,
                'num'              => 'CTR-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'entities_id'      => $this->entity,
                'contracttypes_id' => max(0, $tid),
                'begin_date'       => $begin,
                'duration'         => 12,
                'notice'           => $notice,
                'accounting_number'=> 'GL-' . (4200 + $i),
                'comment'          => ($type === 'Data processing agreement'
                                       ? 'Processor agreement under GDPR Art. 28 and equivalent local law. '
                                       : '')
                                      . DemoData::MARKER,
            ], ['name', 'entities_id']);

            if ($cid > 0 && !$this->dry && ($sup[$supplier] ?? 0) > 0) {
                try {
                    (new \Contract_Supplier())->add([
                        'contracts_id' => $cid,
                        'suppliers_id' => $sup[$supplier],
                    ]);
                } catch (\Throwable $e) {
                }
            }
            $this->tally('contracts');
        }
    }

    private function seedSla(): void
    {
        $slm = $this->ensure(\SLM::class, [
            'name'        => 'Standard service levels',
            'entities_id' => $this->entity,
            'is_recursive'=> 1,
            'comment'     => DemoData::MARKER,
        ], ['name', 'entities_id']);

        if ($slm <= 0 || $this->dry) {
            return;
        }

        foreach (DemoData::SLA_TARGETS as [$priority, $label, $respond, $resolve]) {
            foreach ([[\SLM::TTO, 'response', $respond], [\SLM::TTR, 'resolution', $resolve]] as [$type, $word, $hours]) {
                $this->ensure(\SLA::class, [
                    'name'            => "{$label} {$word}",
                    'slms_id'         => $slm,
                    'entities_id'     => $this->entity,
                    'type'            => $type,
                    'number_time'     => $hours,
                    'definition_time' => 'hour',
                    'comment'         => "Priority {$priority}: {$word} within {$hours} hours. " . DemoData::MARKER,
                ], ['name', 'slms_id']);
                $this->tally('service level targets');
            }
        }
    }

    private function seedKnowledge(): void
    {
        foreach (DemoData::KB_ARTICLES as [$title, $category, $body]) {
            $id = $this->ensure(\KnowbaseItem::class, [
                'name'   => $title,
                // The marker goes in the body as an HTML comment: this table
                // has no comment column, so without it the purge cannot find
                // these again and a second run collides with the first.
                'answer' => nl2br(htmlspecialchars($body, ENT_QUOTES))
                            . '<!-- ' . DemoData::MARKER . ' -->',
                'is_faq' => 1,
            ], ['name']);

            // An article nobody can see is not knowledge. Visibility is a
            // separate record, and skipping it is the usual reason a seeded
            // knowledge base looks empty in the interface.
            if ($id > 0 && !$this->dry) {
                try {
                    if (!$this->db->request([
                        'FROM'  => 'glpi_entities_knowbaseitems',
                        'WHERE' => ['knowbaseitems_id' => $id],
                    ])->current()) {
                        (new \Entity_KnowbaseItem())->add([
                            'knowbaseitems_id' => $id,
                            'entities_id'      => $this->entity,
                            'is_recursive'     => 1,
                        ]);
                    }
                } catch (\Throwable $e) {
                }
            }
            $this->tally('knowledge articles');
        }
    }

    private function seedProblemsChanges(array $categories): void
    {
        foreach (DemoData::PROBLEMS as [$title, $body, $cat]) {
            $this->ensure(\Problem::class, [
                'name'              => $title,
                'content'           => $body,
                'entities_id'       => $this->entity,
                'status'            => \CommonITILObject::ASSIGNED,
                'urgency'           => 3,
                'impact'            => 4,
                'priority'          => 4,
                'itilcategories_id' => max(0, $categories[$cat] ?? 0),
            ], ['name', 'entities_id']);
            $this->tally('problems');
        }

        foreach (DemoData::CHANGES as [$title, $body, $cat]) {
            $this->ensure(\Change::class, [
                'name'              => $title,
                'content'           => $body,
                'entities_id'       => $this->entity,
                'status'            => \CommonITILObject::ACCEPTED,
                'urgency'           => 3,
                'impact'            => 3,
                'priority'          => 3,
                'itilcategories_id' => max(0, $categories[$cat] ?? 0),
            ], ['name', 'entities_id']);
            $this->tally('changes');
        }
    }

    // ---------------------------------------------------------------- purge

    private function purge(OutputInterface $o): int
    {
        $o->writeln('');
        $o->writeln('  <comment>Removing seeded demonstration records</comment>');

        $m = DemoData::MARKER;
        $removed = 0;

        // Tickets carry no marker of their own, so they are matched by the
        // categories this seeder created. A ticket someone filed by hand
        // against one of those categories would go too, which is acceptable
        // only because this runs on demonstration instances.
        $catIds = [];
        foreach ($this->db->request([
            'SELECT' => 'id', 'FROM' => 'glpi_itilcategories',
            'WHERE'  => ['comment' => ['LIKE', '%' . $m . '%']],
        ]) as $row) {
            $catIds[] = (int) $row['id'];
        }

        if ($catIds) {
            foreach ($this->db->request([
                'SELECT' => 'id', 'FROM' => 'glpi_tickets',
                'WHERE'  => ['itilcategories_id' => $catIds],
            ]) as $row) {
                (new \Ticket())->delete(['id' => (int) $row['id']], true);
                $removed++;
            }
        }
        $o->writeln("  tickets removed: {$removed}");

        // Order matters: records that reference others go first, so nothing
        // is left pointing at something that no longer exists.
        foreach ([
            \Problem::class              => 'glpi_problems',
            \Change::class               => 'glpi_changes',
            \SoftwareLicense::class      => 'glpi_softwarelicenses',
            \SoftwareVersion::class      => 'glpi_softwareversions',
            \Software::class             => 'glpi_softwares',
            \Contract::class             => 'glpi_contracts',
            \ContractType::class         => 'glpi_contracttypes',
            \Supplier::class             => 'glpi_suppliers',
            \SLA::class                  => 'glpi_slas',
            \SLM::class                  => 'glpi_slms',
            \KnowbaseItem::class         => 'glpi_knowbaseitems',
            \Monitor::class              => 'glpi_monitors',
            \Printer::class              => 'glpi_printers',
            \Phone::class                => 'glpi_phones',
            \NetworkEquipment::class     => 'glpi_networkequipments',
            \Computer::class             => 'glpi_computers',
            \ComputerModel::class        => 'glpi_computermodels',
            \MonitorModel::class         => 'glpi_monitormodels',
            \PrinterModel::class         => 'glpi_printermodels',
            \PhoneModel::class           => 'glpi_phonemodels',
            \NetworkEquipmentModel::class=> 'glpi_networkequipmentmodels',
            \ComputerType::class         => 'glpi_computertypes',
            \OperatingSystem::class      => 'glpi_operatingsystems',
            \Manufacturer::class         => 'glpi_manufacturers',
            \State::class                => 'glpi_states',
            \ITILCategory::class         => 'glpi_itilcategories',
            \User::class                 => 'glpi_users',
            \Group::class                => 'glpi_groups',
            \Location::class             => 'glpi_locations',
        ] as $class => $table) {
            $n = 0;
            // Knowledge articles have no comment column; their marker lives
            // in the body.
            $col = $table === 'glpi_knowbaseitems' ? 'answer' : 'comment';
            foreach ($this->db->request([
                'SELECT' => 'id', 'FROM' => $table,
                'WHERE'  => [$col => ['LIKE', '%' . $m . '%']],
            ]) as $row) {
                (new $class())->delete(['id' => (int) $row['id']], true);
                $n++;
            }
            $o->writeln(sprintf('  %-22s removed: %d', $table, $n));
        }

        $o->writeln('');
        $o->writeln('  <info>Purge complete.</info>');
        $o->writeln('');
        return self::SUCCESS;
    }
}
